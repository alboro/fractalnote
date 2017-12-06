<?php

namespace OCA\FractalNote\Tests\Unit\Controller;

use PHPUnit_Framework_TestCase;
use OCP\AppFramework\Http\TemplateResponse;
use OCA\FractalNote\Controller\PageController;
use OCA\FractalNote\Service\ProviderFactory;

class PageControllerTest extends PHPUnit_Framework_TestCase {
	private $controller;
	private $userId = 'john';

	public function setUp()
    {
        $provider = $this->getMockBuilder('OCA\FractalNote\Service\AbstractProvider')->getMock();
        $request = $this->getMockBuilder('OCP\IRequest')->getMock();
        $providerFactory = $this->getMockBuilder('OCA\FractalNote\Service\ProviderFactory')
            ->disableOriginalConstructor()
            ->getMock();
        $providerFactory->expects($this->once())
            ->method('createProviderByRequest')
            ->with($request)
            ->willReturn($provider);

		$this->controller = new PageController('fractalnote', $request, $this->userId, $providerFactory);
	}

	public function testIndex()
    {
		$result = $this->controller->index();

		$this->assertEquals('404', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}
}