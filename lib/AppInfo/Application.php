<?php
/**
 * NextCloud / ownCloud - notehierarchy
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author    Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\NoteHierarchy\AppInfo;

use OCP\IContainer;
use OCP\AppFramework\App;
use OCA\NoteHierarchy\Controller\PageController;
use OCA\NoteHierarchy\Controller\NoteController;
use OCA\NoteHierarchy\Service\NotesStructure;

/**
 * Class Application
 *
 * @package OCA\NoteHierarchy\AppInfo
 */
class Application extends App {

    const APP_NAME = 'notehierarchy';

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
            function (IContainer $c) {
                return new PageController(
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->query('UserId'),
                    $c->query('NotesStructure')
                );
            }
        );

        $c->registerService(
            'NoteController',
            function (IContainer $c) {
                return new NoteController(
                    $c->query('AppName'),
                    $c->query('Request'),
                    $c->query('UserId'),
                    $c->query('NotesStructure')
                );
            }
        );
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
                'name' => $server->getL10N($c->getAppName())->t('NoteHierarchy'),
                'href' => $server->getURLGenerator()->linkToRoute($c->getAppName() . '.page.index'),
                'icon' => $server->getURLGenerator()->imagePath($c->getAppName(), 'app.svg'),
            ];
        };
        $server->getNavigationManager()->add($navigationEntry);
    }
}
