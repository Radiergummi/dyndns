<?php

namespace Radiergummi\DynDns\Controllers;

use Radiergummi\DynDns\Controller;
use Slim\Http\Request;
use Slim\Http\Response;
use Throwable;
use function call_user_func;

/**
 * Lambda Controller
 * This one is a little special, since it wraps an ordinary callback into a standard controller.
 *
 * @package Radiergummi\DynDns
 */
class LambdaController extends Controller {

  /**
   * Error message for malfunctioning route handler callbacks
   */
  protected const MESSAGE_CALLBACK_ERROR = 'Could not execute route handler';

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
   * Executes the provided callback action
   *
   * @param \Slim\Http\Request  $request
   * @param \Slim\Http\Response $response
   * @param array               $args
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  public function index( Request $request, Response $response, array $args = [] ): Response {
    try {
      return call_user_func( $this->action, $request, $response, $args );
    }
    catch ( Throwable $exception ) {
      return $this->withServerError( $response, LambdaController::MESSAGE_CALLBACK_ERROR, $exception );
    }
  }
}
