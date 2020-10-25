<?php
namespace Controllers\Core;

use Controllers\Core\Traits\SessionTraits;
use duncan3dc\Laravel\BladeInstance;
use Phalcon\Mvc\Controller;
use Phalcon\Mvc\Dispatcher;

class BaseController extends Controller
{
    use SessionTraits;

    const DERECTORY_VIEW = APP_PATH . '/views';
    const DERECTORY_BLADE_CACHE = BASE_PATH . '/cache/blade';

    /* @var BladeInstance $blade */
    private static $blade;

    public function viewDisabled(bool $isAllowExternal = false): void
    {
        $this->view->disable();

        if ($isAllowExternal) {
            return;
        }

        if (stripos($this->request->getHTTPReferer(), $this->request->getHttpHost()) === false) {
            $this->forward('error', 'index');
        }
    }

    public function blade(string $viewFile, array $data = [], bool $isShow = true): string
    {
        $this->viewDisabled(true);

        if (empty($viewFile)) {
            die('<p>Wrong parameter value.</p>');
        } else if (!file_exists(self::DERECTORY_VIEW . $viewFile . ".blade.php")) {
            die('<p>Not found blade view file.</p>');
        }

        $view = self::getBlade()->render($viewFile,
            array_merge($data, ['flashMessage' => $this->getFlashMassage()])
        );

        if ($isShow) {
            echo $view;
        }

        return $view;
    }

    private function getFlashMassage(): string
    {
        return join(PHP_EOL, array_unique($this->flashSession->getMessages('notice')));
    }

    public function setFlashMessage(string $string): void
    {
        $this->flashSession->notice($string);
    }

    private function getBlade(): BladeInstance
    {
        if (empty(self::$blade)) {
            self::$blade = new BladeInstance(self::DERECTORY_VIEW, self::DERECTORY_BLADE_CACHE);
        }
        return self::$blade;
    }

    public function redirect(string $url, int $statusCode = 302): void
    {
        $this->response->redirect($url)->setStatusCode($statusCode)->send();
    }

    public function forward($controller, $action, array $params = []): void
    {
        $this->dispatcher->forward([
            'namespace' => 'Controllers',
            'controller' => $controller,
            'action' => $action,
            'params' => $params,
        ]);
        $this->dispatcher->dispatch();
        exit;
    }

    public function getParam(string $name, string $default = null): ?string
    {
        return empty($this->request->get($name)) ? $default : $this->request->get($name);
    }

    public function getPostParam(string $name, string $default = null): ?string
    {
        return empty($this->request->getPost($name)) ? $default : $this->request->getPost($name);
    }

    public function getFile(string $name, string $default = null)
    {
        $filsList = $this->request->getUploadedFiles();

        foreach($filsList as $file){
            if($file->getKey() == $name){
                $file = empty($file->getName()) ? $default : $file;
                break;
            }
        }

        return $file;
    }

    public function isPost(): bool
    {
        return $this->request->isPost();
    }
}
