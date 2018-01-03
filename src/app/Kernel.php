<?php

namespace Radiergummi\DynDns;

use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Throwable;

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
   * Holds service instances
   *
   * @var array
   */
  protected $services = [];

  /**
   * Holds arguments for services
   *
   * @var array
   */
  protected $serviceArguments = [];

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
   * Retrieves a service instance or creates a new one if none is available yet
   *
   * @param string $serviceName
   *
   * @return \Radiergummi\DynDns\Service
   * @throws \InvalidArgumentException
   */
  public function getService( string $serviceName ): Service {
    if ( ! $this->services[ $serviceName ] ) {
      $this->loadService( $serviceName );
    }

    return $this->services[ $serviceName ];
  }

  /**
   * Loads a service
   *
   * @param string $serviceName
   *
   * @return \Radiergummi\DynDns\Service
   * @throws \InvalidArgumentException
   */
  public function loadService( string $serviceName ): Service {
    try {
      /** @var \Radiergummi\DynDns\Service $service */
      $service = new $serviceName( ...$this->getServiceArguments( $serviceName ) );
    }
    catch ( Throwable $exception ) {
      throw new InvalidArgumentException( 'Could not load service ' . $serviceName . ': ' . $exception->getMessage() );
    }

    $service->register( $this );
    $this->services[ $serviceName ] = $service;

    return $service;
  }

  /**
   * Retrieves all constructor arguments for a service
   *
   * @param string $serviceName
   *
   * @return array
   */
  public function getServiceArguments( string $serviceName ): array {
    if ( $this->serviceArguments[ $serviceName ] ) {
      return $this->serviceArguments[ $serviceName ];
    }

    return [];
  }

  /**
   * Returns a new service instance
   *
   * @param string $serviceName
   * @param array  ...$serviceArguments
   *
   * @return \Radiergummi\DynDns\Service
   * @throws \InvalidArgumentException
   */
  public function getFactory( string $serviceName, ...$serviceArguments ) {
    $this->registerServiceArguments( $serviceName, $serviceArguments );

    return $this->loadService( $serviceName );
  }

  /**
   * Registers constructor arguments for a service
   *
   * @param string $serviceName
   * @param array  $serviceArguments
   *
   * @return void
   */
  public function registerServiceArguments( string $serviceName, array $serviceArguments = [] ) {
    $this->serviceArguments[ $serviceName ] = $serviceArguments;
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

  /**
   * logs an error message
   *
   * @param string $message
   * @param array  $context
   *
   * @return void
   */
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

  /**
   * logs an info message
   *
   * @param string $message
   * @param array  $context
   *
   * @return void
   */
  public function logInfo( string $message, array $context = [] ) {
    if ( $this->getConfig()->logEnabled ) {
      $this->logger->info( $message, $context );
    }
  }

  /**
   * logs a warning message
   *
   * @param string $message
   * @param array  $context
   *
   * @return void
   */
  public function logWarning( string $message, array $context = [] ) {
    if ( $this->getConfig()->logEnabled ) {
      $this->logger->warning( $message, $context );
    }
  }

  /**
   * logs a debug message, if debug mode is enabled
   *
   * @param string $message
   * @param array  $context
   *
   * @return void
   */
  public function logDebug( string $message, array $context = [] ) {
    if ( $this->getConfig()->debug && $this->getConfig()->logEnabled ) {
      $this->logger->debug( $message, $context );
    }
  }
}
