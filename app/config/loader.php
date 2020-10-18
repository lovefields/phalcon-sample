<?php
$loader = new \Phalcon\Loader();

$loader->registerDirs([
    APP_PATH . '/core/',
    APP_PATH . '/controllers/',
    APP_PATH . '/services/',
    APP_PATH . '/models/',
    APP_PATH . '/library/',
])->registerNamespaces([
    'Controllers' => APP_PATH . '/controllers/',
    'Services' => APP_PATH . '/services/',
    'Models' => APP_PATH . '/models/',
    'Repositories' => APP_PATH . '/repositories/',
    'Library' => APP_PATH . '/library/',
    'Helpers' => APP_PATH . '/helpers/',
])->registerFiles([
        BASE_PATH . '/vendor/autoload.php'
])->register();