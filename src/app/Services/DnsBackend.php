<?php

namespace Radiergummi\DynDns\Services;

/**
 * DNS backend interface
 * This describes a contract for DNS backends
 *
 * @package Radiergummi\DynDns\Services
 */
interface DnsBackend {

  /**
   * Create a new DNS record
   *
   * @param string $zoneName   name of the DNS zone
   * @param string $recordType record type (A, AAAA, etc.)
   * @param string $recordName name of the record (hostname)
   * @param string $content    record content (IP address)
   * @param int    $ttl        Time To Leave. Defaults to 1
   * @param array  $meta       Metadata for the backend to consume
   *
   * @return void
   */
  public function createRecord(
      string $zoneName,
      string $recordType,
      string $recordName,
      string $content,
      int $ttl = 1,
      array $meta = []
  );

  /**
   * Update an existing DNS record
   *
   * @param string $zoneName   name of the DNS zone
   * @param string $recordType record type (A, AAAA, etc.)
   * @param string $recordName name of the record (hostname)
   * @param string $content    record content (IP address)
   * @param int    $ttl        Time To Leave. Defaults to 1
   * @param array  $meta       Metadata for the backend to consume
   *
   * @return void
   */
  public function updateRecord(
      string $zoneName,
      string $recordType,
      string $recordName,
      string $content,
      int $ttl = 1,
      array $meta = []
  );

  /**
   * Retrieve a DNS record
   *
   * @param string $zoneName   name of the DNS zone
   * @param string $domainName name of the record (hostname)
   * @param string $type       optional record type (A, AAAA, etc.)
   *
   * @return \stdClass|null
   */
  public function getRecord( string $zoneName, string $domainName, string $type = '' );

  /**
   * Retrieve a list of DNS records in a zone
   *
   * @param string $zoneName name of the DNS zone
   * @param string $type     optional record type (A, AAAA, etc.)
   *
   * @return array
   */
  public function getRecords( string $zoneName, string $type = '' ): array;

  /**
   * Retrieve all records in a zone
   *
   * @param string $zoneName
   *
   * @return array
   */
  public function getZone( string $zoneName ): array;

  /**
   * Retrieve all zones
   *
   * @return array
   */
  public function getZones(): array;
}
