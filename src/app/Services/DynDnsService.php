<?php

namespace Radiergummi\DynDns\Services;

use Radiergummi\DynDns\Service;

/**
 * DynDns service
 * The main service holding the logic to perform DynDNS actions.
 *
 * @package Radiergummi\DynDns\Services
 */
class DynDnsService extends Service {

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
   * @throws \InvalidArgumentException
   */
  public function update(
      string $zone,
      string $hostname,
      string $ipv4Address = null,
      string $ipv6Address = null
  ) {
    /** @var \Radiergummi\DynDns\Services\CloudflareService $cloudflare */
    $cloudflare = $this->getKernel()->getService( CloudflareService::class );

    if ( $ipv4Address ) {
      $dynDnsV4Record = $cloudflare->getRecord( $zone, $hostname, CloudflareService::DNS_RECORD_A );

      if ( ! $dynDnsV4Record ) {

        // no record yet, create one
        $cloudflare->createRecord( $zone, CloudflareService::DNS_RECORD_A, $hostname, $ipv4Address );
      } else if ( $dynDnsV4Record->content !== $ipv4Address ) {
        $this->getKernel()->logDebug( 'Found record for {name}, but IPv4 differs: {remote} != {local}', [
            'name'   => $dynDnsV4Record->name,
            'remote' => $dynDnsV4Record->content,
            'local'  => $ipv4Address
        ] );

        // DNS record exists, but the IPv4 address doesn't match
        $cloudflare->updateRecord( $zone, CloudflareService::DNS_RECORD_A, $hostname, $ipv4Address );
      }
    }

    if ( $ipv6Address ) {
      $dynDnsV6Record = $cloudflare->getRecord( $zone, $hostname, CloudflareService::DNS_RECORD_AAAA );

      if ( ! $dynDnsV6Record ) {
        $this->getKernel()->logDebug( 'Found no record for {name} ({remote}), creating a new one', [
            'name'   => $hostname,
            'remote' => $dynDnsV6Record->name
        ] );

        // no record yet, create one
        $cloudflare->createRecord( $zone, CloudflareService::DNS_RECORD_AAAA, $hostname, $ipv6Address );
      } else if ( $dynDnsV6Record->content !== $ipv6Address ) {
        $this->getKernel()->logDebug( 'Found record for {name}, but IPv6 differs: {remote} != {local}', [
            'name'   => $hostname,
            'remote' => $dynDnsV6Record->content,
            'local'  => $ipv4Address
        ] );

        // DNS record exists, but the IPv4 address doesn't match
        $cloudflare->updateRecord( $zone, CloudflareService::DNS_RECORD_AAAA, $hostname, $ipv6Address );
      }
    }
  }
}
