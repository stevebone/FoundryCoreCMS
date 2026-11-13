<?php
// Front controller: every non-file request lands here (php_server fallback).

use App\Api\AdminController;
use App\Api\ArmoryController;
use App\Api\AuthController;
use App\Api\RealmController;
use App\Api\SettingsController;
use App\Auth\AuthService;
use App\Core\Lang;
use App\Core\Router;
use App\Core\Settings;
use App\Core\SiteSettings;
use App\Core\Theme;

require dirname(__DIR__) . '/app/bootstrap.php';

// Language switch: ?lang=xx → persist cookie, redirect to a clean URL.
if (isset($_GET['lang']) && in_array($_GET['lang'], Lang::available(), true)) {
    setcookie('cms_lang', $_GET['lang'], [
        'expires' => time() + 31536000,
        'path' => '/',
        'samesite' => 'Lax',
    ]);
    if ($u = AuthService::user()) {
        Settings::save((int) $u['id'], ['lang' => $_GET['lang']]);
    }
    $target = strtok($_SERVER['REQUEST_URI'], '?');
    $query = $_GET;
    unset($query['lang']);
    if ($query) {
        $target .= '?' . http_build_query($query);
    }
    header('Location: ' . $target, true, 302);
    exit;
}

$router = new Router();

$router->get('/', fn() => Theme::render('home', ['title' => t('meta.title')]));

// Auth pages
$router->get('/login', function () {
    if (AuthService::user()) {
        header('Location: /');
        return;
    }
    Theme::render('auth/login', ['title' => t('auth.login_title')]);
});

$router->post('/login', function () {
    $r = AuthService::login($_POST['email'] ?? '', $_POST['password'] ?? '');
    if ($r['ok']) {
        header('Location: /');
        return;
    }
    Theme::render('auth/login', [
        'title' => t('auth.login_title'),
        'error' => $r['error'],
        'email' => $_POST['email'] ?? '',
    ], 401);
});

$router->get('/register', function () {
    if (AuthService::user()) {
        header('Location: /');
        return;
    }
    Theme::render('auth/register', ['title' => t('auth.register_title')]);
});

$router->post('/register', function () {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['password_confirm'] ?? '';

    if ($password !== $confirm) {
        Theme::render('auth/register', [
            'title' => t('auth.register_title'),
            'error' => 'err_password_match',
            'email' => $email,
        ], 422);
        return;
    }

    $r = AuthService::register($email, $password);
    if ($r['ok']) {
        header('Location: /');
        return;
    }
    Theme::render('auth/register', [
        'title' => t('auth.register_title'),
        'error' => $r['error'],
        'email' => $email,
    ], 422);
});

$router->get('/logout', function () {
    AuthService::logout();
    header('Location: /');
});

$router->get('/support', fn() => Theme::render('support', ['title' => t('support.title')]));

$router->get('/armory', function () {
    $char = null;
    $items = [];
    $extras = ['guild' => null, 'title' => null];
    $guid = (int) ($_GET['char'] ?? 0);
    if ($guid) {
        $char = App\Core\Armory::character($guid);
        if ($char) {
            $items = App\Core\Armory::equipment($guid);
            $extras = App\Core\Armory::characterExtras($guid, $char['name']);
        }
    }
    Theme::render('armory', [
        'title' => t('armory.title'),
        'realms' => App\Core\Armory::realms(),
        'characters' => App\Core\Armory::characters(),
        'realmId' => (int) ($_GET['realm'] ?? 0),
        'char' => $char,
        'items' => $items,
        'guild' => $extras['guild'],
        'charTitle' => $extras['title'],
    ]);
});

// Community
$router->get('/guilds', function () {
    Theme::render('guilds', [
        'title' => t('guild.title'),
        'guilds' => App\Core\Guild::guilds(),
        'realm' => implode(' / ', array_column(App\Core\Armory::realms(), 'name')),
    ]);
});

$router->get('/guild/{id}', function (array $p) {
    $d = App\Core\Guild::detail((int) $p['id']);
    if (!$d) {
        Theme::render('404', ['title' => '404'], 404);
        return;
    }
    Theme::render('guild', [
        'title' => $d['guild']['name'],
        'd' => $d,
        'realm' => implode(' / ', array_column(App\Core\Armory::realms(), 'name')),
        'perks' => App\Core\Guild::perks(),
        'ach' => App\Core\Guild::achievementStats((int) $d['guild']['guildid']),
        'achievements' => App\Core\Guild::achievements((int) $d['guild']['guildid']),
    ]);
});

