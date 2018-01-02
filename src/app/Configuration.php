<?php

namespace Radiergummi\DynDns;

use Radiergummi\DynDns\Services\Authentication;

/**
 * Configuration class
 * Provides all configuration options with default values (you might notice
 * I'm a huge fan of IDE autocompletion).
 *
 * @package Radiergummi\DynDns
 */
class Configuration {

  /**
   * Application name
   *
   * @var string
   */
  public $name = 'Cloudflare DynDNS';

  /**
   * Application version
   *
   * @var string
   */
  public $version = '1.0.0';

  /**
   * Application secret string. This value must be changed! It is used to encrypt your Cloudflare passwords.
   *
   * @var string
   */
  public $secret = Authentication::DEFAULT_SECRET;

  /**
   * Whether to put the application in debugging mode. This also affects publicly visible stack traces, so be
   * careful not to enable this in production.
   *
   * @var bool
   */
  public $debug = false;

  /**
   * Application routes. This should be populated with the routes valid for the current context (web/CLI)
   *
   * @var array
   */
  public $routes = [];

  /**
   * Configuration options for Slim
   *
   * @var array
   */
  public $slimConfiguration = [];
}
