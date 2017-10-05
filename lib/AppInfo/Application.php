<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author    Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\AppInfo;

use OCP\IContainer;
use OCP\AppFramework\App;
use OCA\FractalNote\Controller\RelationController;
use OCA\FractalNote\Controller\PageController;
use OCA\FractalNote\Controller\NoteController;
use OCA\FractalNote\Service\NotesStructure;

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
        /**
         * Controllers
         */
        $c->registerService(
            'PageController',
            $this->injectController(\OCA\FractalNote\Controller\PageController::class)
        );

        $c->registerService(
            'NoteController',
            $this->injectController(\OCA\FractalNote\Controller\NoteController::class)
        );

        $c->registerService(
            'RelationController',
            $this->injectController(\OCA\FractalNote\Controller\RelationController::class)
        );
    }

    private function injectController($controllerName)
    {
        return function (IContainer $c) use ($controllerName) {
            new $controllerName(
                $c->query('AppName'),
                $c->query('Request'),
                $c->query('UserId'),
                $c->query('NotesStructure')
            );
        };
    }

    public function registerNavigationEntry()
    {
        $c = $this->getContainer();
        /** @var \OCP\IServerContainer $server */
        $server = $c->getServer();
        $navigationEntry = function () use ($c, $server) {
            return [
                'id' => $c->getAppName(),
                'order' => 10,
                'name' => $server->getL10N($c->getAppName())->t('FractalNote'),
                'href' => $server->getURLGenerator()->linkToRoute($c->getAppName() . '.page.index'),
                'icon' => $server->getURLGenerator()->imagePath($c->getAppName(), 'app.svg'),
            ];
        };
        $server->getNavigationManager()->add($navigationEntry);
    }
}
