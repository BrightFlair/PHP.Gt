<?php
namespace Gt\WebEngine;

use Gt\Config\Config;
use Gt\Http\Request;
use Gt\Http\Response;
use Gt\Http\ServerInfo;
use Gt\Input\Input;
use Gt\Cookie\Cookie;
use Gt\Session\Session;
use Gt\Http\RequestFactory;
use Gt\Http\ResponseFactory;
use Gt\WebEngine\Dispatch\Dispatcher;
use Gt\WebEngine\Response\ApiResponse;
use Gt\WebEngine\Response\PageResponse;
use Gt\WebEngine\Route\Router;
use Gt\WebEngine\Route\RouterFactory;
use Gt\WebEngine\Dispatch\DispatcherFactory;

class Lifecycle {
	/** @var Config */
	protected static $config;
	/** @var ServerInfo */
	protected static $serverInfo;
	/** @var Input */
	protected static $input;
	/** @var Cookie */
	protected static $cookie;
	/** @var Session */
	protected static $session;

	/** @var Request */
	protected static $request;
	/** @var Response */
	protected static $response;
	/** @var Router */
	protected static $router;
	/** @var Dispatcher */
	protected static $dispatcher;

	/**
	 * The start of the application's lifecycle. This function simply breaks the lifecycle down
	 * into different functions, in order.
	 */
	public static function start():void {
		self::createCoreObjects();
		self::createRequestResponse();
		self::createRouter();
		self::checkRequestRoute();
		self::dispatch();
		self::finish();
	}

	/**
	 * The "Core" objects within the WebEngine are encapsulated abstractions to core PHP
	 * functionality:
	 * - Config is used to retrieve configuration via config.ini and environment variables
	 * - Input is used to take user input through the querystring and posted form fields
	 * - Cookie is used to get and set cookies
	 * - Session is used to get and set persistent state data
	 */
	public static function createCoreObjects():void {
		self::$config = new Config();
		self::$serverInfo = new ServerInfo();
		self::$input = new Input();
		self::$cookie = new Cookie();
		self::$session = new Session();
	}

	/**
	 * The two most important parts of the application's lifecycle: the request and the response
	 * from and to the client. There are different types of request and response, depending on
	 * how the application is being used, so the factory methods are used to create the correct
	 * type of request according to the server info. At this stage in the lifecycle objects are
	 * only created, executing their logic when dispatched later.
	 */
	public static function createRequestResponse():void {
		self::$request = RequestFactory::create(
			self::$serverInfo,
			self::$input->getStream()
		);
		ResponseFactory::registerResponseClass(
			PageResponse::class,
			"text/html"
		);
		ResponseFactory::registerResponseClass(
			ApiResponse::class,
			"application/json",
			"application/xml"
		);
		self::$response = ResponseFactory::create(self::$request);
	}

	/**
	 * The router object is used to link the incoming request to the correct view/logic files
	 * within the application's directory. At this stage of the lifecycle the object is only
	 * created, executing its logic when dispatched later.
	 */
	public static function createRouter():void {
		self::$router = RouterFactory::create(self::$request);
	}

	/**
	 * There must only be one route from request to response (case sensitivity, for example).
	 * Checking the request against the route will halt execution and redirect the request
	 * if necessary.
	 */
	public static function checkRequestRoute():void {
		$requestedUri = self::$request->getUri();
		$uri = self::$router->getCorrectedUri($requestedUri);

		/** @noinspection PhpNonStrictObjectEqualityInspection */
		if($uri == $requestedUri) {
			// TODO: Redirect properly.
		}
	}

	/**
	 * Now all of the essential objects of the application are created, the dispatcher will
	 * handle the request, build up the response and dispatch the relevant objects where they
	 * need to go.
	 */
	public static function dispatch():void {
		self::$dispatcher = DispatcherFactory::create(self::$router);
		self::$dispatcher->handle(self::$request, self::$response);
	}

	/**
	 * The final part of the lifecycle is the finish function. This is where the response is
	 * finally output to the client, followed by any tidy-up code required.
	 */
	public static function finish():void {
		echo self::$response;
	}
}