<?php

namespace Radiergummi\DynDns;

use Symfony\Component\Console\Application;

/**
 * Console kernel
 * Application core for the command line application
 *
 * @package Radiergummi\DynDns
 */
class ConsoleKernel extends Kernel {

  /**
   * ConsoleKernel constructor
   *
   * @param \Radiergummi\DynDns\Configuration $config
   */
  public function __construct( Configuration $config ) {
    parent::__construct( $config );

    // create the Symfony Console application
    $this->app = new Application( $config->name, $config->version );
  }
}
