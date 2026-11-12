<?php

namespace App\Core;

use PDO;

/**
 * Database browsing service: item search + detail lookups over the cms
 * db2 tables. Gated by the 'db.enabled' site setting and login.
 */
class Database
{
    public const PER_PAGE = 50;

    public static function enabled(): bool
    {
        return SiteSettings::get('db.enabled') === '1';
    }

    /** DB browsing allowed for this request (enabled + logged in). */
    public static function allowed(): bool
    {
        return self::enabled() && current_user() !== null;
    }

    /**
     * Denormalized browse index: every named item from cms.item_search_name,
     * cms.item_sparse (sparse-only entries), and the hotfixes custom-item
     * overrides, with class/subclass/slot/icon resolved. Rebuilt on demand —
     * the CSV importer rebuilds it automatically; run
     * `php tools/build_db_index.php` after changing hotfixes customs.
     */
    public static function rebuildIndex(): int
    {
        $pdo = Db::cms();
        $pdo->exec('DROP TABLE IF EXISTS db_item_index');
        $pdo->exec(
            'CREATE TABLE db_item_index (
                id BIGINT NOT NULL PRIMARY KEY,
                name TEXT NOT NULL,
                quality BIGINT NULL,
                ilvl BIGINT NULL,
                reqlevel BIGINT NULL,
                classid BIGINT NULL,
                subclassid BIGINT NULL,
                invtype BIGINT NULL,
                fdid BIGINT NULL,
                KEY idx_name (name(32)),
                KEY idx_quality (quality),
                KEY idx_class (classid, subclassid),
                KEY idx_invtype (invtype),
                KEY idx_ilvl (ilvl)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
        );

        $pdo->exec(
            'INSERT IGNORE INTO db_item_index (id, name, quality, ilvl, reqlevel, classid, subclassid, invtype, fdid)
             SELECT n.ID, n.name, n.q, n.ilvl, n.req,
                    COALESCE(i.ClassID, hi.ClassID),
                    COALESCE(i.SubclassID, hi.SubclassID),
                    COALESCE(i.InventoryType, hi.InventoryType),
                    COALESCE(NULLIF(i.IconFileDataID, 0), a.DefaultIconFileDataID,
                             NULLIF(hi.IconFileDataID, 0), ha.DefaultIconFileDataID)
             FROM (
                 SELECT 0 AS prio, ID, Display AS name, OverallQualityID AS q,
                        ItemLevel AS ilvl, RequiredLevel AS req
                 FROM hotfixes.item_search_name
                 UNION ALL
                 SELECT 0, hs.ID, hs.Display, hs.OverallQualityID, hs.ItemLevel, hs.RequiredLevel
                 FROM hotfixes.item_sparse hs
                 WHERE hs.Display <> ""
                   AND NOT EXISTS (SELECT 1 FROM hotfixes.item_search_name x WHERE x.ID = hs.ID)
                 UNION ALL
                 SELECT 1, ID, Display_lang, OverallQualityID, ItemLevel, RequiredLevel
                 FROM item_search_name
                 UNION ALL
                 SELECT 1, s.ID, s.Display_lang, s.OverallQualityID, s.ItemLevel, s.RequiredLevel
                 FROM item_sparse s
                 WHERE s.Display_lang <> ""
                   AND NOT EXISTS (SELECT 1 FROM item_search_name x WHERE x.ID = s.ID)
             ) n
             LEFT JOIN item i ON i.ID = n.ID
             LEFT JOIN item_modified_appearance x
                 ON x.ItemID = n.ID AND x.ItemAppearanceModifierID = 0
             LEFT JOIN item_appearance a ON a.ID = x.ItemAppearanceID
             LEFT JOIN hotfixes.item hi ON hi.ID = n.ID
             LEFT JOIN hotfixes.item_modified_appearance hx
                 ON hx.ItemID = n.ID AND hx.ItemAppearanceModifierID = 0
             LEFT JOIN hotfixes.item_appearance ha ON ha.ID = hx.ItemAppearanceID
             WHERE n.name <> ""
             ORDER BY n.prio, n.ID'
        );

        return (int) $pdo->query('SELECT COUNT(*) FROM db_item_index')->fetchColumn();
    }

    /** True once the browse index table exists. */
    private static function ensureIndex(): bool
    {
        static $ok = null;
        if ($ok === null) {
            $exists = (bool) Db::cms()
                ->query("SHOW TABLES LIKE 'db_item_index'")
                ->fetchColumn();
            $ok = $exists ?: self::rebuildIndex() > 0;
        }
        return $ok;
    }

