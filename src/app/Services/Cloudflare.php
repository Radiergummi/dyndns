<?php

namespace Radiergummi\DynDns\Services;

use Cloudflare\API\Adapter\Guzzle;
use Cloudflare\API\Auth\APIKey;
use Cloudflare\API\Endpoints\DNS;
use Cloudflare\API\Endpoints\Zones;
use stdClass;

/**
 * Cloudflare service
 * Wrapper around the Cloudflare API.
 * The SDK from Cloudflare is already a nice piece of PHP, but this class provides some
 * additional convenience methods and abstracts the dependency injection process behind
 * single methods.
 *
 * @package Radiergummi\DynDns
 */
class Cloudflare {

  /**
   * A (IPv4) DNS Record
   */
  public const DNS_RECORD_A = 'A';

  /**
   * AAAA (IPv6) DNS Record
   */
  public const DNS_RECORD_AAAA = 'AAAA';

  /**
   * CNAME DNS Record
   */
  public const DNS_RECORD_CNAME = 'CNAME';

  /**
   * MX DNS Record
   */
  public const DNS_RECORD_MX = 'MX';

  /**
   * Holds the credentials instance
   *
   * @var \Cloudflare\API\Auth\APIKey
   */
  protected $apiKey;

  /**
   * Holds the Guzzle adapter
   *
   * @var \Cloudflare\API\Adapter\Guzzle
   */
  protected $adapter;

  /**
   * Holds the zones endpoint
   *
   * @var \Cloudflare\API\Endpoints\Zones
   */
  protected $zones;

  /**
   * Holds the DNS endpoint
   *
   * @var \Cloudflare\API\Endpoints\DNS
   */
  protected $dns;

  /**
   * Cloudflare constructor
   *
   * @param string $username Cloudflare username
   * @param string $password Cloudflare password
   */
  public function __construct( string $username, string $password ) {
    $this->apiKey  = new APIKey( $username, $password );
    $this->adapter = new Guzzle( $this->apiKey );
    $this->zones   = new Zones( $this->adapter );
    $this->dns     = new DNS( $this->adapter );
  }

  /**
   * creates a new DNS record
   *
   * @param string $zoneName   name of the zone
   * @param string $recordType type of the record. Can use the constants of this class
   * @param string $recordName name of the record
   * @param string $content    record content text
   * @param int    $ttl        record DNS TTL
   * @param bool   $proxied    whether the record shall be proxied through Cloudflare
   *
   * @return void
   * @throws \Cloudflare\API\Endpoints\EndpointException
   */
  public function createRecord(
      string $zoneName,
      string $recordType,
      string $recordName,
      string $content,
      int $ttl = 1,
      bool $proxied = false
  ) {
    $this->dns->addRecord( $this->getZoneId( $zoneName ), $recordType, $recordName, $content, $ttl, $proxied );
  }

  /**
   * Retrieves the ID of a zone
   *
   * @param string $zoneName name of the zone
   *
   * @return string zone ID
   * @throws \Cloudflare\API\Endpoints\EndpointException
   */
  public function getZoneId( string $zoneName = '' ): string {
    return $this->zones->getZoneID( $zoneName );
  }

  /**
   * updates an existing record
   *
   * @param string $zoneName   name of the zone
   * @param string $recordType type of the record. Can use the constants of this class
   * @param string $recordName name of the record
   * @param string $content    record content text
   * @param int    $ttl        record DNS TTL
   * @param bool   $proxied    whether the record shall be proxied through Cloudflare
   *
   * @return void
   * @throws \Cloudflare\API\Endpoints\EndpointException
   */
  public function updateRecord(
      string $zoneName,
      string $recordType,
      string $recordName,
      string $content,
      int $ttl = 1,
      bool $proxied = false
  ) {
    $record = $this->getRecord( $zoneName, $recordName );
    $this->dns->updateRecordDetails( $this->getZoneId( $zoneName ), $record->id, [
        'type'    => $recordType,
        'name'    => $recordName,
        'content' => $content,
        'ttl'     => $ttl,
        'proxied' => $proxied
    ] );
  }

  /**
   * Retrieve the record for a domain
   *
   * @param string $zoneName   name of the Cloudflare zone
   * @param string $domainName name of the domain
   * @param string $type       optional record type
   *
   * @return null|stdClass record data
   * @throws \Cloudflare\API\Endpoints\EndpointException
   */
  public function getRecord( string $zoneName, string $domainName, string $type = '' ) {
    foreach ( $this->getRecords( $zoneName, $type ) as $record ) {
      if ( $record->name === $domainName ) {
        return $record;
      }
    }

    return null;
  }

  /**
   * Retrieves all records for a zone
   *
   * @param string $zoneName name of the zone
   * @param string $type     optional record type
   *
   * @return array zone DNS records
   * @throws \Cloudflare\API\Endpoints\EndpointException
   */
  public function getRecords( string $zoneName, string $type = '' ): array {
    $zoneId = $this->getZoneId( $zoneName );

    return $this->dns->listRecords( $zoneId, $type )->result;
  }

  /**
   * Retrieves a single zone by name
   *
   * @param string $zoneName name of the zone
   *
   * @return stdClass|null zone data
   */
  public function getZone( string $zoneName ) {
    foreach ( $this->getZones() as $zone ) {
      if ( $zone->name === $zoneName ) {
        return $zone;
      }
    }

    return null;
  }

  /**
   * Retrieves a list of all zones in the account
   *
   * @return array list of zones
   */
  public function getZones(): array {
    return $this->zones->listZones()->result;
  }
}
