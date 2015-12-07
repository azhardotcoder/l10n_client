<?php

/**
 * @file
 * Contains \Drupal\l10n_client_contributor\Plugin\Field\FieldWidget\ClientApiKeyDefaultWidget.
 */

namespace Drupal\l10n_client_contributor\Plugin\Field\FieldWidget;

use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'l10n_client_contributor_key_widget' widget.
 *
 * @FieldWidget(
 *   id = "l10n_client_contributor_key_widget",
 *   label = @Translation("Localization client contributor key"),
 *   field_types = {
 *     "l10n_client_contributor_key"
 *   }
 * )
 */
class ClientApiKeyDefaultWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $config = \Drupal::configFactory()->getEditable('l10n_client_contributor.settings');
    if (!$config->get('use_server')) {
      // Should not expose a widget if we are not using a server.
      return array();
    }

    // @todo add account based local token
    // @todo add permission checking based on account
    $server_root = $config->get('server');
    $server_api_link = $server_root . '?q=translate/remote/userkey/@todo@';

    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $element['value']['#title'] = $this->t('Your API key for @server', array('@server' => $server_root));
    $element['value']['#description'] = $this->t('This is a unique key that will allow you to send translations to the remote server. To get your API key go to <a href=":server">:server</a>.', array(':server' => $server_api_link));

    return $element;
  }
}