    /**
     * Paginated item search. Filters (all optional):
     *   q          name substring (or exact ID when numeric)
     *   quality    exact OverallQualityID 0-7
     *   class      item.ClassID
     *   subclass   item.SubclassID (only with class)
     *   slot       item.InventoryType
     *   ilvl_min / ilvl_max  ItemLevel range
     *   page       1-based page
     * Returns ['rows' => [...], 'total' => int, 'page' => int, 'pages' => int].
     */
    public static function searchItems(array $f): array
    {
        if (!self::ensureIndex()) {
            return ['rows' => [], 'total' => 0, 'page' => 1, 'pages' => 1];
        }
        $cms = Db::cms();
        [$where, $bind] = self::itemWhere($f);

        $page = max(1, (int) ($f['page'] ?? 1));
        $stmt = $cms->prepare("SELECT COUNT(*) FROM db_item_index n {$where}");
        $stmt->execute($bind);
        $total = (int) $stmt->fetchColumn();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $pages);
        $offset = ($page - 1) * self::PER_PAGE;

        $sql = "SELECT n.id, n.name, n.quality, n.ilvl, n.reqlevel AS reqLevel,
                    n.classid AS classId, n.subclassid AS subclassId,
                    n.invtype AS invType, n.fdid,
                    sc.DisplayName_lang AS subclassName,
                    ic.ClassName_lang AS className
                FROM db_item_index n
                LEFT JOIN item_class ic ON ic.ClassID = n.classid
                LEFT JOIN item_sub_class sc
                    ON sc.ClassID = n.classid AND sc.SubClassID = n.subclassid
                {$where}
                ORDER BY n.id
                LIMIT " . self::PER_PAGE . ' OFFSET ' . $offset;

