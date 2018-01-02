<?php

namespace Radiergummi\DynDns\Controllers;

use Radiergummi\DynDns\CloudflareProxyAuthenticator;
use Radiergummi\DynDns\Controller;
use Radiergummi\DynDns\Services\Cloudflare;
use Slim\Http\Request;
use Slim\Http\Response;
use Throwable;

/**
 * Zone Controller
 *
 * @package Radiergummi\DynDns\Controllers
 */
class ZoneController extends Controller {

  /**
   * Name of the field for the zone argument in the route path.
   * This needs to conform to the format specified in the routes file.
   */
  protected const FIELD_ZONE             = 'zone';

  /**
   * Error message for failed zone fetch attempts
   */
  protected const MESSAGE_FETCH_FAILED   = 'Could not fetch zones';

  /**
   * Error message for nonexistent zone names
   */
  protected const MESSAGE_ZONE_NOT_FOUND = 'Zone not found';

  /**
   * Retrieves data from a single zone
   *
   * @param \Slim\Http\Request  $request
   * @param \Slim\Http\Response $response
   * @param array               $args
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  public function single( Request $request, Response $response, array $args ): Response {
    $cloudflare = new Cloudflare(
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_USERNAME ),
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_PASSWORD )
    );

    try {
      $zone = $cloudflare->getZone( $args[ ZoneController::FIELD_ZONE ] );
    }
    catch ( Throwable $exception ) {
      return $this->withServerError( $response, ZoneController::MESSAGE_FETCH_FAILED, $exception );
    }

    if ( $zone ) {
      return $this->withSuccess( $response, $zone );
    }

    return $this->notFound( $response, ZoneController::MESSAGE_ZONE_NOT_FOUND );
  }

  /**
   * Retrieves a list of all zones
   *
   * @param \Slim\Http\Request  $request
   * @param \Slim\Http\Response $response
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  public function index( Request $request, Response $response ): Response {
    $cloudflare = new Cloudflare(
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_USERNAME ),
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_PASSWORD )
    );

    try {
      $zones = $cloudflare->getZones();
    }
    catch ( Throwable $exception ) {
      return $this->withServerError( $response, ZoneController::MESSAGE_FETCH_FAILED, $exception );
    }

    return $this->withSuccess( $response, $zones );
  }
}
