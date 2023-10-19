<?php declare(strict_types = 1);

namespace Drupal\l10n_client_contributor;

use Psr\Http\Client\ClientInterface;
use Drupal\Core\Config\ImmutableConfig;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Logger\LoggerChannelFactory;
use Drupal\Core\Logger\LoggerChannelInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\oauth2_client\Service\Oauth2ClientServiceInterface;

/**
 * @todo Add class description.
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
    protected readonly Oauth2ClientServiceInterface $oauth2ClientService,
    protected readonly ClientInterface $httpClient,
  ) {
    $this->config = $config_factory->get('l10n_client_contributor.settings');
    $this->logger = $logger_factory->get('l10n_client_contributor');
  }

  /**
   * {@inheritdoc}
   */
  public function isAuthenticated(): bool {
    $client = $this->oauth2ClientService->getClient('l10n_authcode');
    return (bool) $client->retrieveAccessToken();
  }

  /**
   * {@inheritdoc}
   */
  public function getAccessToken(): ?string {
    return (string) $this->oauth2ClientService->getAccessToken('l10n_authcode', NULL);
  }

  public function sendTranslation($langcode, $source, $translation, $context = ''): array {
    if (!$oauth_token = $this->getAccessToken()) {
      $message = $this->t('Failed to get access token.');
      $this->logger->error($message);
      return [FALSE, $message];
    }
    
    $server_url = $this->config->get('server');
    try {
    $response = $this->httpClient->post($server_url . 'api/l10n-server-contributor', [
      'headers' => [
        'Content-Type' => 'application/json',
        'Authorization' => 'Bearer ' . $oauth_token,
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
  
    // $response = xmlrpc(
    //   $server_url . '/xmlrpc.php',
    //   array(
    //     'l10n.submit.translation' => array(
    //       $langcode,
    //       $source,
    //       $translation,
    //       (int) $server_uid,
    //       $user_token,
    //       $signature,
    //     ),
    //   )
    // );
  
    if ($response) {
      if ($response->getStatusCode() == 201) {
        $message = $this->t('Translation sent and accepted by @server.', array('@server' => $server_url));
        $this->logger->notice('Translation sent and accepted by @server.', array('@server' => $server_url));
      }
      else {
        $message = $this->t('Translation rejected by @server. Reason: %reason', array('%reason' => $response->getContent(), '@server' => $server_url));
        \Drupal::logger('l10n_client_contributor')->error('Translation rejected by @server. Reason: %reason', array('%reason' => $response['reason'], '@server' => $server_url));
      }
      return array($response->getStatusCode(), $message);
    }
    else {
      // module_load_include('inc', 'xmlrpc', 'xmlrpc');
      $message = $this->t('The connection with @server failed with the following error: %error_code: %error_message.', array('%error_code' => xmlrpc_errno(), '%error_message' => xmlrpc_error_msg(), '@server' => $server_url));
      \Drupal::logger('l10n_client_contributor')->error('The connection with @server failed with the following error: %error_code: %error_message.', array('%error_code' => xmlrpc_errno(), '%error_message' => xmlrpc_error_msg(), '@server' => $server_url));
      return array(FALSE, $message);
    }
  }

}
