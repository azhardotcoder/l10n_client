<?php

/**
 * @file
 * Contains \Drupal\l10n_client_ui\Form\TranslationForm
 */

namespace Drupal\l10n_client_ui\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;


/**
 * Settings form for the localization client user interface module.
 */
class TranslationForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'l10n_client_ui_translation_form';
  }

  public function setValues($languages, $strings) {
    $this->languages = $languages;
    $this->strings = $strings;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['filters'] = array(
      '#type' => 'container',
    );
    $form['filters']['language'] = array(
      '#title' => $this->t('Language'),
      '#type' => 'select',
      '#options' => $this->languages,
    );
    $form['filters']['type'] = array(
      '#title' => $this->t('Find and translate'),
      '#type' => 'select',
      '#options' => array(
        'untranslated' => $this->t('Untranslated strings'),
        'translated' => $this->t('Translated strings'),
      ),
    );
    $form['filters']['search'] = array(
      '#type' => 'search',
      '#placeholder' => $this->t('Search')
    );

    $form['list'] = array(
      '#type' => 'container',
    );
    $form['list']['table'] = array(
      '#type' => 'table',
      '#header' => array($this->t('Source'), $this->t('Translation'), $this->t('Save'), $this->t('Skip'))
    );
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    parent::submitForm($form, $form_state);
  }
}
