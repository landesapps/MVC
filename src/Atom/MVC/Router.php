<?php namespace Atom\MVC;

/**
 * Router class
 *
 * @package    Atom
 * @subpackage MVC
 */
class Router
{
	/**
	 * Collection of defined routes
	 *
	 * @var    array
	 */
	private $routes = [
		'HEAD'    => [],
		'GET'     => [],
		'POST'    => [],
		'PUT'     => [],
		'DELETE'  => [],
		'OPTIONS' => [],
	];

	/**
	 * Collection of short-cut regex variables for routes
	 *
	 * @var    array
	 */
	private $shortcuts = [
		'(:num)'   => '([0-9]+)',
		'(:alnum)' => '([0-9a-zA-Z]+)',
		'(:alpha)' => '([a-zA-Z]+)',
		'(:any)'   => '(.+)',
	];

	/**
	 * Adds a route
	 *
	 * @param    string|array     URI(s) to match for this route
	 * @param    Closure|array    A callback method to execute if matched, or
	 *                            an array of before/callback/after callbacks
	 * @param    string|array     HTTP method(s) to match
	 * @return   void             No value is returned
	 */
	public function add($routes, $callbacks, $methods = 'GET')
	{
		if('ANY' == $methods)
		{
			$methods = array_keys($this->routes);
		}

		$routes    = (array) $routes;
		$methods   = (array) $methods;

		if(!is_array($callbacks))
		{
			$callbacks['callback'] = $callbacks;
		}

		foreach($callbacks as $callback)
		{
			if(!($callback instanceof Closure))
			{
				throw new \RuntimeException('Callbacks must be in the form of a Closure');
			}
		}

		if(!isset($callbacks['before']))
		{
			$callbacks['before'] = false;
		}

		if(!isset($callbacks['after']))
		{
			$callbacks['after'] = false;
		}

		foreach($methods as $method)
		{
			$method = strtoupper($method);

			foreach($routes as $route)
			{
				$route = '/'.trim($route, '/');

				if(isset($this->routes[$method]))
				{
					$this->routes[$method][$route] = [
						'before'   => $callbacks['before'],
						'callback' => $callbacks['callback'],
						'after'    => $callbacks['after'],
					];
				}
			}
		}
	}

	/**
	 * Dispatches an HTTP method and URI into a route
	 *
	 * @param    string           URI to match
	 * @param    string           HTTP method to match
	 * @return   string|boolean   Content of the dispatch, otherwise false
	 */
	public function dispatch($uri, $method = 'GET')
	{
		$method = strtoupper($method);

		if(false !== ($route = $this->match($uri, $method)))
		{
			if($route['before'] !== false)
			{
				call_user_func_array($route['before'], $route['parameters']);
			}

			$response = call_user_func_array($route['callback'], $route['parameters']);

			if($route['after'] !== false)
			{
				call_user_func_array($route['after'], $route['parameters']);
			}

			return $response;
		}

		return false;
	}

	/**
	 * Attempts to match a URI and HTTP method to a defined route, and assembles
	 * parameters
	 *
	 * @param    string           URI to match
	 * @param    string           HTTP method to match
	 * @return   array|boolean    A route array, otherwise false
	 */
	private function match($uri, $method)
	{
		if(isset($this->routes[$method]) and !empty($this->routes[$method]))
		{
			foreach(array_keys($this->route[$method]) as $route)
			{
				foreach($this->choices as $search => $replace)
				{
					$route = str_replace($search, $replace, $route);
				}

				if($route == '(.+)')
				{
					return [
						'parameters' => $uri,
						'before'     => $this->routes[$method][$route]['before'],
						'callback'   => $this->routes[$method][$route]['callback'],
						'after'      => $this->routes[$method][$route]['after'],
					];
				}
				elseif(preg_match('#^'.$route.'$#i', $uri, $matches))
				{
					$parameters = [];

					for($i = 1, $count = count($matches); $i < $count; $i++)
					{
						$parameters[] = $matches[$i];
					}

					return [
						'parameters' => $parameters,
						'before'     => $this->routes[$method][$route]['before'],
						'callback'   => $this->routes[$method][$route]['callback'],
						'after'      => $this->routes[$method][$route]['after'],
					];
				}
			}
		}
	}
}