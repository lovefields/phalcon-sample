<?php
namespace Controllers\Core\Traits;

use Phalcon\Di;

trait SessionTraits
{
    /* @var \Phalcon\Session\AdapterInterface $sessionService */
    private $sessionService;

    public function hasSession(string $name): bool
    {
        return $this->getSessionService()->has($name);
    }

    public function getSession(string $name)
    {
        return $this->getSessionService()->get($name, null);
    }

    public function setSession(string $name, $value): void
    {
        $this->getSessionService()->set($name, $value);
    }

    public function removeSession(string $name): void
    {
        $this->getSessionService()->remove($name);
    }

    public function destroySession(): void
    {
        $this->getSessionService()->destroy();
    }

    public function isLogin(): bool
    {
        return !empty($this->getSession('isLogin'));
    }

    private function getSessionService()
    {
        if (empty($this->sessionService)) {
            $this->sessionService = Di::getDefault()->get('session');
        }

        return $this->sessionService;
    }
}