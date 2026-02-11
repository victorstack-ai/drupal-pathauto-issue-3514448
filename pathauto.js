(($, Drupal) => {
  Drupal.behaviors.pathFieldsetSummaries = {
    attach(context) {
      $(context)
        .find('.path-form')
        .drupalSetSummary((pathForm) => {
          const automaticInput = pathForm.querySelector(
            '.js-form-item-path-0-pathauto input',
          );

          if (automaticInput && automaticInput.checked) {
            return Drupal.t('Automatic alias');
          }

          const pathInput = pathForm.querySelector(
            '.js-form-item-path-0-alias input',
          );

          if (pathInput && pathInput.value) {
            return Drupal.t('Alias: @alias', { '@alias': pathInput.value });
          }

          return Drupal.t('No alias');
        });
    },
  };
})(jQuery, Drupal);
