<?php

namespace Radiergummi\DynDns;

use Slim\App as Slim;
use Slim\Container;

/**
 * Web kernel
 * Application core for the HTTP application
 *
 * @package Radiergummi\DynDns
 */
class WebKernel extends Kernel {

  /**
   * Holds the Slim (Pimple) Container
   *
   * @var \Slim\Container
   */
  protected $container;

  /**
   * WebKernel constructor
   *
   * @param \Radiergummi\DynDns\Configuration $config
   */
  public function __construct( Configuration $config ) {
    parent::__construct( $config );

    // create the IoC container for Slim
    $this->container = new Container( $config->slimConfiguration );

    // create the Slim application
    $this->app       = new Slim( $this->container );

    // load the authentication middleware
    $this->app->add( new CloudflareProxyAuthenticator( $this ) );


  }

  /**
   * Retrieves the WebKernel Container
   *
   * @return \Slim\Container
   */
  public function getContainer(): Container {
    return $this->container;
  }
}
