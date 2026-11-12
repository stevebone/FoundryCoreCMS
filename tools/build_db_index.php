<?php
/**
 * Rebuild cms.db_item_index — the denormalized item-browse index used by
 * the /db pages. Run after changing hotfixes custom items; the CSV importer
 * rebuilds it automatically after importing item tables.
 *
 * Usage: php tools/build_db_index.php
 */

if (PHP_SAPI !== 'cli') {
    exit("CLI only.\n");
}

require dirname(__DIR__) . '/app/bootstrap.php';

$n = App\Core\Database::rebuildIndex();
echo 'db_item_index rebuilt: ' . number_format($n) . " rows\n";
