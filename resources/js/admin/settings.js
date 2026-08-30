( function () {
  jQuery( document ).ready( ( $ ) => {
    /**
     * Apply the show/hide rules a control declares for its current value.
     *
     * Rules arrive already resolved to selectors: the config names control
     * keys, and the server turns each into the class that control's row
     * carries. Nothing here builds a selector, so the two cannot drift.
     */
    function toggleFields( selectedValues, events ) {
      const mergedActions = {};

      if ( !Array.isArray( selectedValues ) ) {
        selectedValues = [ selectedValues ];
      }

      // Merge actions across every selected value, so a multiple select
      // applies all of its selections rather than only the last one.
      selectedValues.forEach( ( selectedValue ) => {
        if ( events[selectedValue] ) {
          Object.entries( events[selectedValue] ).forEach( ( [ action, targetSelectors ] ) => {
            if ( !mergedActions[action] ) {
              mergedActions[action] = [];
            }
            mergedActions[action] = mergedActions[action].concat( targetSelectors );
          } );
        }
      } );

      Object.entries( mergedActions ).forEach( ( [ action, targetSelectors ] ) => {
        const targets = Array.isArray( targetSelectors ) ? targetSelectors : [ targetSelectors ];
        targets.forEach( ( targetSelector ) => {
          const $target = $( targetSelector );
          if ( action === 'show' ) {
            $target.show();
          } else if ( action === 'hide' ) {
            $target.hide();
          }
        } );
      } );
    }

    function setupListeners( field, events ) {
      const $field = $( field );
      const tag = $field.prop( 'tagName' ).toLowerCase();

      switch ( tag ) {
        case 'input':
          const inputType = $field.attr( 'type' );
          switch ( inputType ) {
            case 'radio':
              $field.on( 'change', function () {
                toggleFields( $( this ).val(), events );
              } ).filter( ':checked' ).trigger( 'change' );
              break;
            case 'checkbox':
              $field.on( 'change', function () {
                toggleFields( this.checked.toString(), events );
              } ).trigger( 'change' );
              break;
            default:
              console.info( 'Unsupported input type:', inputType );
          }
          break;
        case 'select':
          const isMultiple = $field.prop( 'multiple' );
          $field.on( 'change', function () {
            toggleFields( isMultiple ? $( this ).val() : [ $( this ).val() ], events );
          } ).trigger( 'change' );
          break;
        default:
          console.info( 'Unsupported field type:', tag );
      }
    }

    // Every control declaring rules is wrapped and carries them as JSON.
    $( '.hbp-disabler-form-wrap form [data-hbp-events]' ).each( function () {
      const events = $( this ).data( 'hbpEvents' );

      if ( !events ) {
        return;
      }

      // The wrapper holds exactly one control; a companion hidden input for
      // the empty case is skipped, since it never changes.
      $( this ).find( 'input:not([type="hidden"]), select' ).each( function () {
        setupListeners( this, events );
      } );
    } );
  } );
} )();
