<?php declare(strict_types = 1);

namespace Drupal\l10n_client_contributor;

/**
 * @todo Add interface description.
 */
interface L10nClientContributorHelperInterface {

  /**
   * Check whether the current user already has a bearer token.
   *
   * @return boolean
   *   TRUE, if it is autheticated.
   */
  public function isAuthenticated(): bool;

  /**
   * Get the bearer token.
   *
   * @return string|null
   *   The bearer token
   */
  public function getAccessToken(): ?string;

  /**
 * Send translation to the server.
 *
 * @param string $langcode
 *   The language code of a translated string.
 * @param string $source
 *   The translatable string.
 * @param string $translation
 *   The translated string.
 * @param string $context.
 *   The context of the source string.
 *
 * @return array
 *    The result of the send request.
 *    [response code/FALSE, message]
 */
  public function sendTranslation($langcode, $source, $translation, $context = ''): array;

}
