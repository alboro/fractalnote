<?php
/**
 * NextCloud / ownCloud - cherrycloud
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */

namespace OCA\CherryCloud\AppInfo;

use OCP\Util;

$app = new Application();
$app->registerNavigationEntry();

if (\OCP\User::isLoggedIn()) {
    $eventDispatcher = \OC::$server->getEventDispatcher();
    $eventDispatcher->addListener(
        'OCA\Files::loadAdditionalScripts',
        function() {
            Util::addScript(Application::APP_NAME, 'router');
        }
    );
}
