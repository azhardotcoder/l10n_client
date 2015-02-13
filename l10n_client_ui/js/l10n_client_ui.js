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
        $('.l10n-client-ui-translation-form .form-item-language select').change(function() {
          Drupal.l10n_client_ui.displayStats();
          Drupal.l10n_client_ui.runFilters();
        });
        $('.l10n-client-ui-translation-form .form-item-type select').change(function() {
          Drupal.l10n_client_ui.runFilters();
        });
        $('.l10n-client-ui-translation-form .form-item-search input').keyup(function() {
          Drupal.l10n_client_ui.runFilters();
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
      var rows = document.createElement('tbody');
      //var sources = [];
      for (var langcode in strings) {
        for (var context in strings[langcode]) {
          for (var string in strings[langcode][context]) {
            var translated = strings[langcode][context][string][0] != false;
            var row = $(document.createElement('tr'));
            row.append($(document.createElement('td')).addClass('l10n_client_ui--source-string').text(string));
            var input = $(document.createElement('textarea')).
                    attr('rows', 1).
                    text(translated ? strings[langcode][context][string][0] : '').
                    data('l10n-client-ui-source', string).
                    data('l10n-client-ui-langcode', langcode).
                    data('l10n-client-ui-context', context).
                    data('l10n-client-ui-translated', translated).
                    data('l10n-client-ui-translation', translated ? strings[langcode][context][string][0] : '');
            row.append($(document.createElement('td')).append(input));
            row.append($(document.createElement('td')).text('X').click(Drupal.l10n_client_ui.saveTranslation));
            row.append($(document.createElement('td')).text('X').click(
                function() {
                  $(this).closest('tr').fadeOut();
                }
            ));
            $(rows).append(row);
            /*if (!translated) {
              sources.push(string);
            }*/
          }
        }
      }
      /*sources.sort(function(a, b){
        return b.length - a.length;
      });
      for (var index in sources) {
        Drupal.l10n_client_ui.findClosest(index, sources[index], $('div')).addClass('found').addClass('found-' + index.toString());
      }*/
      $('.l10n_client_ui--container table').append(rows);
      // Initialize the interface with statistics and filter based on defaults.
      Drupal.l10n_client_ui.displayStats();
      Drupal.l10n_client_ui.runFilters();
    }
  }

  /*Drupal.l10n_client_ui.findClosest = function(index, text, elements) {
    var children = $(elements).find('div:contains("' + text + '")');
    if (children.length) {
      // If we still found the same text in some children, keep looking.
      return Drupal.l10n_client_ui.findClosest(index, text, children);
    }
    else {
      console.log(text);
      console.log(elements.length);
      // Otherwise, we found the closest element.
      return elements;
    }
  }*/

  /**
   * Execute filters on the list of translatable strings.
   */
  Drupal.l10n_client_ui.runFilters = function() {
    var langcode = $('.l10n-client-ui-translation-form .form-item-language select').val();
    var type = $('.l10n-client-ui-translation-form .form-item-type select').val();
    var search = $('.l10n-client-ui-translation-form .form-item-search input').val();

    $.each($('.l10n_client_ui--container table tr textarea'), function(i, el) {
      var visible = false;
      if ($(el).data('l10n-client-ui-langcode') == langcode && $(el).data('l10n-client-ui-translated').toString() == type) {
        visible = true;
        if (search.length) {
          var source = $(el).data('l10n-client-ui-source');
          var translation = $(el).data('l10n-client-ui-translation');
          visible = source.indexOf(search) >= 0 || translation.indexOf(search) >= 0;
        }
      }
      $(el).closest('tr').toggle(visible);
    });
  }

  /**
   * Save a new translation and update the interface.
   */
  Drupal.l10n_client_ui.saveTranslation = function() {
    var translation = $(this).closest('tr').find('textarea');
    if (translation.val().length) {
      drupalSettings.l10n_client_ui[translation.data('l10n-client-ui-langcode')][translation.data('l10n-client-ui-context')][translation.data('l10n-client-ui-source')] = translation.val();
      Drupal.l10n_client_ui.displayStats();
      $(this).data('l10n-client-ui-translated', true).data('l10n-client-ui-translation', translation.val());
      $(this).closest('tr').fadeOut();
    }
  }

  /**
   * Display stats on the form about translation progress.
   */
  Drupal.l10n_client_ui.displayStats = function() {
    var stats = Drupal.l10n_client_ui.computeStats();
    var percent = Math.round(stats.all / 100 * stats.translated);
    $('.l10n-client-ui-translation-form .form-item-stats label').text(Drupal.t('@percent% translated', {'@percent' : percent}));
    $('.l10n_client_ui--stats-done').css('width', 2 * percent);
  }

  /**
   * Compute translation status for the currently selected language.
   */
  Drupal.l10n_client_ui.computeStats = function () {
    var langcode = $('.l10n-client-ui-translation-form .form-item-language select').val();
    var allCount = 0;
    var translatedCount = 0;
    var strings = drupalSettings.l10n_client_ui;
    for (var context in strings[langcode]) {
      for (var string in strings[langcode][context]) {
        if (strings[langcode][context][string][0] != false) {
          translatedCount++;
        }
        allCount++;
      }
    }
    return {'all' : allCount, 'translated': translatedCount};
  }

})(jQuery, Drupal, document);
