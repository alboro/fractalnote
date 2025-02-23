<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Controller;

use OCP\AppFramework\Http\DataResponse;

class StorageController extends AbstractController
{
    /**
     * @NoAdminRequired
     */
    public function create(): DataResponse
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     */
    public function update(): DataResponse
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     */
    public function destroy(): DataResponse
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     */
    public function index(): DataResponse
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     */
    public function show(): DataResponse
    {
        return new DataResponse();
    }
}
