<?php

namespace Radiergummi\DynDns;

/**
 * Route class
 *
 * @package Radiergummi\DynDns
 */
abstract class Route {

  /**
   * Default action method name. This is the method that will be called once your controller
   * is invoked, if you did not pass an action name.
   */
  public const    DEFAULT_ACTION = 'index';

  /**
   * Class identifier for the special lambda handlers (will resolve to LambdaController
   * and LambdaCommand).
   */
  protected const LAMBDA_CLASS   = 'lambda';

  /**
   * Handler parameter for the route constructor
   */
  protected const PARAM_HANDLER  = 'handler';

  /**
   * Name parameter for the route constructor
   */
  protected const PARAM_NAME     = 'name';

  /**
   * Holds the route name
   *
   * @var string
   */
  protected $name;

  /**
   * Holds the route handler
   *
   * @var \Radiergummi\DynDns\Controller|\Radiergummi\DynDns\Command
   */
  protected $handler;

  /**
   * Route constructor
   *
   * @param array $params
   *
   * @throws \Exception
   */
  public function __construct( array $params ) {
    $this->name = $params[ Route::PARAM_NAME ] ?? Route::DEFAULT_ACTION;
    $this->setHandler( $params[ Route::PARAM_HANDLER ] ?? $this->name );
  }

  /**
   * Retrieves the route name
   *
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * Retrieves the route handler
   *
   * @return \Radiergummi\DynDns\Controller|\Radiergummi\DynDns\Command
   */
  public function getHandler() {
    return $this->handler;
  }

  /**
   * Sets the handler
   *
   * @param string|Callable $handler
   *
   * @return void
   */
  abstract protected function setHandler( $handler );

  /**
   * Registers the route on the application kernel
   *
   * @param \Radiergummi\DynDns\Kernel $kernel
   *
   * @return void
   */
  abstract public function register( Kernel $kernel );
}
