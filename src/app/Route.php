<?php

namespace Radiergummi\DynDns;

/**
 * Route class
 *
 * @package Radiergummi\DynDns
 */
abstract class Route {
  public const    DEFAULT_ACTION = 'index';

  protected const LAMBDA_CLASS   = 'lambda';

  protected const PARAM_HANDLER  = 'handler';

  protected const PARAM_NAME     = 'name';

  /**
   * holds the route name
   *
   * @var string
   */
  protected $name;

  /**
   * holds the route handler
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
   * retrieves the route name
   *
   * @return string
   */
  public function getName(): string {
    return $this->name;
  }

  /**
   * retrieves the route handler
   *
   * @return \Radiergummi\DynDns\Controller|\Radiergummi\DynDns\Command
   */
  public function getHandler() {
    return $this->handler;
  }

  /**
   * sets the handler
   *
   * @param string|Callable $handler
   *
   * @return void
   */
  abstract protected function setHandler( $handler );

  /**
   * registers the route on the application kernel
   *
   * @param \Radiergummi\DynDns\Kernel $kernel
   *
   * @return void
   */
  abstract public function register( Kernel $kernel );
}
