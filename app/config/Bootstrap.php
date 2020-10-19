<?php

namespace App\Config;

use josegonzalez\Dotenv\Loader as EnvLoader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\Response;
use Phalcon\Loader;
use Phalcon\Escaper;
use Phalcon\Flash\Direct as Flash;
use Phalcon\Mvc\Application;
use Phalcon\Mvc\Model\Metadata\Memory as MetaDataAdapter;
use Phalcon\Mvc\View;
use Phalcon\Session\Adapter\Stream as SessionAdapter;
use Phalcon\Session\Manager as SessionManager;
use Phalcon\Url as UrlResolver;
use Phalcon\Mvc\Router\Annotations as RouterAnnotations;

class Bootstrap
{
    private static Bootstrap $bootstrap;
    private static FactoryDefault $di;

    private function __construct()
    {
        self::$di = new FactoryDefault();
    }

    public static function of(): self
    {
        if (empty(self::$bootstrap)) {
            self::$bootstrap = new Bootstrap();
        }

        return self::$bootstrap;
    }

    public function run(): void
    {
        self::assignLoader();
        self::assignEnv();
        self::assignService();
        self::assignRouter();

        try {
            echo (new Application(self::$di))->handle($_SERVER['REQUEST_URI'])->getContent();
        } catch (\Exception $e) {
//            $router = self::$di->getRouter();
            if (!IS_DEBUG) {
                error_reporting(E_ALL);
                echo $e->getMessage() . '<br>';
                echo '<pre>' . $e->getTraceAsString() . '</pre>';
            } else {
                ini_set('display_errors', 0);
                error_reporting(0);
//                $responce = new Response();
//                $responce->redirect("/error", false)->send();
            }
        }
    }

    private function assignEnv(): void
    {
        (new EnvLoader(BASE_PATH . '/.env'))->parse()->toEnv(true);

        define('BASE_URI', $_ENV['BASE_URI']);
        define('IMG_URL', $_ENV['IMG_URL']);
        define('IS_DEBUG', $_ENV['IS_DEBUG'] ?: false);
    }

    private function assignLoader(): void
    {
        $loader = new Loader();

        $loader->registerDirs([
            APP_PATH . '/core/',
            APP_PATH . '/controllers/',
            APP_PATH . '/services/',
            APP_PATH . '/models/',
            APP_PATH . '/library/',
        ])->registerNamespaces([
            'Core' => APP_PATH . '/core/',
            'Controllers' => APP_PATH . '/controllers/',
            'Infrastructure' => APP_PATH . '/infrastructure/',
            'Services' => APP_PATH . '/services/',
            'Models' => APP_PATH . '/models/',
            'Repositories' => APP_PATH . '/repositories/',
            'Library' => APP_PATH . '/library/',
            'Helpers' => APP_PATH . '/helpers/',
        ])->registerFiles([
            BASE_PATH . '/vendor/autoload.php'
        ])->register();
    }

    private function assignService(): void
    {
        self::$di->setShared('config', function () {
            return include APP_PATH . "/config/config.php";
        });

        self::$di->setShared('url', function () {
            $config = self::$di->getConfig();

            $url = new UrlResolver();
            $url->setBaseUri($config->application->baseUri);

            return $url;
        });

        self::$di->setShared('view', function () {
            $config = $this->getConfig();
            $view = new View();
            $view->setDI(self::$di);
            $view->setViewsDir($config->application->viewsDir);
            return $view;
        });

        self::$di->setShared('modelsMetadata', function () {
            return new MetaDataAdapter();
        });

        self::$di->set('flash', function () {
            $flash = new Flash((new Escaper()));
            $flash->setImplicitFlush(false);
            $flash->setCssClasses([
                'error'   => 'alert alert-danger',
                'success' => 'alert alert-success',
                'notice'  => 'alert alert-info',
                'warning' => 'alert alert-warning'
            ]);

            return $flash;
        });

        self::$di->setShared('session', function () {
            // ini_set('session.gc_maxlifetime', 3600);
            // session_set_cookie_params(3600);
            $session = new SessionManager();
            $files = new SessionAdapter([
                'savePath' => sys_get_temp_dir(),
            ]);
            $session->setAdapter($files);
            $session->start();

            return $session;
        });
    }

    private function assignRouter(): void
    {
        self::$di['router'] = function() {
            $router = new RouterAnnotations(false);

            $router->addResource('Controllers\Index', '')
                ->addResource('Controllers\error', '/error')
                ->addResource('Controllers\Articles', '/articles')
                ->addResource('Controllers\Amp', '/amp')
                ->addResource('Controllers\Category', '/category')
                ->addResource('Controllers\Archives', '/archive')
                ->addResource('Controllers\Output', '/output')
                ->addResource('Controllers\Auth', '/auth')
                ->addResource('Controllers\Members', '/members')
                ->addResource('Controllers\MyService', '/my-service')
            ;

            $router->notFound(['namespace' => 'Controllers', 'controller' => 'error', 'action' => 'notFound']);
            $router->removeExtraSlashes(true);
            //$router->setUriSource(RouterAnnotations::URI_SOURCE_SERVER_REQUEST_URI);

            return $router;
        };
    }
}