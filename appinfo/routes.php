<?php
/**
 * NextCloud / ownCloud - cherrycloud
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
return [
    'resources' => [
        'note' => ['url' => '/notes']
    ],
    'routes' => [
        ['name' => 'page#index', 'url' => '/', 'verb' => 'GET'],
        ['name' => 'note#tree', 'url' => '/tree', 'verb' => 'GET'],
    ],
];
