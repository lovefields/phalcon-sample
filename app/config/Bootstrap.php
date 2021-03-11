<?php

namespace App\Config;

use josegonzalez\Dotenv\Loader as EnvLoader;
use Phalcon\Di\FactoryDefault;
use Phalcon\Http\ResponseInterface;
use Phalcon\Mvc\Application;
use Phalcon\Http\Response;
use Phalcon\Loader;
use Phalcon\Escaper;
use Phalcon\Flash\Direct as Flash;
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

    public static function init(): self
    {
        if (empty(self::$bootstrap)) {
            self::$bootstrap = new Bootstrap();
        }

        return self::$bootstrap;
    }

    public function run(): void
    {
        $URI = $_SERVER['REQUEST_URI'];

        self::assignLoader();
        self::assignEnv();
        self::assignService();
        self::assignRouter();


        try {
            $app = (new Application(self::$di))->handle($URI);
            $app->send();
        } catch (\Throwable $e) {
            $router = self::$di->get("response");

            if (IS_DEBUG == true) {
                error_reporting(E_ALL);
                throw new \Exception($e->getMessage());
            } else {
                ini_set('display_errors', 0);
                error_reporting(0);

                if($URI != "/error"){
                    $router->redirect('/error')->send();
                }
            }
        }
    }

    private function assignEnv(): void
    {
        (new EnvLoader(BASE_PATH . '/.env'))->parse()->toEnv(true);

        define('BASE_URI', $_ENV['BASE_URI']);
        define('IS_DEBUG', $_ENV['IS_DEBUG'] ?: false);
    }

    private function assignLoader(): void
    {
        $loader = new Loader();

        $loader->registerDirs([
            APP_PATH . '/controllers/',
            APP_PATH . '/services/',
            APP_PATH . '/helpers/',
            APP_PATH . '/models/',
            APP_PATH . '/library/',
        ])->registerNamespaces([
            'Controllers' => APP_PATH . '/controllers/',
            'Controllers\Core' => APP_PATH . '/controllers/core/',
            'Controllers\Core\Traits' => APP_PATH . '/controllers/core/traits/',
            'Services' => APP_PATH . '/services/',
            'Services\Core' => APP_PATH . '/services/core/',
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

            $router->addResource('Controllers\Index', '');

            $router->notFound(['namespace' => 'Controllers', 'controller' => 'error', 'action' => 'notFound']);
            $router->removeExtraSlashes(true);

            return $router;
        };
    }
}