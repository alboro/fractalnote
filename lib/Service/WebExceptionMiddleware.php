<?php
/**
 * NextCloud / ownCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Service;

use OCA\FractalNote\Service\Exception\WebException;
use OCP\AppFramework\Middleware;
use OCP\AppFramework\Controller;

class WebExceptionMiddleware extends Middleware
{

    /**
     * @inheritdoc
     */
    public function afterException($controller, $methodName, \Exception $exception)
    {
        if ($exception instanceof WebException) {
            return $exception->createResponse();
        }
        throw $exception;
    }
}