        $stmt = $cms->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll();
        foreach ($rows as &$r) {
            $r['iconUrl'] = Wow::iconUrl($r['fdid'] !== null ? (int) $r['fdid'] : null);
        }
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    private static function itemWhere(array $f): array
    {
        $where = [];
        $bind = [];
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            if (ctype_digit($q)) {
                $where[] = '(n.id = ? OR n.name LIKE ?)';
                $bind[] = (int) $q;
                $bind[] = '%' . $q . '%';
            } else {
                $where[] = 'n.name LIKE ?';
                $bind[] = '%' . $q . '%';
            }
        }
        foreach (['quality' => 'n.quality', 'class' => 'n.classid',
                  'subclass' => 'n.subclassid', 'slot' => 'n.invtype'] as $key => $col) {
            if (($f[$key] ?? '') !== '' && is_numeric($f[$key])) {
                $where[] = "$col = ?";
                $bind[] = (int) $f[$key];
            }
        }
        if (($f['ilvl_min'] ?? '') !== '' && is_numeric($f['ilvl_min'])) {
            $where[] = 'n.ilvl >= ?';
            $bind[] = (int) $f['ilvl_min'];
        }
        if (($f['ilvl_max'] ?? '') !== '' && is_numeric($f['ilvl_max'])) {
            $where[] = 'n.ilvl <= ?';
            $bind[] = (int) $f['ilvl_max'];
        }
        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $bind];
    }

    /**
     * Related data for an item detail page: disenchant results, quest
     * rewards, creature drops, same-model items. Arrays may be empty —
     * the template renders an empty state per tab.
     */
    public static function itemRelations(int $id): array
    {
        $cms = Db::cms();

        $stmt = $cms->prepare(
            'SELECT l.Item AS id, l.Chance, l.MinCount, l.MaxCount,
                    n.name, n.quality, n.ilvl, n.fdid
             FROM world.disenchant_loot_template l
             JOIN db_item_index n ON n.id = l.Item
             WHERE l.Entry = ? ORDER BY l.Chance DESC LIMIT 50'
        );
        $stmt->execute([$id]);
        $disenchants = $stmt->fetchAll();

        $rewards = [];
        foreach ([1, 2, 3, 4] as $n) {
            $stmt = $cms->prepare(
                "SELECT ID AS id, LogTitle AS title, RewardAmount{$n} AS qty
                 FROM world.quest_template WHERE RewardItem{$n} = ? LIMIT 50"
            );
            $stmt->execute([$id]);
            foreach ($stmt->fetchAll() as $r) {
                $rewards[] = $r;
            }
            if (count($rewards) >= 50) {
                break;
            }
        }

        $stmt = $cms->prepare(
            'SELECT ct.name, l.Chance, l.MinCount, l.MaxCount
             FROM world.creature_loot_template l
             JOIN world.creature_template ct ON ct.entry = l.Entry
             WHERE l.Item = ? ORDER BY l.Chance DESC LIMIT 50'
        );
        $stmt->execute([$id]);
        $droppedBy = $stmt->fetchAll();

        $stmt = $cms->prepare(
            'SELECT DISTINCT x2.ItemID AS id
             FROM item_modified_appearance x2
             WHERE x2.ItemAppearanceID = (
                 SELECT ItemAppearanceID FROM item_modified_appearance
                 WHERE ItemID = ? AND ItemAppearanceModifierID = 0 LIMIT 1)
               AND x2.ItemID <> ?
             ORDER BY x2.ItemID LIMIT 50'
        );
        $stmt->execute([$id, $id]);
        $sameIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $sameModel = [];
        if ($sameIds) {
            $in = implode(',', array_map('intval', $sameIds));
            $sameModel = $cms->query(
                "SELECT n.id, n.name, n.quality, n.ilvl, n.fdid,
                        sc.DisplayName_lang AS subclassName, ic.ClassName_lang AS className
                 FROM db_item_index n
                 LEFT JOIN item_class ic ON ic.ClassID = n.classid
                 LEFT JOIN item_sub_class sc ON sc.ClassID = n.classid AND sc.SubClassID = n.subclassid
                 WHERE n.id IN ($in) ORDER BY n.id"
            )->fetchAll();
        }

        return [
            'disenchants' => $disenchants,
            'rewards' => $rewards,
            'droppedBy' => $droppedBy,
            'sameModel' => $sameModel,
        ];
    }

    private const QTYPE_WHERE = [
        'normal' => 'NOT (q.Flags & 0x1000) AND NOT (q.Flags & 0x8000)',
        'daily' => '(q.Flags & 0x1000)',
        'weekly' => '(q.Flags & 0x8000)',
        'group' => '(q.SuggestedGroupNum > 0 OR q.QuestInfoID = 1)',
    ];

    private const QSORT_COLS = [
        'id' => 'q.ID',
        'name' => 'q.LogTitle',
        'level' => 'reqLevel',
        'zone' => 'zoneName',
        'type' => 'typeName',
    ];

    /**
     * Paginated quest search over world.quest_template joined with the
     * imported db2 tables. Filters (all optional):
     *   q         LogTitle substring (or exact ID when numeric)
     *   lvl_min / lvl_max  required-level range (ContentTuning.MinLevelSquish)
     *   type      normal | daily | weekly | group
     *   zone      resolved zone/sort name substring
     *   sort      id | name | level | zone | type ; dir asc|desc
     *   page      1-based page
     * Rows carry: id, name, reqLevel, zoneName, typeName, icon, rewards[].
     */
    public static function searchQuests(array $f): array
    {
        $cms = Db::cms();
        $joins = 'LEFT JOIN content_tuning ct ON ct.ID = q.ContentTuningID '
            . 'LEFT JOIN area_table at ON at.ID = q.QuestSortID '
            . 'LEFT JOIN quest_sort qs ON qs.ID = -q.QuestSortID '
            . 'LEFT JOIN quest_info qi ON qi.ID = q.QuestInfoID';
        [$where, $bind] = self::questWhere($f);

        $page = max(1, (int) ($f['page'] ?? 1));
        $stmt = $cms->prepare("SELECT COUNT(*) FROM world.quest_template q {$joins} {$where}");
        $stmt->execute($bind);
        $total = (int) $stmt->fetchColumn();
        $pages = max(1, (int) ceil($total / self::PER_PAGE));
        $page = min($page, $pages);
        $offset = ($page - 1) * self::PER_PAGE;

        $sort = self::QSORT_COLS[$f['sort'] ?? ''] ?? 'q.ID';
        $dir = strtolower((string) ($f['dir'] ?? '')) === 'desc' ? 'DESC' : 'ASC';
        $order = "ORDER BY {$sort} IS NULL, {$sort} {$dir}, q.ID";

        $sql = 'SELECT q.ID AS id, q.LogTitle AS name,
                    ct.MinLevelSquish AS reqLevel,
                    COALESCE(at.AreaName_lang, qs.SortName_lang) AS zoneName,
                    IF(q.QuestInfoID > 0, qi.InfoName_lang, NULL) AS typeName,
                    q.Flags AS flags, q.SuggestedGroupNum AS groupNum,'
            . implode(',', array_map(
                static fn ($i) => "q.RewardChoiceItemID{$i} AS rc{$i}, q.RewardChoiceItemQuantity{$i} AS rcq{$i}",
                range(1, 6)
            )) . ','
            . implode(',', array_map(
                static fn ($i) => "q.RewardItem{$i} AS rf{$i}, q.RewardAmount{$i} AS ra{$i}",
                range(1, 4)
            )) .
            " FROM world.quest_template q {$joins} {$where} {$order}
              LIMIT " . self::PER_PAGE . ' OFFSET ' . $offset;

        $stmt = $cms->prepare($sql);
        $stmt->execute($bind);
        $rows = $stmt->fetchAll();

        // Batch-resolve reward item ids -> names/icons from the browse index.
        $ids = [];
        foreach ($rows as $r) {
            for ($i = 1; $i <= 6; $i++) {
                if ($r["rc{$i}"]) {
                    $ids[] = (int) $r["rc{$i}"];
                }
            }
            for ($i = 1; $i <= 4; $i++) {
                if ($r["rf{$i}"]) {
                    $ids[] = (int) $r["rf{$i}"];
                }
            }
        }
        $items = [];
        if ($ids) {
            self::ensureIndex();
            $in = implode(',', array_map('intval', array_unique($ids)));
            foreach ($cms->query(
                "SELECT id, name, quality, fdid FROM db_item_index WHERE id IN ({$in})"
            )->fetchAll() as $it) {
                $items[(int) $it['id']] = $it;
            }
        }

        foreach ($rows as &$r) {
            $r['icon'] = ($r['flags'] & 0x8000) ? 'weekly' : (($r['flags'] & 0x1000) ? 'daily' : 'normal');
            $r['rewards'] = [];
            for ($i = 1; $i <= 6; $i++) {
                if ($r["rc{$i}"] && isset($items[(int) $r["rc{$i}"]])) {
                    $it = $items[(int) $r["rc{$i}"]];
                    $r['rewards'][] = [
                        'id' => (int) $it['id'], 'name' => $it['name'],
                        'quality' => (int) $it['quality'],
                        'qty' => (int) $r["rcq{$i}"],
                        'iconUrl' => Wow::iconUrl($it['fdid'] !== null ? (int) $it['fdid'] : null),
                    ];
                }
            }
            for ($i = 1; $i <= 4; $i++) {
                if ($r["rf{$i}"] && isset($items[(int) $r["rf{$i}"]])) {
                    $it = $items[(int) $r["rf{$i}"]];
                    $r['rewards'][] = [
                        'id' => (int) $it['id'], 'name' => $it['name'],
                        'quality' => (int) $it['quality'],
                        'qty' => (int) $r["ra{$i}"],
                        'iconUrl' => Wow::iconUrl($it['fdid'] !== null ? (int) $it['fdid'] : null),
                    ];
                }
            }
            foreach (array_keys($r) as $k) {
                if (preg_match('/^(rc|rcq|rf|ra|flags|groupNum)/', $k)) {
                    unset($r[$k]);
                }
            }
        }
        return ['rows' => $rows, 'total' => $total, 'page' => $page, 'pages' => $pages];
    }

    private static function questWhere(array $f): array
    {
        $where = [];
        $bind = [];
        $q = trim((string) ($f['q'] ?? ''));
        if ($q !== '') {
            if (ctype_digit($q)) {
                $where[] = '(q.ID = ? OR q.LogTitle LIKE ?)';
                $bind[] = (int) $q;
                $bind[] = '%' . $q . '%';
            } else {
                $where[] = 'q.LogTitle LIKE ?';
                $bind[] = '%' . $q . '%';
            }
        }
        if (($f['lvl_min'] ?? '') !== '' && is_numeric($f['lvl_min'])) {
            $where[] = 'ct.MinLevelSquish >= ?';
            $bind[] = (int) $f['lvl_min'];
        }
        if (($f['lvl_max'] ?? '') !== '' && is_numeric($f['lvl_max'])) {
            $where[] = 'ct.MinLevelSquish <= ?';
            $bind[] = (int) $f['lvl_max'];
        }
        if (isset(self::QTYPE_WHERE[$f['type'] ?? ''])) {
            $where[] = self::QTYPE_WHERE[$f['type']];
        }
        $zone = trim((string) ($f['zone'] ?? ''));
        if ($zone !== '') {
            $where[] = 'COALESCE(at.AreaName_lang, qs.SortName_lang) LIKE ?';
            $bind[] = '%' . $zone . '%';
        }
        return [$where ? 'WHERE ' . implode(' AND ', $where) : '', $bind];
    }

    /** True when a cms table exists (cached — used for optional db2 imports). */
    public static function hasTable(string $table): bool
    {
        static $tables = null;
        if ($tables === null) {
            $tables = array_fill_keys(
                Db::cms()->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN),
                true
            );
        }
        return isset($tables[$table]);
    }

    /**
     * Full quest detail: header facts, text blocks, objectives, rewards and
     * starter/ender relations. Returns null when the quest doesn't exist.
     */
    public static function questDetail(int $id): ?array
    {
        $cms = Db::cms();
        $stmt = $cms->prepare(
            'SELECT q.ID AS id, q.LogTitle AS name, q.LogDescription AS logDesc,
                    q.QuestDescription AS description, q.QuestCompletionLog AS completion,
                    q.Expansion AS expansion, q.AllowableRaces AS races,
                    q.Flags AS flags, q.StartItem AS startItem,
                    ct.MinLevelSquish AS reqLevel, ct.MaxLevelSquish AS level,
                    COALESCE(at.AreaName_lang, qs.SortName_lang) AS zoneName,
                    IF(q.QuestInfoID > 0, qi.InfoName_lang, NULL) AS typeName,
                    q.RewardXPDifficulty AS xpDiff, q.RewardXPMultiplier AS xpMult,
                    ct.XpMultQuest AS xpTuningMult,
                    q.RewardTitle AS titleId, q.RewardSpell AS spellId,
                    q.RewardSkillLineID AS skillLine, q.RewardNumSkillUps AS skillUps,
                    ri.CompletionText AS progress
             FROM world.quest_template q
             LEFT JOIN content_tuning ct ON ct.ID = q.ContentTuningID
             LEFT JOIN area_table at ON at.ID = q.QuestSortID
             LEFT JOIN quest_sort qs ON qs.ID = -q.QuestSortID
             LEFT JOIN quest_info qi ON qi.ID = q.QuestInfoID
             LEFT JOIN world.quest_request_items ri ON ri.ID = q.ID
             WHERE q.ID = ?'
        );
        $stmt->execute([$id]);
        $q = $stmt->fetch();
        if (!$q) {
            return null;
        }

        $q['icon'] = ($q['flags'] & 0x8000) ? 'weekly' : (($q['flags'] & 0x1000) ? 'daily' : 'normal');
        $q['side'] = self::questSide($q['races']);
        $tags = [];
        if ($q['flags'] & 0x8) {
            $tags[] = t('db.sharable');
        }
        $q['tags'] = $tags;

        $q['expansionName'] = Wow::expansionName((int) $q['expansion']);

        $q['objectives'] = self::questObjectives($id);
        $q['starters'] = self::questGivers($id, true);
        $q['enders'] = self::questGivers($id, false);
        // Race mask says "both" -> infer side from the quest-giving NPCs'
        // faction_template groups (falls back to the quest chain ancestors).
        if ($q['side'] === 'both') {
            $q['side'] = self::questSideFromNpcs($id);
        }
        $q['rewards'] = self::questRewards($id, $q);
        $q['maps'] = self::questMaps($id);
        return $q;
    }

    /**
     * Quest objective maps: for each distinct quest_poi.UiMapID resolve the
     * ui_map_assignment row (prefer AreaID>0), transform quest_poi_points world
     * coords into normalized u/v over the zone image, return per-map blocks.
     * Empty array when POI/assignment data is missing.
     */
    public static function questMaps(int $id): array
    {
        $w = Db::world();
        if (!self::hasTable('ui_map_assignment')) {
            return [];
        }
        try {
            $pois = $w->prepare('SELECT DISTINCT UiMapID FROM quest_poi WHERE QuestID = ? AND UiMapID IS NOT NULL');
            $pois->execute([$id]);
            $uiMapIds = array_map('intval', $pois->fetchAll(PDO::FETCH_COLUMN));
            // ObjectiveIndex < 0 marks the turn-in location -> render as a "?" pin.
            $turnin = $w->prepare('SELECT Idx1 FROM quest_poi WHERE QuestID = ? AND ObjectiveIndex < 0');
            $turnin->execute([$id]);
            $turninIdx = array_fill_keys(array_map('intval', $turnin->fetchAll(PDO::FETCH_COLUMN)), true);
            $pts = $w->prepare('SELECT Idx1, X, Y FROM quest_poi_points WHERE QuestID = ?');
            $pts->execute([$id]);
            $points = $pts->fetchAll();
        } catch (\Throwable) {
            return [];
        }
        if (!$uiMapIds || !$points) {
            return [];
        }

        $cms = Db::cms();
        $names = [];
        if (self::hasTable('ui_map')) {
            $in = implode(',', $uiMapIds);
            $names = $cms->query(
                "SELECT ID, Name_lang FROM ui_map WHERE ID IN ({$in})"
            )->fetchAll(PDO::FETCH_KEY_PAIR);
        }

        $maps = [];
        $aStmt = $cms->prepare(
            'SELECT AreaID, UiMin_0, UiMin_1, UiMax_0, UiMax_1,
                    Region_0, Region_1, Region_3, Region_4
             FROM ui_map_assignment WHERE UiMapID = ? ORDER BY OrderIndex'
        );
        foreach ($uiMapIds as $uiMapId) {
            $aStmt->execute([$uiMapId]);
            $rows = $aStmt->fetchAll();
            if (!$rows) {
                continue;
            }
            // prefer the row covering a real zone (AreaID>0), else first
            $a = $rows[0];
            foreach ($rows as $r) {
                if ((int) $r['AreaID'] > 0) {
                    $a = $r;
                    break;
                }
            }
            $areaId = (int) $a['AreaID'];
            if ($areaId <= 0) {
                continue;
            }
            // WoW world axes: +X = north, +Y = west. Image: +x = east, +y = south.
            // Region_0/Region_3 = world-X bounds, Region_1/Region_4 = world-Y bounds.
            // Both axes swap AND flip: u = (Ymax-Y)/dY (west→right), v = (Xmax-X)/dX (north→top).
            $minX = min((float) $a['Region_0'], (float) $a['Region_3']);
            $maxX = max((float) $a['Region_0'], (float) $a['Region_3']);
            $minY = min((float) $a['Region_1'], (float) $a['Region_4']);
            $maxY = max((float) $a['Region_1'], (float) $a['Region_4']);
            $wX = $maxX - $minX;
            $wY = $maxY - $minY;
            if ($wX <= 0 || $wY <= 0) {
                continue;
            }
            $uiMinX = (float) $a['UiMin_0'];
            $uiMaxX = (float) $a['UiMax_0'];
            $uiMinY = (float) $a['UiMin_1'];
            $uiMaxY = (float) $a['UiMax_1'];
            $out = [];
            foreach ($points as $p) {
                $u = ($maxY - (float) $p['Y']) / $wY;
                $v = ($maxX - (float) $p['X']) / $wX;
                if ($u < 0 || $u > 1 || $v < 0 || $v > 1) {
                    continue; // point not on this map
                }
                $out[] = [
                    'u' => $uiMinX + $u * ($uiMaxX - $uiMinX),
                    'v' => $uiMinY + $v * ($uiMaxY - $uiMinY),
                    'turnin' => isset($turninIdx[(int) $p['Idx1']]),
                ];
            }
            if (!$out) {
                continue;
            }
            $maps[] = [
                'uiMapId' => $uiMapId,
                'areaId' => $areaId,
                'name' => $names[$uiMapId] ?? '',
                'img' => 'https://wow.zamimg.com/images/wow/maps/enus/original/' . $areaId . '.jpg',
                'points' => $out,
            ];
        }
        return $maps;
    }

    /** Alliance|Horde|Both from the AllowableRaces mask vs chr_races factions. */
    private static function questSide($races): string
    {
        $races = (string) $races;
        if ($races === '-1' || $races === '18446744073709551615' || $races === '0') {
            return 'both';
        }
        static $masks = null;
        if ($masks === null) {
            $masks = ['a' => 0, 'h' => 0];
            foreach (Db::cms()->query('SELECT ID, Alliance FROM chr_races')->fetchAll() as $r) {
                $id = (int) $r['ID'];
                if ($id < 1 || $id > 62) {
                    continue; // beyond a 63-bit mask (custom races) — rare in quests
                }
                $masks[(int) $r['Alliance'] === 1 ? 'h' : 'a'] |= 1 << ($id - 1);
            }
        }
        $mask = (int) $races;
        $a = ($mask & $masks['a']) !== 0;
        $h = ($mask & $masks['h']) !== 0;
        return $a && $h ? 'both' : ($a ? 'alliance' : ($h ? 'horde' : 'both'));
    }

    /** Quest objectives with resolved target names. */
    private static function questObjectives(int $id): array
    {
        $cms = Db::cms();
        $stmt = $cms->prepare(
            'SELECT Type AS type, ObjectID AS objectId, Amount AS amount, Description AS descr
             FROM world.quest_objectives WHERE QuestID = ? AND Visible = 1
             ORDER BY `Order`, StorageIndex'
        );
        $stmt->execute([$id]);
        $rows = $stmt->fetchAll();
        if (!$rows) {
            return [];
        }
        // Resolve object names by type in one pass per kind.
        $ids = [0 => [], 1 => [], 2 => [], 3 => []];
        foreach ($rows as $r) {
            if (isset($ids[(int) $r['type']])) {
                $ids[(int) $r['type']][] = (int) $r['objectId'];
            }
        }
        $names = [];
        if ($ids[0] || $ids[3]) {
            $in = implode(',', array_map('intval', array_merge($ids[0], $ids[3])));
            $names += Db::world()
                ->query("SELECT entry, name FROM creature_template WHERE entry IN ({$in})")
                ->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        if ($ids[1] && self::ensureIndex()) {
            $in = implode(',', array_map('intval', $ids[1]));
            $names += $cms
                ->query("SELECT id, name FROM db_item_index WHERE id IN ({$in})")
                ->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        if ($ids[2]) {
            $in = implode(',', array_map('intval', $ids[2]));
            $names += Db::world()
                ->query("SELECT entry, name FROM gameobject_template WHERE entry IN ({$in})")
                ->fetchAll(PDO::FETCH_KEY_PAIR);
        }
        foreach ($rows as &$r) {
            $r['target'] = $names[(int) $r['objectId']] ?? null;
        }
        return $rows;
    }

    /**
     * Infer faction from the NPCs that give/complete the quest. Used when the
     * race mask is all-races ('both'): if every giver faction belongs to one
     * faction_template group (2=Alliance, 4=Horde) that side wins; neutral
     * (group 0) factions are ignored. Falls back to chain ancestors so
     * follow-up quests whose own NPCs are neutral still resolve.
     */
    private static function questSideFromNpcs(int $id): string
    {
        if (!self::hasTable('faction_template')) {
            return 'both';
        }
        $side = self::factionsToSide(self::questNpcFactions($id));
        if ($side !== 'both') {
            return $side;
        }
        $visited = [$id => true];
        $cur = $id;
        for ($i = 0; $i < 10; $i++) {
            $prev = (int) Db::world()
                ->query("SELECT PrevQuestID FROM quest_template_addon WHERE ID = {$cur}")
                ->fetchColumn();
            if (!$prev || isset($visited[$prev])) {
                break;
            }
            $visited[$prev] = true;
            $side = self::factionsToSide(self::questNpcFactions($prev));
            if ($side !== 'both') {
                return $side;
            }
            $cur = $prev;
        }
        return 'both';
    }

    /** Distinct creature_template.faction values of a quest's starters+enders. */
    private static function questNpcFactions(int $id): array
    {
        $stmt = Db::world()->prepare(
            'SELECT DISTINCT ct.faction FROM (
                 SELECT id FROM creature_queststarter WHERE quest = ?
                 UNION ALL SELECT id FROM creature_questender WHERE quest = ?
             ) s JOIN creature_template ct ON ct.entry = s.id WHERE ct.faction > 0'
        );
        $stmt->execute([$id, $id]);
        return array_map('intval', $stmt->fetchAll(PDO::FETCH_COLUMN));
    }

    /** alliance/horde when all faction groups agree, else both. */
    private static function factionsToSide(array $factions): string
    {
        if (!$factions) {
            return 'both';
        }
        $in = implode(',', $factions);
        $groups = Db::cms()
            ->query("SELECT DISTINCT FactionGroup FROM faction_template WHERE ID IN ({$in})")
            ->fetchAll(PDO::FETCH_COLUMN);
        $a = $h = false;
        foreach ($groups as $g) {
            $g = (int) $g;
            if ($g & 2) {
                $a = true;
            }
            if ($g & 4) {
                $h = true;
            }
        }
        if ($a && !$h) {
            return 'alliance';
        }
        if ($h && !$a) {
            return 'horde';
        }
        return 'both';
    }

    /** Creatures/gameobjects/items that start or end a quest: [{name,kind}]. */
    private static function questGivers(int $id, bool $starter): array
    {
        $cms = Db::cms();
        $table = $starter ? 'creature_queststarter' : 'creature_questender';
        $goTable = $starter ? 'gameobject_queststarter' : 'gameobject_questender';
        $out = [];
        $stmt = $cms->prepare(
            "SELECT ct.name, 'NPC' AS kind FROM world.{$table} s
             JOIN world.creature_template ct ON ct.entry = s.id WHERE s.quest = ?"
        );
        $stmt->execute([$id]);
        $out = $stmt->fetchAll();
        $stmt = $cms->prepare(
            "SELECT g.name, 'Object' AS kind FROM world.{$goTable} s
             JOIN world.gameobject_template g ON g.entry = s.id WHERE s.quest = ?"
        );
        $stmt->execute([$id]);
        return array_merge($out, $stmt->fetchAll());
    }

    /** Reward blocks: choice/fixed items, XP, reputation, title, spell. */
    private static function questRewards(int $id, array $q): array
    {
        $cms = Db::cms();
        $stmt = $cms->prepare(
            'SELECT ' . implode(',', array_map(
                static fn ($i) => "RewardChoiceItemID{$i} AS rc{$i}, RewardChoiceItemQuantity{$i} AS rq{$i}",
                range(1, 6)
            )) . ',' . implode(',', array_map(
                static fn ($i) => "RewardItem{$i} AS rf{$i}, RewardAmount{$i} AS ra{$i}",
                range(1, 4)
            )) . ',' . implode(',', array_map(
                static fn ($i) => "RewardFactionID{$i} AS fac{$i}, RewardFactionValue{$i} AS fav{$i}",
                range(1, 5)
            )) . ' FROM world.quest_template WHERE ID = ?'
        );
        $stmt->execute([$id]);
        $r = $stmt->fetch() ?: [];

        $resolve = function (array $ids) use ($cms): array {
            $ids = array_filter(array_map('intval', $ids));
            if (!$ids || !self::ensureIndex()) {
                return [];
            }
            $in = implode(',', $ids);
            $out = [];
            foreach ($cms->query(
                "SELECT id, name, quality, fdid FROM db_item_index WHERE id IN ({$in})"
            )->fetchAll(PDO::FETCH_UNIQUE) as $rid => $it) {
                $it['id'] = $rid; // FETCH_UNIQUE strips the key column
                $out[$rid] = $it;
            }
            return $out;
        };

        $choice = [];
        $ids = $resolve(array_map(static fn ($i) => $r["rc{$i}"] ?? 0, range(1, 6)));
        for ($i = 1; $i <= 6; $i++) {
            if (isset($ids[(int) ($r["rc{$i}"] ?? 0)])) {
                $it = $ids[(int) $r["rc{$i}"]];
                $choice[] = $it + ['qty' => (int) $r["rq{$i}"], 'iconUrl' => Wow::iconUrl((int) $it['fdid'])];
            }
        }
        $fixed = [];
        $ids = $resolve(array_map(static fn ($i) => $r["rf{$i}"] ?? 0, range(1, 4)));
        for ($i = 1; $i <= 4; $i++) {
            if (isset($ids[(int) ($r["rf{$i}"] ?? 0)])) {
                $it = $ids[(int) $r["rf{$i}"]];
                $fixed[] = $it + ['qty' => (int) $r["ra{$i}"], 'iconUrl' => Wow::iconUrl((int) $it['fdid'])];
            }
        }

        $rep = [];
        $facNames = [];
        if (self::hasTable('faction')) {
            $facIds = array_filter(array_map(static fn ($i) => (int) ($r["fac{$i}"] ?? 0), range(1, 5)));
            if ($facIds) {
                $in = implode(',', $facIds);
                $facNames = $cms->query(
                    "SELECT ID, Name_lang FROM faction WHERE ID IN ({$in})"
                )->fetchAll(PDO::FETCH_KEY_PAIR);
            }
        }
        for ($i = 1; $i <= 5; $i++) {
            $fid = (int) ($r["fac{$i}"] ?? 0);
            $val = (int) ($r["fav{$i}"] ?? 0);
            if ($fid && $val) {
                $rep[] = ['value' => $val, 'name' => $facNames[$fid] ?? "Faction #{$fid}"];
            }
        }

        // quest_xp is keyed by quest LEVEL (sQuestXPStore.LookupEntry(questLevel))
        // with Difficulty_0..9 columns indexed by RewardXPDifficulty.
        $xp = null;
        $lvl = (int) ($q['level'] ?? $q['reqLevel'] ?? 0);
        if ($lvl > 0 && self::hasTable('quest_xp')) {
            $diff = min(9, max(0, (int) $q['xpDiff']));
            $stmt = $cms->prepare("SELECT Difficulty_{$diff} AS xp FROM quest_xp WHERE ID = ?");
            $stmt->execute([$lvl]);
            $base = $stmt->fetchColumn();
            if ($base !== false && $base !== null) {
                $xp = (float) $base * (float) ($q['xpTuningMult'] ?: 1) * (float) ($q['xpMult'] ?: 1);
            }
        }

        $title = null;
        if ($q['titleId']) {
            $stmt = $cms->prepare('SELECT Name_lang FROM char_titles WHERE ID = ?');
            $stmt->execute([(int) $q['titleId']]);
            $title = $stmt->fetchColumn() ?: null;
        }
        $spell = null;
        if ($q['spellId']) {
            $stmt = $cms->prepare('SELECT Name_lang FROM spell_name WHERE ID = ?');
            $stmt->execute([(int) $q['spellId']]);
            $spell = $stmt->fetchColumn() ?: null;
        }

        return [
            'choice' => $choice, 'fixed' => $fixed, 'xp' => $xp,
            'rep' => $rep, 'title' => $title, 'spell' => $spell,
        ];
    }

    /** Zone/sort names that actually have quests — for the zone autocomplete. */
    public static function questZones(string $q): array
    {
        $q = trim($q);
        $like = '%' . $q . '%';
        $stmt = Db::cms()->prepare(
            'SELECT DISTINCT COALESCE(at.AreaName_lang, qs.SortName_lang) AS zone
             FROM world.quest_template q
             LEFT JOIN area_table at ON at.ID = q.QuestSortID
             LEFT JOIN quest_sort qs ON qs.ID = -q.QuestSortID
             WHERE COALESCE(at.AreaName_lang, qs.SortName_lang) LIKE ?
             ORDER BY zone LIMIT 10'
        );
        $stmt->execute([$like]);
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    /** Quest name suggestions for autocomplete: [{id,name}]. */
    public static function questSuggest(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        $stmt = Db::world()->prepare(
            'SELECT ID AS id, LogTitle AS name FROM quest_template
             WHERE LogTitle LIKE ? ORDER BY LogTitle LIMIT 12'
        );
        $stmt->execute(['%' . $q . '%']);
        return $stmt->fetchAll();
    }

    /** Item name suggestions for autocomplete: [{id,name}]. */
    public static function itemSuggest(string $q): array
    {
        $q = trim($q);
        if ($q === '') {
            return [];
        }
        $stmt = Db::cms()->prepare(
            'SELECT id, name FROM db_item_index
             WHERE name LIKE ? ORDER BY name LIMIT 12'
        );
        $stmt->execute(['%' . $q . '%']);
        return $stmt->fetchAll();
    }

    /** Item classes for the filter dropdown: [classId => name]. */
    public static function itemClasses(): array
    {
        return Db::cms()
            ->query('SELECT ClassID, ClassName_lang FROM item_class ORDER BY ClassID')
            ->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    /** Subclasses for a class: [subclassId => name]. */
    public static function itemSubClasses(int $classId): array
    {
        $stmt = Db::cms()->prepare(
            'SELECT SubClassID, DisplayName_lang FROM item_sub_class
             WHERE ClassID = ? ORDER BY SubClassID'
        );
        $stmt->execute([$classId]);
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
}
