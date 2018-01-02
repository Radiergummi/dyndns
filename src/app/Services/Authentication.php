<?php

namespace Radiergummi\DynDns\Services;

use RuntimeException;
use function bin2hex;
use function hex2bin;
use function openssl_decrypt;
use function openssl_encrypt;

/**
 * Authentication service
 * Provides encryption and decryption of API tokens for Cloudflare.
 * The encrypt/decrypt methods are largely taken from the link below.
 *
 * @link    https://stackoverflow.com/a/46872528/2532203
 * @package Radiergummi\DynDns
 */
class Authentication {

  /**
   * Holds the default secret for the application. This ensures the app won't work
   * if the user did not specify their own.
   */
  public const    DEFAULT_SECRET = '__DEFAULT__';

  /**
   * Holds the encryption algorithm to use
   */
  protected const ENCRYPTION_ALGORITHM = 'AES-256-CBC';

  /**
   * Holds the hash algorithm to use
   */
  protected const HASHING_ALGORITHM = 'sha256';

  /**
   * Holds the application encryption secret
   *
   * @var string
   */
  protected $secret;

  /**
   * Authentication constructor
   *
   * @param string $secret application encryption secret
   *
   * @throws \RuntimeException
   */
  public function __construct( string $secret ) {

    // check if the secret has been changed and stop execution otherwise
    if ( $secret === Authentication::DEFAULT_SECRET ) {
      throw new RuntimeException( 'Application secret has not been changed! Please specify a random value to use the API.' );
    }

    $this->secret = $secret;
  }

  /**
   * Decrypts a password using the app secret.
   * Passwords are essentially assembled from <IV value (16)><Hash value (32)><Cipher text>
   *
   * @param string $password hex representation of the cipher text
   *
   * @return string UTF-8 string containing the plain text password
   */
  public function decrypt( string $password ): string {

    // we'll need the binary cipher
    $binaryPassword = hex2bin( $password );
    $iv             = substr( $binaryPassword, 0, 16 );
    $hash           = substr( $binaryPassword, 16, 32 );
    $cipherText     = substr( $binaryPassword, 48 );
    $key            = hash( Authentication::HASHING_ALGORITHM, $this->secret, true );

    // if the HMAC hash doesn't match the hash string, something has gone wrong
    if ( hash_hmac( Authentication::HASHING_ALGORITHM, $cipherText, $key, true ) !== $hash ) {
      return '';
    }

    return openssl_decrypt(
        $cipherText,
        Authentication::ENCRYPTION_ALGORITHM,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
  }

  /**
   * Encrypts a password using the app secret. This returns a hex representation of the binary cipher text
   *
   * @param string $password plain text password to encrypt
   *
   * @return string hex representation of the binary cipher text
   */
  public function encrypt( string $password ): string {
    $key = hash( Authentication::HASHING_ALGORITHM, $this->secret, true );
    $iv  = openssl_random_pseudo_bytes( 16 );

    $cipherText = openssl_encrypt(
        $password,
        Authentication::ENCRYPTION_ALGORITHM,
        $key,
        OPENSSL_RAW_DATA,
        $iv
    );
    $hash       = hash_hmac( Authentication::HASHING_ALGORITHM, $cipherText, $key, true );

    return bin2hex( $iv . $hash . $cipherText );
  }
}
