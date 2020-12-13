<?php
/**
 * NextCloud - fractalnote
 *
 * Licensed under the Apache License, Version 2.0
 *
 * @author Alexander Demchenko <a.demchenko@aldem.ru>, <https://github.com/alboro>
 * @copyright Alexander Demchenko 2017
 */
namespace OCA\FractalNote\Controller;

use OCP\AppFramework\Http\DataResponse;

class StorageController extends AbstractController
{
    /**
     * @NoAdminRequired
     *
     * @return DataResponse
     */
    public function create()
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     *
     * @return DataResponse
     */
    public function update()
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     *
     * @return DataResponse
     */
    public function destroy()
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     */
    public function index()
    {
        return new DataResponse();
    }

    /**
     * @NoAdminRequired
     *
     */
    public function show()
    {
        return new DataResponse();
    }
}
