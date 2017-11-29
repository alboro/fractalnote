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
        $request = $this->getMockBuilder('OCP\IRequest')->getMock();
        $mockedProvider = $this->getMockBuilder('OCA\FractalNote\Service\NotesStructure')->getMock();
        $providerFactory = $this->getMockBuilder('OCA\FractalNote\Service\ProviderFactory')
            ->disableOriginalConstructor()
            ->getMock()
                ->expects($request)->method('createProviderByRequest')->willReturn($mockedProvider);

		$this->controller = new PageController('fractalnote', $request, $this->userId, $providerFactory);
	}

	public function testIndex()
    {
		$result = $this->controller->index();

		$this->assertEquals('404', $result->getTemplateName());
		$this->assertTrue($result instanceof TemplateResponse);
	}
}