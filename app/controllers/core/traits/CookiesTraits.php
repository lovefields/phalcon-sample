<?php
namespace Controllers\Core\Traits;

use Phalcon\Di;

trait CookiesTraits
{
    /* @var \Phalcon\Http\Response\CookiesInterface $cokiesService */
    private $cokiesService;

    public function hasCookies(string $name): bool
    {
        return $this->getCookiesService()->has($name);
    }

    public function getCookies(string $name)
    {
        return $this->getCookiesService()->get($name);
    }

    public function setCookies(string $name, $value, int $expire)
    {
        return $this->getCookiesService()->set($name, $value, $expire);
    }

    public function deleteCookies(string $name): bool
    {
        return $this->getCookiesService()->delete($name);
    }

    private function getCookiesService()
    {
        if (empty($this->cokiesService)) {
            $this->cokiesService = Di::getDefault()->get('cookies');
        }

        return $this->cokiesService;
    }
}