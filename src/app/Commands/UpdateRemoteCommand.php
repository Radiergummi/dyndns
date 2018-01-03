<?php

namespace Radiergummi\DynDns\Commands;

use GuzzleHttp\Client;
use Radiergummi\DynDns\Command;
use Radiergummi\DynDns\Services\AuthenticationService;
use Radiergummi\DynDns\Services\IpAddressService;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

/**
 * Update Command
 *
 * @package Radiergummi\DynDns\Commands
 */
class UpdateRemoteCommand extends Command {

  /**
   * Hostname argument
   */
  protected const ARGUMENT_HOSTNAME = 'hostname';

  /**
   * Hostname argument description
   */
  protected const ARGUMENT_HOSTNAME_DESCRIPTION = 'Hostname of the record to update';

  protected const ARGUMENT_REMOTE               = 'remote';

  protected const ARGUMENT_REMOTE_DESCRIPTION   = 'Remote DynDNS server to update';

  /**
   * Zone argument
   */
  protected const ARGUMENT_ZONE = 'zone';

  /**
   * Zone argument descriptions
   */
  protected const ARGUMENT_ZONE_DESCRIPTION = 'Zone containing the hostname record';

  /**
   * Command description
   */
  protected const DESCRIPTION = 'Updates the DynDNS record';

  /**
   * Success message for successful DNS record update attempts
   */
  protected const MESSAGE_UPDATE_SUCCEEDED = 'Updated DynDNS record for {hostname} successfully to "{ipv4}" (IPv4) / "{ipv6}" (IPv6)';

  /**
   * Password option
   */
  protected const OPTION_PASSWORD = 'password';

  /**
   * Password option description
   */
  protected const OPTION_PASSWORD_DESCRIPTION = 'Encrypted Cloudflare password';

  /**
   * Username option
   */
  protected const OPTION_USERNAME = 'username';

  /**
   * Username option description
   */
  protected const OPTION_USERNAME_DESCRIPTION = 'Cloudflare email address';

  protected const REMOTE_PROTOCOL             = 'https';

  /**
   * @return void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   */
  protected function configure() {
    parent::configure();

    $this->setDescription( UpdateRemoteCommand::DESCRIPTION );

    $this->addArgument(
        UpdateRemoteCommand::ARGUMENT_REMOTE,
        InputArgument::REQUIRED,
        UpdateRemoteCommand::ARGUMENT_REMOTE_DESCRIPTION
    );

    // add the zone argument
    $this->addArgument(
        UpdateRemoteCommand::ARGUMENT_ZONE,
        InputArgument::REQUIRED,
        UpdateRemoteCommand::ARGUMENT_ZONE_DESCRIPTION
    );

    // add the hostname argument
    $this->addArgument(
        UpdateRemoteCommand::ARGUMENT_HOSTNAME,
        InputArgument::REQUIRED,
        UpdateRemoteCommand::ARGUMENT_HOSTNAME_DESCRIPTION
    );

    // add the username option
    $this->addOption(
        UpdateRemoteCommand::OPTION_USERNAME,
        substr( UpdateRemoteCommand::OPTION_USERNAME, 0, 1 ),
        InputOption::VALUE_REQUIRED,
        UpdateRemoteCommand::OPTION_USERNAME_DESCRIPTION
    );

    // add the password option
    $this->addOption(
        UpdateRemoteCommand::OPTION_PASSWORD,
        substr( UpdateRemoteCommand::OPTION_PASSWORD, 0, 1 ),
        InputOption::VALUE_REQUIRED,
        UpdateRemoteCommand::OPTION_PASSWORD_DESCRIPTION
    );
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface   $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int|null|void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   * @throws \Throwable
   */
  public function execute( InputInterface $input, OutputInterface $output ) {

    /** @var \Radiergummi\DynDns\Services\IpAddressService $ipAddress */
    $ipAddress = $this->getKernel()->getService( IpAddressService::class );

    /** @var \Radiergummi\DynDns\Services\AuthenticationService $authentication */
    $authentication = $this->getKernel()->getService( AuthenticationService::class );
    $httpClient     = new Client();

    $username    = $input->getOption( UpdateRemoteCommand::OPTION_USERNAME );
    $password    = $input->getOption( UpdateRemoteCommand::OPTION_PASSWORD );
    $remote      = $input->getArgument( UpdateRemoteCommand::ARGUMENT_REMOTE );
    $zone        = $input->getArgument( UpdateRemoteCommand::ARGUMENT_ZONE );
    $hostname    = $input->getArgument( UpdateRemoteCommand::ARGUMENT_HOSTNAME );
    $ipv4Address = $ipAddress->getOwnIpAddress( IpAddressService::IPV4 );
    $ipv6Address = $ipAddress->getOwnIpAddress( IpAddressService::IPV6 );
    $userAgent   = $this->getKernel()->getConfig()->name . '/' . $this->getKernel()->getConfig()->version;

    try {
      $httpClient->put( sprintf( '%s://%s/%s/%s/update', [
          UpdateRemoteCommand::REMOTE_PROTOCOL,
          $remote,
          $zone,
          $hostname
      ] ), [
                            'auth'    => [
                                $username,
                                $authentication->encrypt( $password )
                            ],
                            'headers' => [
                                'User-Agent' => $userAgent,
                            ],
                            'query'   => [
                                'ipv4' => $ipv4Address,
                                'ipv6' => $ipv6Address
                            ]
                        ] );
    }
    catch ( Throwable $exception ) {
      $this->getKernel()->logError( $exception->getMessage() );
      throw $exception;
    }

    $this->getKernel()->logInfo( UpdateRemoteCommand::MESSAGE_UPDATE_SUCCEEDED, [
        'hostname' => $hostname,
        'ipv4'     => $ipv4Address,
        'ipv6'     => $ipv6Address
    ] );
  }
}
