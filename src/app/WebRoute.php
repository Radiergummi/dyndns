<?php

namespace Radiergummi\DynDns;

use RuntimeException;
use Throwable;
use function is_callable;

/**
 * WebRoute class
 *
 * @package Radiergummi\DynDns
 */
class WebRoute extends Route {

  /**
   * Namespace path to the controllers namespace
   */
  protected const CONTROLLER_NAMESPACE = __NAMESPACE__ . '\\Controllers\\';

  /**
   * Controller class name suffix
   */
  protected const CONTROLLER_SUFFIX = 'Controller';

  /**
   * DELETE HTTP method
   */
  public const    METHOD_DELETE = 'delete';

  /**
   * GET HTTP method
   */
  public const    METHOD_GET = 'get';

  /**
   * OPTIONS HTTP method
   */
  public const    METHOD_OPTIONS = 'options';

  /**
   * PATCH HTTP method
   */
  public const    METHOD_PATCH = 'patch';

  /**
   * POST HTTP method
   */
  public const    METHOD_POST = 'post';

  /**
   * PUT HTTP method
   */
  public const    METHOD_PUT = 'put';

  /**
   * Method parameter for the route constructor
   */
  protected const PARAM_METHOD = 'method';

  /**
   * Path parameter for the route constructor
   */
  protected const PARAM_PATH = 'path';

  /**
   * Holds the request method
   *
   * @var string
   */
  protected $method;

  /**
   * Holds the request path
   *
   * @var string
   */
  protected $path;

  /**
   * Holds the route action
   *
   * @var string
   */
  protected $action;

  /**
   * WebRoute constructor
   *
   * @param array $params
   *
   * @throws \Exception
   */
  public function __construct( array $params ) {
    parent::__construct( $params );

    $this->path   = $params[ WebRoute::PARAM_PATH ] ?? '/';
    $this->method = $params[ WebRoute::PARAM_METHOD ] ?? WebRoute::METHOD_GET;
  }

  /**
   * Shorthand method to create a GET route
   *
   * @param string          $path
   * @param string|Callable $handler
   * @param string          $name
   *
   * @return static
   */
  public static function get( string $path, $handler, string $name = null ): WebRoute {
    return new static( [
                           WebRoute::PARAM_PATH   => $path,
                           WebRoute::PARAM_METHOD => WebRoute::METHOD_GET,
                           Route::PARAM_HANDLER   => $handler,
                           Route::PARAM_NAME      => $name
                       ] );
  }

  /**
   * Shorthand method to create a POST route
   *
   * @param string          $path
   * @param string|Callable $handler
   * @param string          $name
   *
   * @return static
   */
  public static function post( string $path, $handler, string $name = null ): WebRoute {
    return new static( [
                           WebRoute::PARAM_PATH   => $path,
                           WebRoute::PARAM_METHOD => WebRoute::METHOD_POST,
                           Route::PARAM_HANDLER   => $handler,
                           Route::PARAM_NAME      => $name
                       ] );
  }

  /**
   * Shorthand method to create a PUT route
   *
   * @param string          $path
   * @param string|Callable $handler
   * @param string          $name
   *
   * @return static
   */
  public static function put( string $path, $handler, string $name = null ): WebRoute {
    return new static( [
                           WebRoute::PARAM_PATH   => $path,
                           WebRoute::PARAM_METHOD => WebRoute::METHOD_PUT,
                           Route::PARAM_HANDLER   => $handler,
                           Route::PARAM_NAME      => $name
                       ] );
  }

  /**
   * Shorthand method to create a PATCH route
   *
   * @param string          $path
   * @param string|Callable $handler
   * @param string          $name
   *
   * @return static
   */
  public static function patch( string $path, $handler, string $name = null ): WebRoute {
    return new static( [
                           WebRoute::PARAM_PATH   => $path,
                           WebRoute::PARAM_METHOD => WebRoute::METHOD_PATCH,
                           Route::PARAM_HANDLER   => $handler,
                           Route::PARAM_NAME      => $name
                       ] );
  }

