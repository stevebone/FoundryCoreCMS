<?php

namespace App\Auth;

use GMP;

/**
 * TrinityCore SRP6 verifier generation, matching the C++ implementation in
 * src/common/Cryptography/Authentication/SRP6.cpp.
 *
 * battlenet_accounts  -> BnetSRP6v2 (PBKDF2-HMAC-SHA512, 2048-bit N, g=2)
 *                      or legacy BnetSRP6v1 (double SHA256, 1024-bit N, g=2)
 * account (game rows) -> GruntSRP6 (SHA1, 256-bit N, g=7)
 *
 * All verifiers are stored little-endian in the database.
 */
class Srp6
{
    private const N_GRUNT = '894B645E89E1535BBDAD5B8B290650530801B18EBFBF5E8FAB3C82872A3E9BB7';

    private const N_BNET_V1 = '86A7F6DEEB306CE519770FE37D556F29944132554DED0BD68205E27F3231FEF5A10108238A3150C59CAF7B0B6478691C13A6ACF5E1B5ADAFD4A943D4A21A142B800E8A55F8BFBAC700EB77A7235EE5A609E350EA9FC19F10D921C2FA832E4461B7125D38D254A0BE873DFC27858ACB3F8B9F258461E4373BC3A6C2A9634324AB';

    private const N_BNET_V2 = 'AC6BDB41324A9A9BF166DE5E1389582FAF72B6651987EE07FC3192943DB56050A37329CBB4A099ED8193E0757767A13DD52312AB4B03310DCD7F48A9DA04FD50E8083969EDB767B0CF6095179A163AB3661A05FBD5FAAAE82918A9962F0B93B855F97993EC975EEAA80D740ADBF4FF747359D041D5C33EA71D281E446B14773BCA97B43A23FB801676BD207A436C6481F1D2B9078717461A5B9D32E688F87748544523B524B0D57D5EA77A2775D2ECFA032CFBDBF52FB3786160279004E57AE6AF874E7303CE53299CCC041C7BC308D82A5698F3A8D0C38271AE35F8E9DBFBB694B5C803D89F7AE435DE236D525F54759B65E372FCD68EF20FA7111F9E4AFF73';

    private const PBKDF2_ITERATIONS = 15000;

    /** account (game) table verifier: SHA1, g=7, N=256-bit, 32-byte LE. */
    public static function gameVerifier(string $username, string $password, string $salt): string
    {
        $up = strtoupper($username . ':' . $password);
        $h1 = sha1($up, true);
        $h2 = sha1($salt . $h1, true);
        $x = self::importLE($h2);
        $v = gmp_powm(gmp_init(7), $x, self::n(self::N_GRUNT));
        return str_pad(self::exportLE($v), 32, "\0", STR_PAD_RIGHT);
    }

    /** battlenet_accounts srp_version=2 verifier: PBKDF2-HMAC-SHA512, 256-byte LE. */
    public static function bnetV2Verifier(string $email, string $password, string $salt): string
    {
        $srpUser = strtoupper(hash('sha256', strtoupper($email)));
        $xBytes = hash_pbkdf2('sha512', $srpUser . ':' . $password, $salt, self::PBKDF2_ITERATIONS, 64, true);

        $x = gmp_import($xBytes, 1, GMP_MSW_FIRST | GMP_BIG_ENDIAN);
        if (ord($xBytes[0]) & 0x80) {
            $x = gmp_sub($x, gmp_pow(2, 512));
        }

        $n = self::n(self::N_BNET_V2);
        // g^x mod N; OpenSSL BN_mod_exp accepts negative exponents via modular
        // inverse - gmp_powm does not, so handle that case explicitly.
        if (gmp_sign($x) < 0) {
            $v = gmp_invert(gmp_powm(gmp_init(2), gmp_neg($x), $n), $n);
        } else {
            $v = gmp_powm(gmp_init(2), $x, $n);
        }
        return str_pad(self::exportLE($v), 256, "\0", STR_PAD_RIGHT);
    }

    /** battlenet_accounts srp_version=1 verifier: double SHA256, 128-byte LE. */
    public static function bnetV1Verifier(string $email, string $password, string $salt): string
    {
        $inner = strtoupper(hash('sha256', strtoupper($email)) . ':' . substr($password, 0, 16));
        $x = self::importLE(hash('sha256', $salt . hash('sha256', $inner, true), true));
        $v = gmp_powm(gmp_init(2), $x, self::n(self::N_BNET_V1));
        return str_pad(self::exportLE($v), 128, "\0", STR_PAD_RIGHT);
    }

    /** Version-aware check used by website login. */
    public static function verifyBnet(string $email, string $password, int $srpVersion, string $salt, string $verifier): bool
    {
        $calc = match ($srpVersion) {
            1 => self::bnetV1Verifier($email, $password, $salt),
            2 => self::bnetV2Verifier($email, $password, $salt),
            default => null,
        };
        return $calc !== null && hash_equals($verifier, $calc);
    }

    public static function salt(): string
    {
        return random_bytes(32);
    }

    private static function n(string $hex): GMP
    {
        return gmp_init($hex, 16);
    }

    private static function importLE(string $bytes): GMP
    {
        return gmp_import($bytes, 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
    }

    private static function exportLE(GMP $n): string
    {
        return gmp_export($n, 1, GMP_LSW_FIRST | GMP_LITTLE_ENDIAN);
    }
}
