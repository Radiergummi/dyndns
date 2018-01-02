<?php

namespace Radiergummi\DynDns;

use Symfony\Component\Console\Command\Command as ConsoleCommand;

/**
 * Command class
 * Base class for all commands
 *
 * @package Radiergummi\DynDns
 */
abstract class Command extends ConsoleCommand {

  /**
   * Holds the kernel instance for this application
   *
   * @var \Radiergummi\DynDns\Kernel
   */
  protected $kernel;

  /**
   * Holds the command name. Can be overwritten in the configure method
   *
   * @var string
   */
  protected $commandName;

  /**
   * Command constructor
   *
   * @param string $name
   *
   * @throws \Symfony\Component\Console\Exception\LogicException
   */
  public function __construct( string $name ) {
    $this->commandName = $name;

    parent::__construct();
  }

  /**
   * Retrieves the kernel instance for this application
   *
   * @return \Radiergummi\DynDns\Kernel
   */
  public function getKernel(): Kernel {
    return $this->kernel;
  }

  /**
   * Sets the kernel instance for this application
   *
   * @param \Radiergummi\DynDns\Kernel $kernel
   *
   * @return void
   */
  public function setKernel( Kernel $kernel ) {
    $this->kernel = $kernel;
  }

  /**
   * Base configure method as required by Symfony Console. It sets the command
   * name as passed from the routes file, unless overwritten by children methods
   *
   * @return void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   */
  protected function configure() {
    $this->setName( $this->commandName );
  }
}
