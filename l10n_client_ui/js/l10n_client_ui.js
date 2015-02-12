/**
 * @file
 * Attaches behaviors for the Localization client toolbar tab.
 */

(function ($, Drupal, document) {

  "use strict";

  /**
   * Attaches the toolbar behavior.
   */
  Drupal.behaviors.l10n_client_ui = {
    attach: function (context) {
      $('body').once('l10n_client_ui', function () {
        $('#toolbar-tab-l10n_client_ui').click(function () {
          Drupal.l10n_client_ui.buildUi();
          Drupal.l10n_client_ui.toggle(true);
          Drupal.dialog(
              $('.l10n_client_ui--container').get(0),
              {
                title: Drupal.t('Translate interface'),
                buttons: [
                  {
                    text: Drupal.t('Close'),
                    click: function () {
                      $(this).dialog("close");
                      Drupal.l10n_client_ui.toggle(false);
                    }
                  }
                ],
                width: '50%',
                close: function () {
                  Drupal.l10n_client_ui.toggle(false);
                }
              }
          ).showModal();
        });
      });
    }
  }

  Drupal.l10n_client_ui = Drupal.l10n_client_ui || {};

  Drupal.l10n_client_ui.toggle = function(isActive) {
    $('#toolbar-tab-l10n_client_ui button').toggleClass('active', isActive).prop('aria-pressed', isActive);
  }

  /**
   * Build the list of strings for the translation table.
   */
  Drupal.l10n_client_ui.buildUi = function() {
    if ($('.l10n_client_ui--container table tr').length <= 1) {
      var strings = drupalSettings.l10n_client_ui;
      for (var langcode in strings) {
        for (var context in strings[langcode]) {
          for (var string in strings[langcode][context]) {
            var row = $(document.createElement('tr')).
                addClass('l10n_client_ui--language--' + langcode).
                addClass('l10n_client_ui--status--' + (strings[langcode][context][string] == false ? 'un' : '') + 'translated');
            row.append($(document.createElement('td')).text(string));
            var input = $(document.createElement('textarea')).
                    attr('rows', 1).
                    text(strings[langcode][context][string]);
            row.append($(document.createElement('td')).append(input));
            row.append($(document.createElement('td')).text('X'));
            row.append($(document.createElement('td')).text('X'));
            $('.l10n_client_ui--container table').append(row);
          }
        }
      }
    }
  }

})(jQuery, Drupal, document);
