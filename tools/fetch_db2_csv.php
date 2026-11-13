<?php
/**
 * fetch_db2_csv.php — download whitelisted DB2 tables as CSV from wago.tools.
 *
 * Usage: php tools/fetch_db2_csv.php <csv-dir> <build> [tables]
 *   csv-dir : target folder. Relative paths resolve against the web root.
 *   build   : full client build, e.g. 12.1.7.70740
 *   tables  : optional comma-separated subset of whitelisted db2 names
 *
 * Writes {Table}.{build}.csv per table — the filename convention
 * import_db2_csv.php already picks up (newest build wins).
 */
if (PHP_SAPI !== 'cli') {
    exit("CLI only\n");
}
$dir   = $argv[1] ?? '';
$build = $argv[2] ?? '';
if ($dir === '' || !preg_match('/^\d+\.\d+\.\d+\.\d+$/', $build)) {
    fwrite(STDERR, "Usage: php tools/fetch_db2_csv.php <csv-dir> <build> [tables]\n");
    exit(1);
}
if (!preg_match('#^([a-zA-Z]:[/\\\\]|[/\\\\])#', $dir)) {
    $dir = dirname(__DIR__) . '/' . $dir;
}
if (!is_dir($dir) && !mkdir($dir, 0777, true)) {
    fwrite(STDERR, "Could not create directory: $dir\n");
    exit(1);
}
$TABLES = require __DIR__ . '/db2_tables.php';

$only = null;
if (isset($argv[3]) && $argv[3] !== '') {
    $list = explode(',', $argv[3]);
    foreach ($list as $t) {
        if (!isset($TABLES[$t])) {
            fwrite(STDERR, "Unknown db2 table: $t\n");
            exit(1);
        }
    }
    $only = array_flip($list);
}
$names = $only === null ? array_keys($TABLES) : array_keys($only);

// wago.tools rate-limits rapid requests: space them out and retry failures.
function fetchCsv(string $table, string $build): ?string
{
    $url = 'https://wago.tools/db2/' . $table . '/csv?build=' . $build;
    $ctx = stream_context_create(['http' => [
        'timeout'          => 90,
        'user_agent'       => 'TrinityCore-CMS db2-fetch/1.0',
        'follow_location'  => 1,
        'ignore_errors'    => true,
    ]]);
    for ($try = 1; $try <= 3; $try++) {
        $body = @file_get_contents($url, false, $ctx);
        $code = 0;
        foreach ($http_response_header ?? [] as $h) {
            if (preg_match('#^HTTP/\S+\s+(\d+)#', $h, $m)) {
                $code = (int) $m[1];
            }
        }
        if ($code === 200 && is_string($body) && $body !== '' && $body[0] !== '<') {
            return $body;
        }
        if ($try < 3) {
            sleep(5);
        }
    }
    return null;
}

$ok = $fail = 0;
foreach ($names as $name) {
    $csv = fetchCsv($name, $build);
    if ($csv === null) {
        echo str_pad($name, 28) . "FAILED\n";
        $fail++;
    } else {
        $out = rtrim($dir, '/\\') . '/' . $name . '.' . $build . '.csv';
        file_put_contents($out, $csv);
        echo str_pad($name, 28) . number_format(strlen($csv)) . " bytes\n";
        $ok++;
    }
    usleep(1500000);
}
echo "Fetched $ok tables, $fail failed.\n";
exit($fail > 0 ? 1 : 0);
