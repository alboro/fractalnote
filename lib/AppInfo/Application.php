<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author    Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\AppInfo;

use OCP\Util;
use OCP\IContainer;
use OCP\AppFramework\App;
use OCA\FractalNote\Service\ProviderFactory;
use OCA\FractalNote\Controller\PageController;
use OCA\FractalNote\Controller\NoteController;
use OCA\FractalNote\Service\WebExceptionMiddleware;

/**
 * Class Application
 *
 * @package OCA\FractalNote\AppInfo
 */
class Application extends App {

    const APP_NAME = 'fractalnote';

    /**
     * Constructor
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_NAME, $urlParams);

        $c = $this->getContainer();
        /** Controllers */
        $c->registerService(
            'PageController',
            $this->injectController(\OCA\FractalNote\Controller\PageController::class)
        );

        $c->registerService(
            'NoteController',
            $this->injectController(\OCA\FractalNote\Controller\NoteController::class)
        );

        /** Middleware */
        $c->registerService('WebExceptionMiddleware', function() {
            return new WebExceptionMiddleware();
        });
        $c->registerMiddleware('WebExceptionMiddleware');
    }

    private function injectController($controllerName)
    {
        return function (IContainer $c) use ($controllerName) {
            new $controllerName(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('ProviderFactory')
            );
        };
    }

    public function registerFrontendScripts()
    {
        $c = $this->getContainer();
        /** @var \OCP\IServerContainer $server */
        $server = $c->getServer();
        if ($server->getUserSession()->isLoggedIn()) {
            $eventDispatcher = $server->getEventDispatcher();
            $eventDispatcher->addListener(
                'OCA\Files::loadAdditionalScripts',
                function() {
                    Util::addScript(Application::APP_NAME, 'router');
                }
            );
        }
    }
}
