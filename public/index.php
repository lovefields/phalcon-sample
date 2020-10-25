<?php
declare(strict_types=1);

use App\Config\Bootstrap;

define('BASE_PATH', dirname(__DIR__));
define('APP_PATH', BASE_PATH . '/app');

include APP_PATH . '/config/Bootstrap.php';
$boot = Bootstrap::init()->run();
