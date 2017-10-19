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

use Exception;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\DataResponse;
use Throwable;

class WebException extends Exception
{
    public function __construct($message = '', $code = 0, Throwable $previous = null)
    {
        $code = $code ? : $this->getStatus();
        $message = $message ? : $this->defaultMessage();
        parent::__construct($message, $code, $previous);
    }

    public function createResponse()
    {
        return new DataResponse($this->getData(), $this->getStatus());
    }

    private function getData()
    {
        return ['message' => $this->getMessage()];
    }

    protected function getStatus()
    {
        return Http::STATUS_INTERNAL_SERVER_ERROR;
    }

    protected function defaultMessage()
    {
        return 'Unknown server error';
    }
}