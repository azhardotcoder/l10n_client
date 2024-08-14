<?php declare(strict_types = 1);

namespace Drupal\l10n_client_contributor;

/**
 * Provides an interface for Localization Client Contributor helper service.
 */
interface L10nClientContributorHelperInterface {

  /**
   * Sends translation to the server.
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
  public function sendTranslation($langcode, $source, $translation, $context = ''): array;

}
