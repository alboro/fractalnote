<?php
/**
 * NextCloud / ownCloud - cherrycloud
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author    Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\CherryCloud\AppInfo;

use OCP\IContainer;
use OCP\AppFramework\App;
use OCA\CherryCloud\Controller\PageController;
use OCA\CherryCloud\Controller\NoteController;
use OCA\CherryCloud\Service\NotesStructure;

/**
 * Class Application
 *
 * @package OCA\CherryCloud\AppInfo
 */
class Application extends App {

    /**
     * Constructor
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = [])
    {
        parent::__construct('cherrycloud', $urlParams);

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
                'name' => $server->getL10N($c->getAppName())->t('CherryCloud'),
                'href' => $server->getURLGenerator()->linkToRoute('cherrycloud.page.index'),
                'icon' => $server->getURLGenerator()->imagePath($c->getAppName(), 'cherrycloud.svg'),
            ];
        };
        $server->getNavigationManager()->add($navigationEntry);
    }
}
