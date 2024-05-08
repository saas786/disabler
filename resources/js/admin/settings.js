(function () {
  jQuery(document).ready(($) => {
    function toggleFields(selectedValues, fieldSelector) {
      const events = $(fieldSelector).data('events');
      const mergedActions = {};

      if (!Array.isArray(selectedValues)) {
        selectedValues = [selectedValues];
      }

      // Merge actions for all selected values
      selectedValues.forEach((selectedValue) => {
        if (events[selectedValue]) {
          Object.entries(events[selectedValue]).forEach(([action, targetSelectors]) => {
            if (!mergedActions[action]) {
              mergedActions[action] = [];
            }
            mergedActions[action] = mergedActions[action].concat(targetSelectors);
          });
        }
      });

      // Apply merged actions
      Object.entries(mergedActions).forEach(([action, targetSelectors]) => {
        const targets = Array.isArray(targetSelectors) ? targetSelectors : [targetSelectors];
        targets.forEach((targetSelector) => {
          if (action === 'show') {
            $(targetSelector).show();
          }
          else if (action === 'hide') {
            $(targetSelector).hide();
          }
        });
      });
    }

    function setupListeners(fieldSelector) {
      const fieldType = $(fieldSelector).prop('tagName').toLowerCase(); // Use 'prop' to get the tag name

      switch (fieldType) {
        case 'input':
          const inputType = $(fieldSelector).attr('type');
          switch (inputType) {
            case 'radio':
              $(fieldSelector).on('change', function () {
                toggleFields($(this).val(), fieldSelector);
              }).filter(':checked').trigger('change');
              break;
            case 'checkbox':
              $(fieldSelector).on('change', function () {
                toggleFields(this.checked.toString(), fieldSelector);
              }).trigger('change');
              break;
            default:
              console.error('Unsupported input type:', inputType);
          }
          break;
        case 'select':
          const isMultiple = $(fieldSelector).prop('multiple');
          $(fieldSelector).on('change', function () {
            const selectedValues = isMultiple ? $(this).val() : [$(this).val()];
            toggleFields(selectedValues, fieldSelector);
          }).trigger('change');
          break;
          // Add more cases as needed for other field types
        default:
          console.error('Unsupported field type:', fieldType);
      }
    }

    // Set up listeners for each main field on the page.
    $('.hbp-disabler-form-wrap form .hbp-disabler-form-field').each(function () {
      setupListeners(this);
    });
  });
})();
