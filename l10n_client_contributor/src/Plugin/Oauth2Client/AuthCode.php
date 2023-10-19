<?php

declare(strict_types = 1);

namespace Drupal\l10n_client_contributor\Plugin\Oauth2Client;

use Drupal\Core\Url;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Drupal\oauth2_client\Plugin\Oauth2Client\TempStoreTokenStorage;
use Drupal\oauth2_client\Plugin\Oauth2Client\Oauth2ClientPluginBase;
use Drupal\oauth2_client\Plugin\Oauth2Client\Oauth2ClientPluginRedirectInterface;

/**
 * Auth code example.
 *
 * @Oauth2Client(
 *   id = "l10n_authcode",
 *   name = @Translation("Get L10N authcode"),
 *   grant_type = "authorization_code",
 *   authorization_uri = "https://localize.drupal.org/oauth/authorize",
 *   token_uri = "https://localize.drupal.org/oauth/token",
 *   scopes = {"email"},
 *   success_message = TRUE
 * )
 */
class AuthCode extends Oauth2ClientPluginBase implements Oauth2ClientPluginRedirectInterface {

  public function getPostCaptureRedirect(): RedirectResponse {
    $url = Url::fromRoute('locale.translate_page');
    return new RedirectResponse($url->toString(TRUE)->getGeneratedUrl());
  }


  use TempStoreTokenStorage;

}
