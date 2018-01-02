<?php

namespace Radiergummi\DynDns\Services;

/**
 * DynDns service
 * The main service holding the logic to perform DynDNS actions.
 *
 * @package Radiergummi\DynDns\Services
 */
class DynDns {

  /**
   * Holds the Cloudflare service instance
   *
   * @var \Radiergummi\DynDns\Services\Cloudflare
   */
  protected $cloudflare;

  /**
   * DynDns constructor
   *
   * @param \Radiergummi\DynDns\Services\Cloudflare $cloudflare
   */
  public function __construct( Cloudflare $cloudflare ) {
    $this->cloudflare = $cloudflare;
  }

  /**
   * Update the Cloudflare DNS, A and AAAA records are updated separately.
   *
   * @param string $zone
   * @param string $hostname
   * @param string $ipv4Address
   * @param string $ipv6Address
   *
   * @return void
   * @throws \Cloudflare\API\Endpoints\EndpointException
   */
  public function update(
      string $zone,
      string $hostname,
      string $ipv4Address = null,
      string $ipv6Address = null
  ) {
    if ( $ipv4Address ) {
      $dynDnsV4Record = $this->cloudflare->getRecord( $zone, $hostname, Cloudflare::DNS_RECORD_A );

      if ( ! $dynDnsV4Record ) {

        // no record yet, create one
        $this->cloudflare->createRecord( $zone, Cloudflare::DNS_RECORD_A, $hostname, $ipv4Address );
      } else if ( $dynDnsV4Record->content !== $ipv4Address ) {

        // DNS record exists, but the IPv4 address doesn't match
        $this->cloudflare->updateRecord( $zone, Cloudflare::DNS_RECORD_A, $hostname, $ipv4Address );
      }
    }

    if ( $ipv6Address ) {
      $dynDnsV6Record = $this->cloudflare->getRecord( $zone, $hostname, Cloudflare::DNS_RECORD_AAAA );

      if ( ! $dynDnsV6Record ) {

        // no record yet, create one
        $this->cloudflare->createRecord( $zone, Cloudflare::DNS_RECORD_AAAA, $hostname, $ipv6Address );
      } else if ( $dynDnsV6Record->content !== $ipv6Address ) {

        // DNS record exists, but the IPv4 address doesn't match
        $this->cloudflare->updateRecord( $zone, Cloudflare::DNS_RECORD_AAAA, $hostname, $ipv6Address );
      }
    }
  }
}
