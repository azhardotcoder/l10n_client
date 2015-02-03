<?php

namespace Drupal\l10n_client\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Class L10nClientSettingsForm
 *
 * @package Drupal\l10n_client
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'l10n_client_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['l10n_client.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('l10n_client.settings');

    $form['disabled_paths'] = array(
      '#title'         => t('Disable on-page translation on the following system paths'),
      '#type'          => 'textarea',
      '#description'   => t('One per line. Wildcard-enabled. Examples: system/ajax, admin*'),
      '#default_value' => $config->get('disabled_paths'),
    );
    $form['use_server'] = array(
      '#title'         => t('Enable sharing translations with server'),
      '#type'          => 'checkbox',
      '#default_value' => $config->get('use_server'),
    );
    $form['server'] = array(
      '#title'         => t('Address of localization server to use'),
      '#type'          => 'textfield',
      '#description'   => t('Each translation submission will also be submitted to this server. We suggest you enter <a href="@localize">http://localize.drupal.org</a> to share with the greater Drupal community. Make sure you set up an API-key in the user account settings for each user that will participate in the translations.', array('@localize' => 'http://localize.drupal.org')),
      '#default_value' => $config->get('server'),
    );
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // Verify connection with the server if enabled.
    if ($form_state->getValue('use_server')) {
      if (!$form_state->isValueEmpty('server')) {
        // Try to invoke the remote string submission with a test request.
        $response = xmlrpc($form_state->getValue('server') . '/xmlrpc.php', array('l10n.server.test' => array('2.0')));
        if ($response && !empty($response['name']) && !empty($response['version'])) {
          if (empty($response['supported']) || !$response['supported']) {
            $form_state->setErrorByName('l10n_client_server', t('The given server could not handle the v2.0 remote submission API.'));
          }
          else {
            drupal_set_message(t('Verified that the specified server can handle remote string submissions. Supported languages: %languages.', array('%languages' => $response['languages'])));
          }
        }
        else {
          $form_state->setErrorByName('l10n_client_server', t('Invalid localization server address specified. Make sure you specified the right server address.'));
        }
      }
      else {
        $form_state->setErrorByName('l10n_client_server', t('You should provide a server address, such as http://localize.drupal.org'));
      }
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('l10n_client.settings')
         ->set('disabled_paths', $form_state->getValue('disabled_paths'))
         ->set('use_server', $form_state->getValue('use_server'))
         ->set('server', $form_state->getValue('server'))->save();

    parent::submitForm($form, $form_state);
  }
}
