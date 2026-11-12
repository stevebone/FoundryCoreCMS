<?php
// Private config - lives outside the docroot (public/) so it can never
// be served or downloaded by the web server. Include from public files
// via: require __DIR__ . '/../config.php';

return [
    'db' => [
        'host' => '127.0.0.1',
        'port' => 3307,
        'user' => 'trinity',
        'pass' => 'trinity',
    ],
    'databases' => ['auth', 'world', 'characters', 'cms'],
    'app' => [
        'name' => 'FoundryCore',
        'theme' => 'blizzard',
        'default_lang' => 'en',
        'languages' => ['en', 'de', 'fr'],
    ],
];
