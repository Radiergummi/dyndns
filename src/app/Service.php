<?php

namespace Radiergummi\DynDns;

/**
 * Service class
 *
 * @package Radiergummi\DynDns
 */
abstract class Service {
  protected $kernel;

  protected function getKernel(): Kernel {
    return $this->kernel;
  }

  public function register( Kernel $kernel ) {
    $this->kernel = $kernel;
  }
}
