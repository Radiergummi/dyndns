<?php

namespace Radiergummi\DynDns;

use Radiergummi\DynDns\Services\AuthenticationService;

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
  public $secret = AuthenticationService::DEFAULT_SECRET;

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

  /**
   * Whether logging is enabled
   *
   * @var bool
   */
  public $logEnabled = true;

  /** @noinspection SpellCheckingInspection */
  /**
   * Where to write the log to
   *
   * @var string
   */
  public $logPath = 'dyndns.log';
}
