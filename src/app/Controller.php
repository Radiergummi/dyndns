<?php

namespace Radiergummi\DynDns;

use Cloudflare\API\Endpoints\EndpointException;
use GuzzleHttp\Exception\ClientException;
use Slim\Http\Response;
use Throwable;

/**
 * Controller class
 * Base class for all controllers
 *
 * @package Radiergummi\DynDns
 */
abstract class Controller {

  /**
   * The result field of the response JSON object
   */
  protected const RESPONSE_FIELD_RESULT = 'result';

  /**
   * The status field of the response JSON object
   */
  protected const RESPONSE_FIELD_STATUS = 'status';

  /**
   * The status message for erroneous responses
   */
  protected const STATUS_ERROR = 'error';

  /**
   * The status message for successful responses
   */
  protected const STATUS_SUCCESS = 'success';

  /**
   * Holds the kernel instance for this application
   *
   * @var \Radiergummi\DynDns\Kernel
   */
  protected $kernel;

  /**
   * Shorthand method for 404 errors
   *
   * @param \Slim\Http\Response $response
   * @param string              $message
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  protected function notFound( Response $response, string $message ): Response {
    $this->getKernel()->logWarning( $message );

    return $this->withError( $response, 404, $message );
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
   * Shorthand method to create valid error responses
   *
   * @param \Slim\Http\Response $response
   * @param int                 $status
   * @param string              $message
   * @param \Throwable|null     $exception
   *
   * @return Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  protected function withError(
      Response $response,
      int $status,
      string $message,
      Throwable $exception = null
  ): Response {
    if ( $exception instanceof ClientException || $exception instanceof EndpointException ) {
      $status = $exception->getResponse()->getStatusCode();
    }

    $this->getKernel()->logError( $message . ': ' . $exception->getMessage() );

    return $response
        ->withStatus( $status )
        ->withJson( [
                        Controller::RESPONSE_FIELD_STATUS => Controller::STATUS_ERROR,
                        Controller::RESPONSE_FIELD_RESULT => ( $exception
                            ? [
                                'message' => $message,
                                'error'   => $exception
                            ]
                            : $message
                        )
                    ] );
  }

  /**
   * Shorthand method for 500 errors
   *
   * @param \Slim\Http\Response $response
   * @param string              $message
   * @param \Throwable|null     $exception
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  protected function withServerError(
      Response $response,
      string $message,
      Throwable $exception = null
  ): Response {
    return $this->withError( $response, 500, $message, $exception );
  }

  /**
   * Shorthand method for 400 errors
   *
   * @param \Slim\Http\Response $response
   * @param string              $message
   * @param \Throwable|null     $exception
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  protected function withClientError(
      Response $response,
      string $message,
      Throwable $exception = null
  ): Response {
    return $this->withError( $response, 400, $message, $exception );
  }

  /**
   * Shorthand method to create valid success responses
   *
   * @param \Slim\Http\Response $response slim response object
   * @param mixed               $data     response data
   *
   * @return Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  protected function withSuccess( Response $response, $data ): Response {
    $this->getKernel()->logInfo( $data );

    return $response
        ->withStatus( 200 )
        ->withJson( [
                        Controller::RESPONSE_FIELD_STATUS => Controller::STATUS_SUCCESS,
                        Controller::RESPONSE_FIELD_RESULT => $data
                    ] );
  }
}
