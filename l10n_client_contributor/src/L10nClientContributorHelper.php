<?php declare(strict_types = 1);

namespace Drupal\l10n_client_contributor;

use Psr\Http\Client\ClientInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Psr\Http\Message\ResponseInterface;

/**
 * Provides helper services for the Localization Client Contributor module.
 */
final class L10nClientContributorHelper implements L10nClientContributorHelperInterface {

  use StringTranslationTrait;

  protected ImmutableConfig $config;

  /**
   * Logger channel.
   *
   * @var \Drupal\Core\Logger\LoggerChannelInterface
   */
  protected LoggerChannelInterface $logger;

  /**
   * Constructs a L10nClientContributorHelper object.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LoggerChannelFactory $logger_factory,
    protected readonly ClientInterface $httpClient,
  ) {
    $this->config = $config_factory->get('l10n_client_contributor.settings');
    $this->logger = $logger_factory->get('l10n_client_contributor');
  }

  /**
   * Sends translation to the localization server using basic authentication.
   *
   * @param string $langcode
   *   The language code of a translated string.
   * @param string $source
   *   The translatable string.
   * @param string $translation
   *   The translated string.
   * @param string $context
   *   The context of the source string.
   *
   * @return array
   *   The result of the send request.
   *   [response code/FALSE, message]
   */
  public function sendTranslation($langcode, $source, $translation, $context = ''): array {
    $server_url = $this->config->get('server');
    $username = $this->config->get('basic_auth_user');
    $password = $this->config->get('basic_auth_pass');

    try {
      $response = $this->httpClient->post($server_url . 'api/l10n-server-contributor', [
        'headers' => [
          'Content-Type' => 'application/json',
          'Authorization' => 'Basic ' . base64_encode("$username:$password"),
        ],
        'json' => [
          'source' => $source,
          'translation' => $translation,
          'langcode' => $langcode,
          'context' => $context,
        ],
      ]);
    }
    catch (\Exception $e) {
      $message = $e->getMessage();
      $this->logger->error($message);
      return [FALSE, $message];
    }

    if ($response instanceof ResponseInterface) {
      if ($response->getStatusCode() == 201) {
        $message = $this->t('Translation sent and accepted by @server.', ['@server' => $server_url]);
        $this->logger->notice('Translation sent and accepted by @server.', ['@server' => $server_url]);
      }
      else {
        $message = $this->t('Translation rejected by @server. Reason: %reason', [
          '%reason' => $response->getReasonPhrase(),
          '@server' => $server_url
        ]);
        $this->logger->error('Translation rejected by @server. Reason: %reason', [
          '%reason' => $response->getReasonPhrase(),
          '@server' => $server_url
        ]);
      }
      return [$response->getStatusCode(), $message];
    }
    else {
      $message = $this->t('The connection with @server failed.', ['@server' => $server_url]);
      return [FALSE, $message];
    }
  }

}
