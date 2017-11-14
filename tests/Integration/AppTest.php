<?php

namespace OCA\FractalNote\Tests\Integration\Controller;

use PHPUnit_Framework_TestCase;
use \OCA\FractalNote\AppInfo\Application;

/**
 * This test shows how to make a small Integration Test. Query your class
 * directly from the container, only pass in mocks if needed and run your tests
 * against the database
 */
class AppTest extends PHPUnit_Framework_TestCase {

    private $container;

    public function setUp() {
        parent::setUp();
        $app = new Application();
        $this->container = $app->getContainer();
    }

    public function testAppInstalled() {
        $appManager = $this->container->query('OCP\App\IAppManager');
        $this->assertTrue($appManager->isInstalled('fractalnote'));
    }

}
