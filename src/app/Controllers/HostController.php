<?php

namespace Radiergummi\DynDns\Controllers;

use Cloudflare\API\Endpoints\EndpointException;
use GuzzleHttp\Exception\ClientException;
use Radiergummi\DynDns\CloudflareProxyAuthenticator;
use Radiergummi\DynDns\Controller;
use Radiergummi\DynDns\Services\Cloudflare;
use Radiergummi\DynDns\Services\DynDns;
use Slim\Http\Request;
use Slim\Http\Response;
use Throwable;

/**
 * Host Controller
 *
 * @package Radiergummi\DynDns\Controllers
 */
class HostController extends Controller {

  /**
   * Field for the hostname argument in the route path.
   * This needs to conform to the format specified in the routes file.
   */
  public const    FIELD_HOSTNAME           = 'hostname';

  /**
   * Field for the IPv4 address in the body or GET parameter.
   */
  public const    FIELD_IPV4               = 'ipv4';

  /**
   * Field for the IPv6 address in the body or GET parameter.
   */
  public const    FIELD_IPV6               = 'ipv6';

  /**
   * Field for the zone argument in the route path.
   * This needs to conform to the format specified in the routes file.
   */
  public const    FIELD_ZONE               = 'zone';

  /**
   * Error message for failed DNS record fetch attempts
   */
  protected const MESSAGE_FETCH_FAILED     = 'Could not fetch DNS record';

  /**
   * Error message for nonexistent host names
   */
  protected const MESSAGE_HOST_NOT_FOUND   = 'Host not found';

  /**
   * Error message for invalid or no IP addresses
   */
  protected const MESSAGE_INVALID_IP       = 'Invalid IP address supplied';

  /**
   * Error message for failed DNS record update attempts
   */
  protected const MESSAGE_UPDATE_FAILED    = 'Could not update DNS record';

  /**
   * Success message for successful DNS record update attempts
   */
  protected const MESSAGE_UPDATE_SUCCEEDED = 'Record updated successfully';

  /**
   * Updates a DNS record. Must contain fields for 'ipv4' and/or 'ipv6' in the body (or params for GET).
   *
   * @param \Slim\Http\Request  $request
   * @param \Slim\Http\Response $response
   * @param array               $args
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  public function update( Request $request, Response $response, array $args ): Response {
    $zone     = $args[ HostController::FIELD_ZONE ];
    $hostname = $args[ HostController::FIELD_HOSTNAME ];

    // search the body and the request params for the IP address(es). Due to many routers only being
    // able to use GET requests for the update, we'll need to accept those too.
    $ipv4Address = $request->getParsedBodyParam( HostController::FIELD_IPV4 )
                   ?? $request->getParam( HostController::FIELD_IPV4 );
    $ipv6Address = $request->getParsedBodyParam( HostController::FIELD_IPV6 )
                   ?? $request->getParam( HostController::FIELD_IPV6 );

    // check if we have either a V4 or V6 IP address and the zone name
    if ( ! ( $ipv4Address || $ipv6Address ) ) {
      return $this->withClientError( $response, HostController::MESSAGE_INVALID_IP );
    }

    $cloudflare = new Cloudflare(
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_USERNAME ),
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_PASSWORD )
    );
    $dynDns     = new DynDns( $cloudflare );

    // try to update the DNS record
    try {
      $dynDns->update( $zone, $hostname, $ipv4Address, $ipv6Address );
    }
    catch ( ClientException $exception ) {
      return $this->withClientError( $response, HostController::MESSAGE_UPDATE_FAILED, $exception );
    }
    catch ( EndpointException $exception ) {
      return $this->withServerError( $response, HostController::MESSAGE_UPDATE_FAILED, $exception );
    }

    return $this->withSuccess( $response, HostController::MESSAGE_UPDATE_SUCCEEDED );
  }

  /**
   * Retrieves a single host DNS record
   *
   * @param \Slim\Http\Request  $request
   * @param \Slim\Http\Response $response
   * @param array               $args
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  public function index( Request $request, Response $response, array $args ): Response {
    $zone     = $args[ HostController::FIELD_ZONE ];
    $hostname = $args[ HostController::FIELD_HOSTNAME ];

    $cloudflare = new Cloudflare(
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_USERNAME ),
        $request->getAttribute( CloudflareProxyAuthenticator::ATTRIBUTE_PASSWORD )
    );

    // try to fetch the DNS record from Cloudflare
    try {
      $record = $cloudflare->getRecord( $zone, $hostname );
    }
    catch ( Throwable $exception ) {
      return $this->withServerError( $response, HostController::MESSAGE_FETCH_FAILED, $exception );
    }

    if ( $record ) {
      return $this->withSuccess( $response, $record );
    }

    return $this->notFound( $response, HostController::MESSAGE_HOST_NOT_FOUND );
  }
}
