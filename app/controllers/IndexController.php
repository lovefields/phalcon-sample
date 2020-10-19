<?php
namespace Controllers;

use Controllers\Core\BaseController;

/**
 * @RoutePrefix('/')
 */
class IndexController extends BaseController
{
    /**
     * @Get('/')
     */
    public function indexAction()
    {
    }
}
