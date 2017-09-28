<?php
/**
 * NextCloud / ownCloud - notehierarchy
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\NoteHierarchy\Service;

use OCP\AppFramework\Http;
use Throwable;

class NotEditableException extends WebException
{
    public function __construct($isRich, $isReadonly)
    {
        switch (true)
        {
            case $isReadonly:
                $message = 'This note is readonly! Cannot edit it.';
                break;
            case $isRich:
                $message = 'This note has rich text! This version of an app does not support editing of rich text yet.';
                break;
        }
        parent::__construct($message);
    }

    public function getStatus()
    {
        return Http::STATUS_FORBIDDEN;
    }
}
