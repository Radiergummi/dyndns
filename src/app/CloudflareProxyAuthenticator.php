<?php

namespace Radiergummi\DynDns;

use Radiergummi\DynDns\Services\Authentication;
use Slim\Http\Request;
use Slim\Http\Response;

/**
 * Cloudflare authentication middleware for Slim. It does not actually authenticate requests, but rather
 * parse the Authorization header and attach the decrypted credentials to the request object.
 *
 * @package Radiergummi\DynDns
 */
class CloudflareProxyAuthenticator {

  /**
   * Name of the password attribute as accessible from the $request object in controller actions
   */
  public const ATTRIBUTE_PASSWORD = 'password';

  /**
   * Name of the username attribute as accessible from the $request object in controller actions
   */
  public const ATTRIBUTE_USERNAME = 'username';

  /**
   * Holds the application kernel
   *
   * @var \Radiergummi\DynDns\Kernel
   */
  protected $kernel;

  /**
   * Holds the Authentication instance
   *
   * @var \Radiergummi\DynDns\Services\Authentication
   */
  protected $authentication;

  /**
   * Holds the authentication realm
   *
   * @var string
   */
  protected $realm;

  /**
   * Holds the list of paths to not be authorized
   *
   * @var array
   */
  protected $exceptions;

  /**
   * CloudflareProxyAuthenticator constructor
   *
   * @param \Radiergummi\DynDns\Kernel $kernel     app kernel
   * @param array                      $exceptions list of paths to not be authorized
   * @param string                     $realm      authentication realm as shown in the dialog
   */
  public function __construct( Kernel $kernel, array $exceptions = [], string $realm = '' ) {
    $this->kernel         = $kernel;
    $this->authentication = new Authentication( $this->kernel->getConfig()->secret );
    $this->exceptions     = $exceptions;
    $this->realm          = $this->kernel->getConfig()->name;
  }

  /**
   * Middleware main method - checks for the basic auth header and attaches the actual, decrypted credentials
   *
   * @param \Slim\Http\Request  $request
   * @param \Slim\Http\Response $response
   * @param                     $next
   *
   * @return \Slim\Http\Response
   * @throws \InvalidArgumentException
   */
  public function __invoke( Request $request, Response $response, $next ) {

    // check whether this request needs to be authenticated at all
    if ( ! $this->shouldAuthenticate( $request ) ) {
      return $next( $request, $response );
    }

    // if the request has no basic auth header, we refuse to serve it
    if ( ! $request->hasHeader( 'Authorization' ) ) {
      return $response
          ->withStatus( 401 )
          ->withHeader( "WWW-Authenticate", sprintf( 'Basic realm="%s"', $this->realm ) );
    }

    $username = false;
    $password = false;

    // extract the credentials from the basic auth header
    if ( preg_match( "/Basic\s+(.*)$/i", $request->getHeader( 'Authorization' )[0], $matches ) ) {
      list( $username, $password ) = explode( ":", base64_decode( $matches[1] ), 2 );
    }

    // at this point, we have the correct credentials available, so we'll attach them to the request
    $request = $request->withAttribute(
        CloudflareProxyAuthenticator::ATTRIBUTE_USERNAME,
        $username
    );

    $request = $request->withAttribute(
        CloudflareProxyAuthenticator::ATTRIBUTE_PASSWORD,
        $this->authentication->decrypt( $password )
    );

    return $next( $request, $response );
  }

  /**
   * Checks whether a route should not be authenticated. All exceptions need to be specified as an array of paths.
   *
   * @param \Slim\Http\Request $request main request object
   *
   * @return bool whether the route should be authenticated
   */
  protected function shouldAuthenticate( Request $request ): bool {
    $uri = $request->getUri()->getPath();

    foreach ( $this->exceptions as $path ) {
      $path = rtrim( $path, '/' );

      // check if the request path is in the exception list
      if ( ! ! preg_match( "@^{$path}(/.*)?$@", $uri ) ) {
        return false;
      }
    }

    // assume true for all other routes
    return true;
  }
}
