<?php

namespace Radiergummi\DynDns\Commands;

use Radiergummi\DynDns\Command;
use Radiergummi\DynDns\Services\Authentication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Decrypt Command
 *
 * @package Radiergummi\DynDns\Commands
 */
class DecryptCommand extends Command {

  /**
   * Argument name for the encrypted cipher
   */
  protected const ARGUMENT_CIPHER = 'cipher';

  /**
   * @return void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   */
  protected function configure() {
    parent::configure();

    $this->addArgument( DecryptCommand::ARGUMENT_CIPHER, InputArgument::REQUIRED );
  }

  /**
   * @param \Symfony\Component\Console\Input\InputInterface   $input
   * @param \Symfony\Component\Console\Output\OutputInterface $output
   *
   * @return int|null|void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   */
  public function execute( InputInterface $input, OutputInterface $output ) {
    $authentication    = new Authentication( $this->getKernel()->getSecret() );
    $cipher            = trim( $input->getArgument( DecryptCommand::ARGUMENT_CIPHER ) );
    $decryptedPassword = $authentication->decrypt( $cipher );

    $output->writeln( '<fg=red;options=bold>Decrypted password:</>' );

    // check for the quiet switch - this enables automation
    if ( $output->isQuiet() ) {
      echo $decryptedPassword;
    } else {
      $output->writeln( '<fg=white>' . $decryptedPassword . '</>' );
    }

    $output->writeln( '' );
    $output->writeln( [
                          'This is your actual Cloudflare password. Please be careful',
                          'and make sure you do not publish it anywhere.'
                      ] );
  }
}
