<?php

namespace Radiergummi\DynDns;

/**
 * Kernel class
 * Application core to be extended by children kernels
 *
 * @package Radiergummi\DynDns
 */
abstract class Kernel {

  /**
   * Holds the app instance
   *
   * @var \Slim\App|\Symfony\Component\Console\Application
   */
  protected $app;

  /**
   * Holds the application configuration
   *
   * @var \Radiergummi\DynDns\Configuration
   */
  protected $config;

  /**
   * Kernel constructor
   *
   * @param \Radiergummi\DynDns\Configuration $config
   */
  public function __construct( Configuration $config ) {
    $this->config = $config;
  }

  /**
   * Retrieves the Kernel App
   *
   * @return \Slim\App|\Symfony\Component\Console\Application
   */
  public function getApp() {
    return $this->app;
  }

  /**
   * Initializes and runs the app
   *
   * @return void
   */
  public function init() {
    $this->loadRoutes();

    // run the underlying application
    $this->app->run();
  }

  /**
   * Loads all application routes and registers them on the app
   *
   * @return void
   */
  protected function loadRoutes() {

    /** @var \Radiergummi\DynDns\Route $route */
    foreach ( $this->getRoutes() as $route ) {

      // register each route on this kernel
      $route->register( $this );
    }
  }

  /**
   * Retrieves the Kernel Routes
   *
   * @return array
   */
  public function getRoutes(): array {
    return $this->config->routes;
  }

  /**
   * Retrieves the Kernel Config
   *
   * @return \Radiergummi\DynDns\Configuration
   */
  public function getConfig(): Configuration {
    return $this->config;
  }

  /**
   * Retrieves the application secret
   *
   * @return string
   */
  public function getSecret(): string {
    return $this->config->secret;
  }
}
