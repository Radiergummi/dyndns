<?php

namespace Radiergummi\DynDns\Controllers;

use Radiergummi\DynDns\Controller;
use Radiergummi\DynDns\WebRoute;
use ReflectionClass;
use Slim\Http\Request;
use Slim\Http\Response;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_SLASHES;
use function preg_match;
use function preg_replace;
use function strtoupper;

/**
 * Home Controller
 *
 * @package Radiergummi\DynDns\Controllers
 */
class HomeController extends Controller {

  /**
   * Shows a list of all API endpoints
   *
   * @param \Slim\Http\Request  $request
   * @param \Slim\Http\Response $response
   *
   * @return Response
   * @throws \RuntimeException
   */
  public function index( Request $request, Response $response ): Response {
    $routes = [];

    /** @var WebRoute $route */
    foreach ( $this->getKernel()->getConfig()->routes as $route ) {

      // reflect on the route controller
      $controllerReflection = new ReflectionClass( $route->getHandler() );

      // retrieve the doc comment of the action method and remove all comment characters from it (/**, * , */)
      $docComment = preg_replace(
          '#^\s*\/?\*+\s?\/?#m',
          '',
          $controllerReflection->getMethod( $route->getAction() )->getDocComment()
      );

      // retrieve the description text (anything before the first @ character)
      preg_match(
          '/^([^@]+)/',
          $docComment,
          $routeDescription
      );

      // print the HTTP method, the route path and the description
      $routes[ sprintf( '[%s] %s', strtoupper( $route->getMethod() ), $route->getPath() ) ] = trim( $routeDescription[0] ?? ''
      );
    }

    // send the JSON pretty printed (so it looks nice in the browser too)
    return $response->withJson(
        $routes,
        null,
        JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT
    );
  }
}
