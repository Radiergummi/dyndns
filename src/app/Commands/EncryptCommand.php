<?php

namespace Radiergummi\DynDns\Commands;

use Radiergummi\DynDns\Command;
use Radiergummi\DynDns\Services\Authentication;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Encrypt Command
 *
 * @package Radiergummi\DynDns\Commands
 */
class EncryptCommand extends Command {

  /**
   * Argument name for the plain text password
   */
  protected const ARGUMENT_PASSWORD = 'password';

  /**
   * Argument password description
   */
  protected const ARGUMENT_PASSWORD_DESCRIPTION = 'Password to encrypt';

  /**
   * Command description
   */
  protected const DESCRIPTION = 'Encrypts a password';

  /**
   * @return void
   * @throws \Symfony\Component\Console\Exception\InvalidArgumentException
   */
  protected function configure() {
    parent::configure();

    $this->setDescription( EncryptCommand::DESCRIPTION );
    $this->addArgument(
        EncryptCommand::ARGUMENT_PASSWORD,
        InputArgument::REQUIRED,
        EncryptCommand::ARGUMENT_PASSWORD_DESCRIPTION
    );
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
    $password          = trim( $input->getArgument( EncryptCommand::ARGUMENT_PASSWORD ) );
    $encryptedPassword = $authentication->encrypt( $password );

    $output->writeln( '<fg=red;options=bold>Encrypted password:</>' );

    // check for the quiet switch - this enables automation
    if ( $output->isQuiet() ) {
      echo $encryptedPassword;
    } else {
      $output->writeln( '<fg=white>' . $encryptedPassword . '</>' );
    }

    $output->writeln( '' );
    $output->writeln( [
                          'To execute any method on the public REST API, you will',
                          'need to use this password together with your Cloudflare ',
                          'username for the Basic Auth challenge.'
                      ] );
    $output->writeln( '' );
    $output->writeln( [
                          'The password has been encrypted using the secret provided ',
                          'by you. This serves as an additional measure of security, ',
                          'should your communication be compromised. Still, it is only',
                          'as secure as you keep this secret to yourself, used some ',
                          'long, random string and only provide the API via HTTPS.'
                      ] );
  }
}
