<?php

namespace App\Auth;

use App\Core\Db;
use PDO;
use PDOException;
use Throwable;

class AuthService
{
    /** @return array{ok:bool, error?:string, account?:array} */
    public static function register(string $email, string $password): array
    {
        $email = trim($email);
        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($email) > 320) {
            return ['ok' => false, 'error' => 'err_email'];
        }
        if (strlen($password) < 8 || strlen($password) > 128) {
            return ['ok' => false, 'error' => 'err_password_short'];
        }

        $emailU = strtoupper($email);
        $pdo = Db::auth();

        try {
            if (self::findBnet($pdo, $emailU)) {
                return ['ok' => false, 'error' => 'err_exists'];
            }

            $pdo->beginTransaction();

            $salt = Srp6::salt();
            $stmt = $pdo->prepare(
                'INSERT INTO battlenet_accounts (email, srp_version, salt, verifier, last_ip) VALUES (?, 2, ?, ?, ?)'
            );
            $stmt->execute([$emailU, $salt, Srp6::bnetV2Verifier($emailU, $password, $salt), self::clientIp()]);
            $bnetId = (int) $pdo->lastInsertId();

            $gameUser = $bnetId . '#1';
            $gameSalt = Srp6::salt();
            $stmt = $pdo->prepare(
                'INSERT INTO account (username, salt, verifier, email, reg_mail, expansion, battlenet_account, battlenet_index)
                 VALUES (?, ?, ?, ?, ?, 11, ?, 1)'
            );
            $stmt->execute([
                $gameUser,
                $gameSalt,
                Srp6::gameVerifier($gameUser, strtoupper(substr($password, 0, 16)), $gameSalt),
                $emailU,
                $emailU,
                $bnetId,
            ]);

            $pdo->commit();
        } catch (Throwable) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            return ['ok' => false, 'error' => 'err_server'];
        }

        $account = ['id' => $bnetId, 'email' => $emailU];
        self::setSession($account);
        return ['ok' => true, 'account' => $account];
    }

    /** @return array{ok:bool, error?:string, account?:array} */
    public static function login(string $email, string $password): array
    {
        $emailU = strtoupper(trim($email));

        try {
            $row = self::findBnet(Db::auth(), $emailU);
        } catch (Throwable) {
            return ['ok' => false, 'error' => 'err_server'];
        }

        if (
            !$row
            || !Srp6::verifyBnet($emailU, $password, (int) $row['srp_version'], $row['salt'], $row['verifier'])
        ) {
            if ($row) {
                Db::auth()
                    ->prepare('UPDATE battlenet_accounts SET failed_logins = failed_logins + 1 WHERE id = ?')
                    ->execute([$row['id']]);
            }
            return ['ok' => false, 'error' => 'err_credentials'];
        }

        $account = ['id' => (int) $row['id'], 'email' => $row['email']];
        self::setSession($account);
        return ['ok' => true, 'account' => $account];
    }

    public static function logout(): void
    {
        unset($_SESSION['account']);
    }

    public static function user(): ?array
    {
        return $_SESSION['account'] ?? null;
    }

    /**
     * Highest account_access SecurityLevel across all game accounts linked
     * to this battlenet account (0 = player). Cached per request.
     */
    public static function accessLevel(int $bnetId): int
    {
        static $cache = [];
        if (!array_key_exists($bnetId, $cache)) {
            try {
                $stmt = Db::auth()->prepare(
                    'SELECT COALESCE(MAX(aa.SecurityLevel), 0)
                     FROM account a
                     JOIN account_access aa ON aa.AccountID = a.id
                     WHERE a.battlenet_account = ?'
                );
                $stmt->execute([$bnetId]);
                $cache[$bnetId] = (int) $stmt->fetchColumn();
            } catch (Throwable) {
                $cache[$bnetId] = 0;
            }
        }
        return $cache[$bnetId];
    }

    private static function findBnet(PDO $pdo, string $emailU): ?array
    {
        $stmt = $pdo->prepare('SELECT id, email, srp_version, salt, verifier FROM battlenet_accounts WHERE email = ?');
        $stmt->execute([$emailU]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    private static function setSession(array $account): void
    {
        session_regenerate_id(true);
        $_SESSION['account'] = $account;
    }

    private static function clientIp(): string
    {
        return substr((string) ($_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'), 0, 64);
    }
}
