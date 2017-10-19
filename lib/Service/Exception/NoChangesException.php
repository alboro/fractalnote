<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Service\Exception;

use OCP\AppFramework\Http;

class NoChangesException extends WebException
{
    public function getStatus()
    {
        return Http::STATUS_NOT_MODIFIED;
    }

    protected function defaultMessage()
    {
        return 'No any changes done';
    }
}
