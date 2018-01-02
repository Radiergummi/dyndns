<?php

namespace Radiergummi\DynDns;

use RuntimeException;
use Throwable;
use function is_callable;

/**
 * Console Route
 * Provides helper methods to create new commands via the route file
 *
 * @package Radiergummi\DynDns
 */
class ConsoleRoute extends Route {

  /**
   * Namespace for commands
   */
  protected const COMMAND_NAMESPACE = __NAMESPACE__ . '\\Commands\\';

  /**
   * Class name suffix for commands. This defines how the commands will be created.
   * Taking the default value 'Command', we will see command classes like "FooCommand"
   */
  protected const COMMAND_SUFFIX    = 'Command';

  /**
   * Shorthand method to add a command
   *
   * @param string $name    command name (as entered on the CLI)
   * @param string $handler command handler class
   *
   * @return \Radiergummi\DynDns\ConsoleRoute
   */
  public static function add( string $name, string $handler ): ConsoleRoute {
    return new static( [
                           Route::PARAM_NAME    => $name,
                           Route::PARAM_HANDLER => $handler
                       ] );
  }

  /**
   * Registers a route on a Symfony Console instance
   *
   * @param \Radiergummi\DynDns\Kernel $kernel
   *
   * @return mixed
   */
  public function register( Kernel $kernel ) {
    $this->getHandler()->setKernel( $kernel );

    return $kernel->getApp()->add( $this->getHandler() );
  }

  /**
   * Sets the handler. To account for callbacks instead of class names, the special 'LambdaCommand' is in place.
   * It's execute method simply runs the callback provided as the handler here.
   * Otherwise, all strings passed to ConsoleRoutes will be assumed to be class names for commands.
   *
   * @param string|Callable $handler
   *
   * @return void
   * @throws \RuntimeException
   */
  public function setHandler( $handler ) {
    $commandHandle = ( is_callable( $handler )
        ? Route::LAMBDA_CLASS
        : $handler
    );

    // Create the command class path
    $command = ConsoleRoute::COMMAND_NAMESPACE . ucfirst( $commandHandle ) . ConsoleRoute::COMMAND_SUFFIX;

    try {
      $this->handler = new $command( $this->getName() );
    }
    catch ( Throwable $exception ) {
      // Doesn't seem to be a valid class tho...
      throw new RuntimeException( 'Invalid route handler: ' . $exception->getMessage(), 0, $exception );
    }

    // if we're dealing with a callback, we'll need to attach the action to the Lambda instance
    if ( is_callable( $handler ) ) {

      /** @var \Radiergummi\DynDns\Commands\LambdaCommand $this ->handler */
      /** @noinspection PhpUndefinedMethodInspection */
      $this->handler->setAction( $handler );
    }
  }
}
