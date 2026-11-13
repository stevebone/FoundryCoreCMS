<?php
/**
 * Import db2 CSV exports into the `cms` database.
 *
 * Usage:  php tools/import_db2_csv.php <csv-dir>
 *
 * Expects files named {Table}.{build}.csv with a header row of db2 field
 * names (e.g. ItemSparse.12.1.7.70740.csv). Columns map by header name into
 * cms.<snake_case_table>; column types are inferred by sampling the data
 * (BIGINT / DOUBLE / TEXT). Re-running truncates and re-imports.
 */

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

$dir = $argv[1] ?? null;
if (!$dir || !is_dir($dir)) {
    fwrite(STDERR, "Usage: php tools/import_db2_csv.php <csv-dir>\n");
    exit(1);
}

// Whitelisted db2 files -> cms table names (shared with fetch_db2_csv.php).
$TABLES = require __DIR__ . '/db2_tables.php';

// Non-ID columns the app filters/joins on (added after import alongside the PK).
const EXTRA_INDEXES = [
    'item_modified_appearance' => 'ADD INDEX idx_ima_item (ItemID, ItemAppearanceModifierID)',
    'item_sub_class'           => 'ADD INDEX idx_isc_lookup (ClassID, SubClassID)',
    'item_class'               => 'ADD INDEX idx_ic_class (ClassID)',
    'guild_emblem'             => 'ADD INDEX idx_ge_emblem (EmblemID)',
    'spell_misc'               => 'ADD INDEX idx_sm_spell (SpellID)',
    'spell_effect'             => 'ADD INDEX idx_se_spell (SpellID, EffectIndex)',
];

// Optional second arg: only import these db2 table names (comma-separated).
$only = isset($argv[2]) ? array_flip(explode(',', $argv[2])) : null;

$cfg = require dirname(__DIR__) . '/config.php';
$db = $cfg['db'];
$pdo = new PDO(
    "mysql:host={$db['host']};port={$db['port']};dbname=cms;charset=utf8mb4",
    $db['user'],
    $db['pass'],
    [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
);

// Column-name -> SQL type. Anything else is inferred by sampling values.
function colType(array $samples): string
{
    $int = true;
    $any = false;
    foreach ($samples as $v) {
        if ($v === '' || $v === null) {
            continue;
        }
        $any = true;
        if (!is_numeric($v)) {
            return 'TEXT';
        }
        if (!preg_match('/^-?\d+$/', $v)) {
            $int = false;
        }
    }
    if (!$any) {
        return 'TEXT'; // all-empty column: safest default
    }
    return $int ? 'BIGINT' : 'DOUBLE';
}

// First pass: pick the newest build per table (glob order is not reliable
// when multiple exports of the same db2 exist side by side).
$done = $skipped = 0;
$files = [];
foreach (glob(rtrim($dir, '/\\') . '/*.csv') as $file) {
    $base = basename($file);
    if (str_contains($base, '(1)') || !preg_match('/^(\w+)\.([\d.]+)\.csv$/', $base, $m)) {
        continue; // duplicate downloads / artifacts
    }
    $table = $TABLES[$m[1]] ?? null;
    if ($table === null || ($only !== null && !isset($only[$m[1]]))) {
        $skipped++;
        continue;
    }
    if (!isset($files[$table]) || version_compare($m[2], $files[$table][1], '>')) {
        $files[$table] = [$file, $m[2]];
    }
}
foreach ($files as $table => [$file, $ver]) {

    $fh = fopen($file, 'rb');
    $header = fgetcsv($fh, escape: '');
    if (!$header) {
        fclose($fh);
        continue;
    }
    $cols = array_map('trim', $header);

    // Sample up to 500 rows for type inference.
    $samples = array_fill(0, count($cols), []);
    $rows = [];
    while (($row = fgetcsv($fh, escape: '')) !== false && count($rows) < 500) {
        foreach ($cols as $i => $c) {
            if (count($samples[$i]) < 500 && ($row[$i] ?? '') !== '') {
                $samples[$i][] = $row[$i];
            }
        }
        $rows[] = $row;
    }

    $defs = [];
    foreach ($cols as $i => $c) {
        $defs[] = "`{$c}` " . colType($samples[$i]);
    }
    $pdo->exec("DROP TABLE IF EXISTS `{$table}`");
    $pdo->exec("CREATE TABLE `{$table}` (" . implode(',', $defs) . ") ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Bulk insert: header + sampled rows, then the rest of the file.
    $pdo->beginTransaction();
    $colList = '`' . implode('`,`', $cols) . '`';
    $batch = 300;
    $placeholders = '(' . rtrim(str_repeat('?,', count($cols)), ',') . ')';
    $sql = "INSERT INTO `{$table}` ($colList) VALUES " . implode(',', array_fill(0, $batch, $placeholders));
    $stmt = $pdo->prepare($sql);

    $width = count($cols);
    $norm = function (array $row) use ($width): array {
        return array_map(
            static fn ($v) => $v === '' ? null : $v,
            array_pad(array_slice($row, 0, $width), $width, null)
        );
    };

    $flush = function (array $chunk) use ($stmt, $pdo, $colList, $batch, $placeholders, $table) {
        if (!$chunk) {
            return;
        }
        if (count($chunk) === $batch) {
            $stmt->execute(array_merge(...$chunk));
        } else {
            $pdo->prepare("INSERT INTO `{$table}` ($colList) VALUES "
                . implode(',', array_fill(0, count($chunk), $placeholders))
            )->execute(array_merge(...$chunk));
        }
    };

    $inserted = 0;
    $write = function () use (&$rows, &$inserted, $flush, $norm) {
        $rows = array_map($norm, $rows);
        foreach (array_chunk($rows, 300) as $chunk) {
            $flush($chunk);
            $inserted += count($chunk);
        }
        $rows = [];
    };
    $write(); // sampled rows

    $rows = [];
    while (($row = fgetcsv($fh, escape: '')) !== false) {
        $rows[] = $row;
        if (count($rows) >= 3000) {
            $write();
        }
    }
    $write();
    $pdo->commit();
    fclose($fh);

    if (in_array('ID', $cols, true)) {
        $pdo->exec("ALTER TABLE `{$table}` ADD PRIMARY KEY (`ID`)");
    }
    if (isset(EXTRA_INDEXES[$table])) {
        $pdo->exec("ALTER TABLE `{$table}` " . EXTRA_INDEXES[$table]);
    }
    echo str_pad($table, 28) . number_format($inserted) . " rows\n";
    $done++;
}
echo "Imported $done tables ($skipped non-whitelisted files skipped).\n";

// Rebuild the db-browse index when item tables were (re)imported.
if ($done && ($only === null || array_intersect(array_keys($only),
        ['Item', 'ItemSparse', 'ItemSearchName', 'ItemModifiedAppearance', 'ItemAppearance']))) {
    require dirname(__DIR__) . '/app/bootstrap.php';
    echo 'db_item_index rebuilt: ' . number_format(App\Core\Database::rebuildIndex()) . " rows\n";
}
