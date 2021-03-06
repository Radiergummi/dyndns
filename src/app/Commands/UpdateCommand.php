<?php

namespace Radiergummi\DynDns\Commands;

use Radiergummi\DynDns\Command;
use Radiergummi\DynDns\Services\CloudflareService;
use Radiergummi\DynDns\Services\DynDnsService;
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
class UpdateCommand extends Command {

  /**
   * Hostname argument
   */
  protected const ARGUMENT_HOSTNAME = 'hostname';

  /**
   * Hostname argument description
   */
  protected const ARGUMENT_HOSTNAME_DESCRIPTION = 'Hostname of the record to update';

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
   * IPv4 address option
   */
  protected const OPTION_IPV4_ADDRESS = 'ipv4';

  /**
   * IPv4 address option description
   */
  protected const OPTION_IPV4_ADDRESS_DESCRIPTION = 'New IPv4 address';

  /**
   * IPv6 address option
   */
  protected const OPTION_IPV6_ADDRESS = 'ipv6';

  /**
   * IPv6 address option description
   */
  protected const OPTION_IPV6_ADDRESS_DESCRIPTION = 'New IPv6 address';

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

  /**
   * @return void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   */
  protected function configure() {
    parent::configure();

    $this->setDescription( UpdateCommand::DESCRIPTION );

    // add the zone argument
    $this->addArgument(
        UpdateCommand::ARGUMENT_ZONE,
        InputArgument::REQUIRED,
        UpdateCommand::ARGUMENT_ZONE_DESCRIPTION
    );

    // add the hostname argument
    $this->addArgument(
        UpdateCommand::ARGUMENT_HOSTNAME,
        InputArgument::REQUIRED,
        UpdateCommand::ARGUMENT_HOSTNAME_DESCRIPTION
    );

    // add the username option
    $this->addOption(
        UpdateCommand::OPTION_USERNAME,
        substr( UpdateCommand::OPTION_USERNAME, 0, 1 ),
        InputOption::VALUE_REQUIRED,
        UpdateCommand::OPTION_USERNAME_DESCRIPTION
    );

    // add the password option
    $this->addOption(
        UpdateCommand::OPTION_PASSWORD,
        substr( UpdateCommand::OPTION_PASSWORD, 0, 1 ),
        InputOption::VALUE_REQUIRED,
        UpdateCommand::OPTION_PASSWORD_DESCRIPTION
    );

    $this->addOption(
        UpdateCommand::OPTION_IPV4_ADDRESS,
        4,
        InputOption::VALUE_OPTIONAL,
        UpdateCommand::OPTION_IPV4_ADDRESS_DESCRIPTION
    );

    $this->addOption(
        UpdateCommand::OPTION_IPV6_ADDRESS,
        6,
        InputOption::VALUE_OPTIONAL,
        UpdateCommand::OPTION_IPV6_ADDRESS_DESCRIPTION
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
    $username    = $input->getOption( UpdateCommand::OPTION_USERNAME );
    $password    = $input->getOption( UpdateCommand::OPTION_PASSWORD );
    $zone        = $input->getArgument( UpdateCommand::ARGUMENT_ZONE );
    $hostname    = $input->getArgument( UpdateCommand::ARGUMENT_HOSTNAME );
    $ipv4Address = $input->getOption( UpdateCommand::OPTION_IPV4_ADDRESS ) ?? null;
    $ipv6Address = $input->getOption( UpdateCommand::OPTION_IPV6_ADDRESS ) ?? null;

    $this->getKernel()->registerServiceArguments( CloudflareService::class, [
        $username,
        $password
    ] );

    /** @var \Radiergummi\DynDns\Services\DynDnsService $dynDns */
    $dynDns = $this->getKernel()->getService( DynDnsService::class );

    try {
      $dynDns->update( $zone, $hostname, $ipv4Address, $ipv6Address );
    }
    catch ( Throwable $exception ) {
      $this->getKernel()->logError( $exception->getMessage() );
      throw $exception;
    }

    $this->getKernel()->logInfo( UpdateCommand::MESSAGE_UPDATE_SUCCEEDED, [
        'hostname' => $hostname,
        'ipv4'     => $ipv4Address,
        'ipv6'     => $ipv6Address
    ] );
  }
}
