<?php

namespace Radiergummi\DynDns\Services;

use GuzzleHttp\Client;
use InvalidArgumentException;
use Radiergummi\DynDns\Service;
use RuntimeException;
use Throwable;

/**
 * IpAddress Service
 * This service uses icanhazip.com for IP resolution
 *
 * @package Radiergummi\DynDns\Services
 */
class IpAddressService extends Service {

  /**
   * IPv4 subdomain for resolver
   */
  public const    IPV4 = 'ipv4';

  /**
   * IPv6 subdomain for resolver
   */
  public const    IPV6 = 'ipv6';

  /**
   * External IP resolver host URL
   */
  protected const RESOLVER_URL = 'icanhazip.com';

  /**
   * HTTP adapter
   *
   * @var \GuzzleHttp\Client
   */
  protected $adapter;

  public function __construct() {
    $this->adapter = new Client();
  }

  /**
   * Retrieves the IP address of an arbitrary public hostname
   *
   * @param string $hostname hostname to find an IP address for
   * @param string $type     IP protocol version
   *
   * @return string resolved IP address
   */
  public function getIpAddress( string $hostname, string $type = IpAddressService::IPV4 ): string {
    // TODO: Implement method
  }

  /**
   * retrieves the current public server IP address
   *
   * @param string $type IP protocol version
   *
   * @return string resolved IP address
   * @throws \InvalidArgumentException
   * @throws \RuntimeException
   */
  public function getOwnIpAddress( string $type = IpAddressService::IPV4 ): string {
    if ( $type !== IpAddressService::IPV4 && $type !== IpAddressService::IPV6 ) {
      throw new InvalidArgumentException( 'Invalid protocol type ' . $type );
    }

    try {
      $response = $this->adapter->get( $type . '.' . IpAddressService::RESOLVER_URL );
    }
    catch ( Throwable $exception ) {
      throw new RuntimeException( 'Cannot resolve IP address of this host: ' . $exception->getMessage() );
    }

    return trim( $response->getBody()->getContents() );
  }
}