$router->get('/forums', fn() => Theme::render('stub', ['title' => t('nav.forums'), 'heading' => t('nav.forums')]));
$router->get('/leaderboards', fn() => Theme::render('stub', ['title' => t('nav.leaderboards'), 'heading' => t('nav.leaderboards')]));

// News
$router->get('/news', function () {
    Theme::render('news', [
        'title' => t('news.title'),
        'result' => App\Core\News::page((int) ($_GET['page'] ?? 1)),
        'canWrite' => user_level() >= 2,
    ]);
});

$router->get('/news/{id}', function (array $p) {
    $article = App\Core\News::get((int) $p['id']);
    if (!$article) {
        Theme::render('404', ['title' => '404'], 404);
        return;
    }
    Theme::render('news_item', ['title' => $article['title'], 'a' => $article]);
});

$router->get('/news/new', function () {
    if (user_level() < 2) {
        header('Location: /news');
        return;
    }
    Theme::render('news_edit', [
        'title' => t('news.new'),
        'tags' => App\Core\News::tags(),
        'old' => $_POST,
    ]);
});

$router->post('/news/new', function () {
    $u = AuthService::user();
    if (!$u || user_level() < 2) {
        header('Location: /news');
        return;
    }
    $id = App\Core\News::create($_POST, (int) $u['id']);
    if (!$id) {
        header('Location: /news/new');
        return;
    }
    header('Location: /news/' . $id);
});

$router->get('/settings', function () {
    $u = AuthService::user();
    if (!$u) {
        header('Location: /login');
        return;
    }
    Theme::render('settings', [
        'title' => t('settings.title'),
        'account' => $u,
        'prefs' => Settings::for((int) $u['id']) ?? [],
        'themes' => Theme::available(),
        'backgrounds' => Theme::backgrounds(),
        'languages' => Lang::available(),
        'saved' => isset($_GET['saved']),
    ]);
});

$router->post('/settings', function () {
    $u = AuthService::user();
    if (!$u) {
        header('Location: /login');
        return;
    }
    $prefs = SettingsController::validate($_POST);
    Settings::save((int) $u['id'], $prefs);
    if (array_key_exists('lang', $prefs)) {
        setcookie('cms_lang', $prefs['lang'] ?? '', [
            'expires' => $prefs['lang'] ? time() + 31536000 : 1,
            'path' => '/',
            'samesite' => 'Lax',
        ]);
    }
    header('Location: /settings?saved=1');
});

// Admin panel
$adminGuard = function (int $minLevel): bool {
    if (!AuthService::user()) {
        header('Location: /login');
        return false;
    }
    if (user_level() < $minLevel) {
        header('Location: /');
        return false;
    }
    return true;
};

$router->get('/admin/settings', function () use ($adminGuard) {
    if (!$adminGuard(3)) {
        return;
    }
    Theme::render('admin/site_settings', [
        'title' => t('admin.site_settings'),
        'tab' => in_array($_GET['tab'] ?? '', ['footer', 'database'], true) ? $_GET['tab'] : 'main',
        'values' => [
            'app.name' => config('app.name'),
            'app.theme' => config('app.theme'),
            'app.default_lang' => config('app.default_lang'),
        ],
        'themes' => Theme::available(),
        'backgrounds' => Theme::backgrounds(),
        'bgDefault' => App\Core\SiteSettings::get('theme.bg') ?? '',
        'favicons' => Theme::favicons(),
        'faviconDefault' => App\Core\SiteSettings::get('theme.favicon') ?? '',
        'heroes' => Theme::heroes(),
        'heroDefault' => App\Core\SiteSettings::get('theme.hero') ?? '',
        'logos' => Theme::logos(),
        'logoDefault' => App\Core\SiteSettings::get('theme.logo') ?? '',
        'languages' => Lang::available(),
        'saved' => isset($_GET['saved']),
    ]);
});

$router->post('/admin/settings', function () use ($adminGuard) {
    if (!$adminGuard(3)) {
        return;
    }
    $overrides = AdminController::validateForm($_POST);
    if (!isset($overrides['err'])) {
        SiteSettings::save($overrides);
    }
    $tab = isset($_POST['f_footer']) ? 'footer'
        : (isset($_POST['f_db']) ? 'database' : 'main');
    header("Location: /admin/settings?tab={$tab}&saved=1");
});