  /**
   * Shorthand method to create a DELETE route
   *
   * @param string          $path
   * @param string|Callable $handler
   * @param string          $name
   *
   * @return static
   */
  public static function delete( string $path, $handler, string $name = null ): WebRoute {
    return new static( [
                           WebRoute::PARAM_PATH   => $path,
                           WebRoute::PARAM_METHOD => WebRoute::METHOD_DELETE,
                           Route::PARAM_HANDLER   => $handler,
                           Route::PARAM_NAME      => $name
                       ] );
  }

  /**
   * Shorthand method to create an OPTIONS route
   *
   * @param string          $path
   * @param string|Callable $handler
   * @param string          $name
   *
   * @return static
   */
  public static function options( string $path, $handler, string $name = null ): WebRoute {
    return new static( [
                           WebRoute::PARAM_PATH   => $path,
                           WebRoute::PARAM_METHOD => WebRoute::METHOD_OPTIONS,
                           Route::PARAM_HANDLER   => $handler,
                           Route::PARAM_NAME      => $name
                       ] );
  }

  /**
   * Registers a route on a Slim instance
   *
   * @param \Radiergummi\DynDns\Kernel $kernel
   *
   * @return mixed
   */
  public function register( Kernel $kernel ) {

    $this->getHandler()->setKernel( $kernel );

    // looks complicated, is actually pretty simple:
    // you could replace this with $app->get('/path', $controller->method);
    // but we want to specify the method automatically, so we need the
    // call_user_func.
    return call_user_func(
        [ $kernel->getApp(), $this->getMethod() ],
        $this->getPath(),
        [ $this->getHandler(), $this->getAction() ]
    );
  }

  /**
   * Retrieves the HTTP request method this route shall be valid for
   *
   * @return string
   */
  public function getMethod(): string {
    return $this->method;
  }

  /**
   * Retrieves the request URI this route shall be valid for
   *
   * @return string
   */
  public function getPath(): string {
    return $this->path;
  }

  /**
   * Retrieves the route action (Controller method)
   *
   * @return string
   */
  public function getAction(): string {
    return $this->action;
  }

  /**
   * Sets the route handler. This is a little complex due to the support of callbacks as handlers instead
   * of controller classes only. Basically, we check for strings, assume they hold a controller class name
   * and optionally a method (if no method is given, we'll assume the route name or just index).
   * If we have a callback passed, we create an instance of the special lambda controller and inject the
   * callback into it.
   *
   * @param string|Callable $handler route handler callback or classname and optionally method
   *
   * @return void
   * @throws \RuntimeException
   */
  public function setHandler( $handler ) {

    // if we have a handler specified as controllerName@controllerMethod...
    if ( is_string( $handler ) && preg_match( '/.+@.+/', $handler ) ) {

      // split the handler and action like controllerName@controllerMethod
      list( $controllerHandle, $method ) = explode( '@', $handler );
    } else if ( is_callable( $handler ) ) {

      // if we have a callback, we'll set this statically to the Lambda controller
      $controllerHandle = Route::LAMBDA_CLASS;
      $method           = Route::DEFAULT_ACTION;
    } else {

      // no @ char in there, so this must be a class name only
      $controllerHandle = $handler;
      $method           = $this->getName();
    }

    // Resolve the controller by uppercasing the first letter and appending "Controller", then look for the class
    // within the controllers namespace
    $controller = WebRoute::CONTROLLER_NAMESPACE . ucfirst( $controllerHandle ) . WebRoute::CONTROLLER_SUFFIX;

    try {
      // create a controller instance
      $this->handler = new $controller();
    }
    catch ( Throwable $exception ) {
      // doesn't seem to be a valid class tho...
      throw new RuntimeException( 'Invalid route handler: ' . $exception->getMessage(), 0, $exception );
    }

    $this->action = $method;

    // if we have a callback, we'll set the action on the Lambda pseudo controller
    if ( is_callable( $handler ) ) {

      /** @var \Radiergummi\DynDns\Controllers\LambdaController $handler */
      /** @noinspection PhpUndefinedMethodInspection */
      $this->handler->setAction( $handler );
    }
  }
}
