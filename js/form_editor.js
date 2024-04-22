var GFPipeSettings = function() {

	var self = this,
	    $    = jQuery;

	self.init = function() {

		// Define needed elements.
		self.$elem = {
			settingsFields: {
				width:           $( '#piperecorder_width' ),
				height:          $( '#piperecorder_height' ),
				recording_time:  $( '#piperecorder_recording_time' ),
				bottom_menu:     $( '#piperecorder_bottom_menu' ),
				autosave:        $( '#piperecorder_autosave' ),
				audio_only:      $( '#piperecorder_audio_only' ),
				mirror:          $( '#piperecorder_mirror' ),
				environment:     $( '#piperecorder_environment' ),
				resolution:      {
					all:    $( 'input[name="piperecorder_resolution"]' ),
					'240p': $( '#piperecorder_resolution_240p' ),
					'300p': $( '#piperecorder_resolution_300p' ),
					'480p': $( '#piperecorder_resolution_480p' ),
					'720p': $( '#piperecorder_resolution_720p' ),
				}
			}
		};

		self.bindFieldAdded();

		self.bindFieldLoad();

		self.bindFieldEvents();

		gform.addFilter( 'gform_form_editor_can_field_be_added', self.canFieldBeAdded );

	}

	/**
	 * Bind field added event.
	 *
	 * @since 1.0
	 */
	self.bindFieldAdded = function() {

		jQuery( document ).bind( 'gform_field_added', function( event, form, field ) {

			// If this is not a Pipe field, return.
			if ( 'pipe_recorder' !== field.type ) {
				return;
			}

			// Add default field settings.
			field.pipe = {
				resolution:      '300p',
				width:           400,
				height:          300,
				recording_time:  120,
				bottom_menu:     true,
				autosave:        true,
				mirror:          false,
				audio_only:      false,
				environment:     self.$elem.settingsFields.environment.find( 'option' ).eq( 0 ).attr( 'value' ),
			};

		} );

	}

	/**
	 * Bind field settings events.
	 *
	 * @since 1.0
	 */
	self.bindFieldEvents = function() {

		self.$elem.settingsFields.resolution.all.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.resolution = e.target.value;

		} );

		self.$elem.settingsFields.width.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.width = e.target.value;

		} );

		self.$elem.settingsFields.height.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.height = e.target.value;

		} );

		self.$elem.settingsFields.recording_time.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.recording_time = e.target.value;

		} );

		self.$elem.settingsFields.bottom_menu.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.bottom_menu = e.target.checked;

		} );

		self.$elem.settingsFields.autosave.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.autosave = e.target.checked;

		} );

		self.$elem.settingsFields.audio_only.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.audio_only = e.target.checked;

		} );

		self.$elem.settingsFields.mirror.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.mirror = e.target.checked;

		} );

		self.$elem.settingsFields.environment.on( 'change', function( e ) {

			// Get selected field.
			var field = self.getSelectedField();

			// Update field property.
			field.pipe.environment = e.target.value;

		} );

	}

	/**
	 * Bind field settings initialize functions:
	 *	- Set default field settings
	 *	- Load current field settings
	 *
	 * @since 1.0
	 */
	self.bindFieldLoad = function() {

		$( document ).bind( 'gform_load_field_settings', function( event, field ) {

			// Get selected field.
			var field = self.getSelectedField( field );

			// If this is not a Pipe Recorder field, exit.
			if ( 'pipe_recorder' !== field.type ) {
				return;
			}

			// Set settings fields values.
			self.$elem.settingsFields.resolution[ field.pipe.resolution ].prop( 'checked', true );
			self.$elem.settingsFields.width.val( field.pipe.width );
			self.$elem.settingsFields.height.val( field.pipe.height );
			self.$elem.settingsFields.recording_time.val( field.pipe.recording_time );
			self.$elem.settingsFields.environment.val( field.pipe.environment );

			self.$elem.settingsFields.bottom_menu.prop( 'checked', field.pipe.bottom_menu );
			self.$elem.settingsFields.autosave.prop( 'checked', field.pipe.autosave );
			self.$elem.settingsFields.audio_only.prop( 'checked', field.pipe.audio_only );
			self.$elem.settingsFields.mirror.prop( 'checked', field.pipe.mirror );

		} );

	}

	/**
	 * Determine if Pipe Recorder field can be added to form.
	 *
	 * @since 1.0
	 */
	self.canFieldBeAdded = function( canBeAdded, type ) {

		// If this is not a Pipe Recorder field, return.
		if ( 'pipe_recorder' !== type ) {
			return canBeAdded;
		}

		// If form already has a Pipe Recorder field, prevent field from being added.
		if ( GetFieldsByType( ["pipe_recorder"] ).length > 0 ) {
			alert( gravityformspipe_form_editor_strings.cannot_add );
			return false;
		}

		return true;

	}





	// # HELPER METHODS ------------------------------------------------------------------------------------------------

	/**
	 * Get a specific form field.
	 *
	 * @since 1.0
	 *
	 * @param int fieldId Field ID.
	 *
	 * @return object|null
	 */
	self.getField = function( fieldId ) {

		// If we cannot get the form object, return false.
		if ( ! form ) {
			return null;
		}

		// Loop through the form fields.
		for ( var i = 0; i < form.fields.length; i++ ) {

			// If this is not the target field, skip it.
			if ( fieldId == form.fields[ i ].id ) {
				return form.fields[ i ];
			}

		}

		return null;

	}

	/**
	 * Retrieve currently selected field in form editor.
	 *
	 * @since 1.0
	 *
	 * @param object field Currently selected field.
	 *
	 * @return object
	 */
	self.getSelectedField = function( field ) {

		// Get selected field.
		var field = field == null ? GetSelectedField() : field;

		// Initialize field properties, if not defined.
		if ( ! field.pipe && field.type === 'pipe_recorder' ) {

			field.pipe = {
				resolution:      '300p',
				width:           400,
				height:          300,
				recording_time:  120,
				bottom_menu:     true,
				autosave:        true,
				mirror:          false,
				audio_only:      false,
				environment:     self.$elem.settingsFields.environment.find( 'option' ).eq( 0 ).attr( 'value' ),
			};

		}

		return field;

	}

	self.init();

}

jQuery( document ).ready( function() {
	window.GFPipeSettings = new GFPipeSettings();
} );