$router->get('/admin/dbtool', function () use ($adminGuard) {
    if (!$adminGuard(3)) {
        return;
    }
    Theme::render('stub', ['title' => t('nav.db_tool'), 'heading' => t('nav.db_tool')]);
});

// Relative paths resolve against the site root (e.g. tools/db2csv).
$db2ResolveDir = static function (string $dir): string {
    $dir = trim($dir);
    if ($dir !== '' && !preg_match('#^([a-zA-Z]:[\\\\/]|/|\\\\\\\\)#', $dir)) {
        $dir = WEB_DIR . '/' . $dir;
    }
    return $dir;
};

$router->post('/admin/db2-import', function () use ($adminGuard, $db2ResolveDir) {
    if (!$adminGuard(3)) {
        return;
    }
    $posted = trim((string) ($_POST['db2_dir'] ?? ''));
    App\Core\SiteSettings::save(['db.db2_dir' => $posted === '' ? null : substr($posted, 0, 255)]);
    $dir = $db2ResolveDir($posted);
    if ($dir === '' || !is_dir($dir)) {
        $_SESSION['db2_import'] = ['code' => -1, 'output' => t('admin.db2_missing_dir')];
        header('Location: /admin/settings?tab=database');
        return;
    }
    $tables = trim((string) ($_POST['db2_tables'] ?? ''));
    $tables = preg_match('/^[\w]+(,[\w]+)*$/', $tables) ? $tables : '';
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(WEB_DIR . '/tools/import_db2_csv.php')
        . ' ' . escapeshellarg($dir) . ($tables !== '' ? ' ' . escapeshellarg($tables) : '') . ' 2>&1';
    set_time_limit(0);
    exec($cmd, $out, $code);
    $_SESSION['db2_import'] = ['code' => $code, 'output' => implode("\n", $out)];
    header('Location: /admin/settings?tab=database');
});

$router->post('/admin/db2-fetch', function () use ($adminGuard, $db2ResolveDir) {
    if (!$adminGuard(3)) {
        return;
    }
    $build = trim((string) ($_POST['db2_build'] ?? ''));
    if (!preg_match('/^\d+\.\d+\.\d+\.\d+$/', $build)) {
        $_SESSION['db2_import'] = ['code' => -1, 'output' => t('admin.db2_bad_build')];
        header('Location: /admin/settings?tab=database');
        return;
    }
    App\Core\SiteSettings::save(['db.db2_build' => $build]);
    $dir = (string) App\Core\SiteSettings::get('db.db2_dir');
    $dir = $db2ResolveDir($dir === '' ? 'tools/db2csv' : $dir);
    $tables = trim((string) ($_POST['db2_tables'] ?? ''));
    $tables = preg_match('/^[\w]+(,[\w]+)*$/', $tables) ? $tables : '';
    $cmd = escapeshellarg(PHP_BINARY) . ' ' . escapeshellarg(WEB_DIR . '/tools/fetch_db2_csv.php')
        . ' ' . escapeshellarg($dir) . ' ' . escapeshellarg($build)
        . ($tables !== '' ? ' ' . escapeshellarg($tables) : '') . ' 2>&1';
    set_time_limit(0);
    exec($cmd, $out, $code);
    $_SESSION['db2_import'] = ['code' => $code, 'output' => implode("\n", $out)];
    header('Location: /admin/settings?tab=database');
});

$router->get('/admin/gm', function () use ($adminGuard) {
    if (!$adminGuard(1)) {
        return;
    }
    Theme::render('admin/gm', [
        'title' => t('admin.gm_title'),
        'level' => user_level(),
    ]);
});

// Database browsing (gated: admin toggle + login)
$dbGate = function (): bool {
    if (!App\Core\Database::enabled()) {
        Theme::render('404', ['title' => '404'], 404);
        return false;
    }
    if (!AuthService::user()) {
        header('Location: /login');
        return false;
    }
    return true;
};

$dbFilters = fn(): array => [
    'q' => $_GET['q'] ?? '',
    'quality' => $_GET['quality'] ?? '',
    'class' => $_GET['class'] ?? '',
    'subclass' => $_GET['subclass'] ?? '',
    'slot' => $_GET['slot'] ?? '',
    'ilvl_min' => $_GET['ilvl_min'] ?? '',
    'ilvl_max' => $_GET['ilvl_max'] ?? '',
    'page' => (int) ($_GET['page'] ?? 1),
];

