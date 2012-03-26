<?php namespace Atom\Tests\MVC;

// Aliasing rules
use Atom\MVC\Router;

class RouterTest extends \PHPUnit_Framework_TestCase
{
	private $router;

	public function setUp()
	{
		$this->router = new Router;
	}

	public function testEmptyRoutes()
	{
		$routes = $this->router->getRoutes();

		foreach($routes as $method)
		{
			$this->assertTrue(count($method) == 0);
		}
	}

	public function testAdd()
	{
		$this->router->add('test', ($route = function() { return 'Hello World'; }), 'GET');

		$expect = ['/test' => ['before' => false, 'callback' => $route, 'after' => false]];
		$actual = $this->router->getRoutes();

		$this->assertEquals($expect, $actual['GET']);

		$this->router->add('test', $route, 'POST');
		$actual = $this->router->getRoutes();

		$this->assertEquals($expect, $actual['GET']);
		$this->assertEquals($expect, $actual['POST']);
	}

	/**
	 * @todo testDispatch
	 */
}