<?php

namespace Radiergummi\DynDns;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

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
   * Holds the logger instance
   *
   * @var \Monolog\Logger
   */
  protected $logger;

  /**
   * Kernel constructor
   *
   * @param \Radiergummi\DynDns\Configuration $config
   */
  public function __construct( Configuration $config ) {
    $this->config = $config;

    if ( $this->config->logEnabled ) {
      $this->logger = new Logger( $this->config->name );
      $this->logger->pushHandler( new StreamHandler( $this->config->logPath ) );
    }
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
   * Retrieves the application secret
   *
   * @return string
   */
  public function getSecret(): string {
    return $this->config->secret;
  }

  public function logError( string $message, array $context = [] ) {
    if ( $this->getConfig()->logEnabled ) {
      $this->logger->error( $message, $context );
    }
  }

  /**
   * Retrieves the Kernel Config
   *
   * @return \Radiergummi\DynDns\Configuration
   */
  public function getConfig(): Configuration {
    return $this->config;
  }

  public function logInfo( string $message, array $context = [] ) {
    if ( $this->getConfig()->logEnabled ) {
      $this->logger->info( $message, $context );
    }
  }

  public function logWarning( string $message, array $context = [] ) {
    if ( $this->getConfig()->logEnabled ) {
      $this->logger->warning( $message, $context );
    }
  }

  public function logDebug( string $message, array $context = [] ) {
    if ( $this->getConfig()->debug && $this->getConfig()->logEnabled ) {
      $this->logger->debug( $message, $context );
    }
  }
}