$router->get('/db/items', function () use ($dbGate, $dbFilters) {
    if (!$dbGate()) {
        return;
    }
    $filters = $dbFilters();
    Theme::render('db/items', [
        'title' => t('db.items'),
        'section' => 'items',
        'filters' => $filters,
        'result' => App\Core\Database::searchItems($filters),
        'classes' => App\Core\Database::itemClasses(),
        'subclasses' => ($filters['class'] ?? '') !== '' && is_numeric($filters['class'])
            ? App\Core\Database::itemSubClasses((int) $filters['class'])
            : [],
        'slots' => App\Core\Wow::INV_TYPES,
    ]);
});

$router->get('/db/item/{id}', function (array $p) use ($dbGate) {
    if (!$dbGate()) {
        return;
    }
    $item = App\Core\Armory::itemTooltip((int) $p['id']);
    if (!$item) {
        Theme::render('404', ['title' => '404'], 404);
        return;
    }
    Theme::render('db/item', [
        'title' => $item['name'],
        'section' => 'items',
        'item' => $item,
        'relations' => App\Core\Database::itemRelations((int) $p['id']),
    ]);
});

$router->get('/db/quests', function () use ($dbGate) {
    if (!$dbGate()) {
        return;
    }
    $filters = [
        'q' => trim((string) ($_GET['q'] ?? '')),
        'lvl_min' => $_GET['lvl_min'] ?? '',
        'lvl_max' => $_GET['lvl_max'] ?? '',
        'type' => $_GET['type'] ?? '',
        'zone' => trim((string) ($_GET['zone'] ?? '')),
        'sort' => $_GET['sort'] ?? 'id',
        'dir' => strtolower((string) ($_GET['dir'] ?? 'asc')) === 'desc' ? 'desc' : 'asc',
        'page' => (int) ($_GET['page'] ?? 1),
    ];
    Theme::render('db/quests', [
        'title' => t('db.quests'),
        'section' => 'quests',
        'filters' => $filters,
        'result' => App\Core\Database::searchQuests($filters),
    ]);
});

$router->get('/db/quest/{id}', function (array $p) use ($dbGate) {
    if (!$dbGate()) {
        return;
    }
    $quest = App\Core\Database::questDetail((int) $p['id']);
    if (!$quest) {
        Theme::render('404', ['title' => '404'], 404);
        return;
    }
    Theme::render('db/quest', [
        'title' => $quest['name'],
        'section' => 'quests',
        'quest' => $quest,
    ]);
});

foreach (['creatures', 'objects'] as $section) {
    $router->get("/db/{$section}", function () use ($dbGate, $section) {
        if (!$dbGate()) {
            return;
        }
        Theme::render('db/section', [
            'title' => t('db.' . $section),
            'section' => $section,
        ]);
    });
}

// APIs
$router->get('/api/realm/status', [RealmController::class, 'status']);
$router->get('/api/server/online', [RealmController::class, 'online']);
$router->post('/api/auth/register', [AuthController::class, 'register']);
$router->post('/api/auth/login', [AuthController::class, 'login']);
$router->post('/api/auth/logout', [AuthController::class, 'logout']);
$router->get('/api/auth/me', [AuthController::class, 'me']);
$router->get('/api/account/settings', [SettingsController::class, 'get']);
$router->post('/api/account/settings', [SettingsController::class, 'save']);
$router->post('/api/admin/settings', [AdminController::class, 'saveSettings']);
$router->get('/api/armory/realms', [ArmoryController::class, 'realms']);
$router->get('/api/armory/characters', [ArmoryController::class, 'characters']);
$router->get('/api/armory/character', [ArmoryController::class, 'character']);
$router->get('/api/armory/item', [ArmoryController::class, 'item']);
$router->get('/api/db/items', [App\Api\DbController::class, 'items']);
$router->get('/api/db/item', [App\Api\DbController::class, 'item']);
$router->get('/api/db/quests', [App\Api\DbController::class, 'quests']);
$router->get('/api/db/quest', [App\Api\DbController::class, 'quest']);
$router->get('/api/db/item-names', [App\Api\DbController::class, 'itemNames']);
$router->get('/api/db/zones', [App\Api\DbController::class, 'zones']);
$router->get('/api/db/quest-names', [App\Api\DbController::class, 'questNames']);
$router->post('/api/news/preview', [App\Api\NewsController::class, 'preview']);
$router->post('/api/news/image', [App\Api\NewsController::class, 'image']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
