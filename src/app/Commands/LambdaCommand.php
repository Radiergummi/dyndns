<?php

namespace Radiergummi\DynDns\Commands;

use Radiergummi\DynDns\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use function call_user_func;

/**
 * Lambda Command
 * This command is a little special, since it wraps an ordinary callback into a command digestible by Symfony.
 *
 * @package Radiergummi\DynDns
 */
class LambdaCommand extends Command {

  /**
   * holds the lambda function to provide as the single action
   *
   * @var Callable
   */
  protected $action;

  /**
   * @param callable $action
   *
   * @return void
   */
  public function setAction( Callable $action ) {
    $this->action = $action;
  }

  /**
   * @return void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   */
  protected function configure() {
    $this->setName( $this->commandName );
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface   $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int|mixed|null
   */
  protected function execute( InputInterface $input, OutputInterface $output ) {
    return call_user_func( $this->action, $input, $output );
  }
}
