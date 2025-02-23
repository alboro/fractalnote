<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author    Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\AppInfo;

use OC\App\AppManager;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\Util;
use Psr\Container\ContainerInterface;
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
class Application extends App implements IBootstrap {

    public const APP_ID = 'fractalnote';

    /**
     * Constructor
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
    }

    private function injectController($controllerName): \Closure
    {
        return function (ContainerInterface $c) use ($controllerName) {
            new $controllerName(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('ProviderFactory')
            );
        };
    }

    public function register(IRegistrationContext $context): void
    {
        /** Controllers */
        $context->registerService(
            'PageController',
            $this->injectController(PageController::class)
        );

        $context->registerService(
            'NoteController',
            $this->injectController(NoteController::class)
        );

        /** Middleware */
        $context->registerService('WebExceptionMiddleware', function() {
            return new WebExceptionMiddleware();
        });
        $context->registerMiddleware(WebExceptionMiddleware::class);
    }

    public function boot(IBootContext $context): void
    {
        Util::addScript(Application::APP_ID, 'test');
//        \OC::$server->getMimeTypeDetector()->registerType('ctb', 'application/cherrytree-ctb');

        $c = $this->getContainer();
        if ($c->get(\OCP\IUserSession::class)->isLoggedIn() /*&& $c->get(AppManager::class)->isEnabledForUser('files')*/) {
            // Util::addScript(Application::APP_ID, 'router');
//            $server = $c->getServer();
//            $eventDispatcher = $server->getEventDispatcher();
//            $eventDispatcher->addListener(
//                'OCA\Files::loadAdditionalScripts',
//                function() {
//                    Util::addScript(Application::APP_NAME, 'router');
//                }
//            );
        }
    }
}
