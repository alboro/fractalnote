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
use Throwable;

class ConflictException extends WebException
{
    /**
     * ConflictException constructor.
     *
     * @param string|null $title
     */
    public function __construct($title = null)
    {
        $title = $title ? ' "' . $title . '"' : '';
        $message = "It's not possible to save your changes to the$title note, "
            . "because the note tree was recently changed by another process. "
            . "Please, save your changes somewhere, reload the page in the browser and retry.";
        parent::__construct($message);
    }

    protected function getStatus()
    {
        return Http::STATUS_CONFLICT;
    }
}