<?php

if ( ! class_exists( 'GFForms' ) ) {
	die();
}

class GF_Field_Pipe_Recorder extends GF_Field {

	/**
	 * Field type.
	 *
	 * @since  1.0
	 * @access public
	 * @var    string $type Field type.
	 */
	public $type = 'pipe_recorder';

	/**
	 * Adds the field button to the specified group.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array $field_groups
	 *
	 * @return array
	 */
	public function add_button( $field_groups ) {

		// Verify button has not already been added for field.
		foreach ( $field_groups as $group ) {
			foreach ( $group['fields'] as $button ) {
				if ( isset( $button['data-type'] ) && $button['data-type'] == $this->type ) {
					return $field_groups;
				}
			}
		}

		// Get form edito button.
		$new_button = $this->get_form_editor_button();

		// Loop through field groups.
		foreach ( $field_groups as &$group ) {

			// If this is not the desired field group, skip it.
			if ( $group['name'] !== $new_button['group'] ) {
				continue;
			}

			// Add Pipe Recorder button.
			$group['fields'][] = array(
				'value'            => $new_button['text'],
				'class'            => 'button',
				'data-type'        => $this->type,
				'onclick'          => "StartAddField('{$this->type}');",
				'onkeypress'       => "StartAddField('{$this->type}');",
				'data-icon'        => empty( $new_button['icon'] ) ? $this->get_form_editor_field_icon() : $new_button['icon'],
				'data-description' => empty( $new_button['description'] ) ? $this->get_form_editor_field_description() : $new_button['description'],
			);

		}

		return $field_groups;

	}

	/**
	 * Returns the field inner markup.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array        $form  Form object.
	 * @param string|array $value Field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array   $entry Entry object currently being edited. Defaults to null.
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {

		$form_id         = $form['id'];
		$is_entry_detail = $this->is_entry_detail();
		$is_form_editor  = $this->is_form_editor();

		// If we are in the form editor, display a placeholder video recorder.
		if ( $is_form_editor ) {
			return sprintf(
				"<img src='%s' alt='%s' title='%s' class='gf-pipe-recorder-preview'/>",
				gf_pipe()->get_base_url() . '/images/recorder-preview.png',
				esc_html__( 'Pipe Recorder Preview', 'gravityformspipe' ),
				esc_html__( 'Pipe Recorder Preview', 'gravityformspipe' )
			);
		}

		$id       = (int) $this->id;
		$field_id = $is_entry_detail || $is_form_editor || $form_id == 0 ? "input_$id" : 'input_' . $form_id . "_$id";

		$disabled_text = $is_form_editor ? 'disabled="disabled"' : '';

		$class_attribute    = $is_entry_detail || $is_form_editor ? '' : "class='gform_hidden'";
		$required_attribute = $this->isRequired ? 'aria-required="true"' : '';
		$invalid_attribute  = $this->failed_validation ? 'aria-invalid="true"' : 'aria-invalid="false"';

		// Build Javascript size array.
		$size = array(
			'width'  => esc_js( $this->pipe['width'] ),
			'height' => esc_js( $this->pipe['bottom_menu'] ? $this->pipe['height'] + 30 : $this->pipe['height'] ),
		);

		// Build Javascript flash variables array.
		$flash_vars = array(
			'qualityurl'  => 'avq/' . esc_js( $this->pipe['resolution'] ) . '.xml',
			'accountHash' => gf_pipe()->get_plugin_setting( 'accountHash' ),
			'eid'         => $this->pipe['environment'],
			'showMenu'    => $this->pipe['bottom_menu'] ? 'true' : 'false',
			'mrt'         => $this->pipe['recording_time'],
			'sis'         => 0,
			'asv'         => $this->pipe['autosave'] ? 1 : 0,
			'mv'          => $this->pipe['mirror'] ? 1 : 0,
			'ao'          => isset( $this->pipe['audio_only'] ) && $this->pipe['audio_only'] ? 1 : 0,
		);

		// Add hidden field to store video URLs.
		$html = sprintf( "<input name='input_%d' id='%s' type='hidden' {$class_attribute} {$required_attribute} {$invalid_attribute} value='%s' %s/>", $id, $field_id, esc_attr( $value ), $disabled_text );

		// Initialize recorder.
		$html .= sprintf( '<script type="text/javascript">var size = %s;var flashvars = %s;</script><div id="hdfvr-content" ></div>', json_encode( $size ), json_encode( $flash_vars ) );

		return $html;

	}

	/**
	 * Return the button for the form editor.
	 *
	 * @since  1.0
	 * @since  1.2 Added icon & description to button array.
	 *
	 * @access public
	 *
	 * @return array
	 */
	public function get_form_editor_button() {

		return array(
			'group'       => 'advanced_fields',
			'text'        => $this->get_form_editor_field_title(),
			'icon'        => $this->get_form_editor_field_icon(),
			'description' => $this->get_form_editor_field_description(),
		);

	}

	/**
	 * Returns the class names of the settings which should be available on the field in the form editor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return array
	 */
	public function get_form_editor_field_settings() {

		return array(
			'conditional_logic_field_setting',
			'error_message_setting',
			'label_setting',
			'label_placement_setting',
			'admin_label_setting',
			'rules_setting',
			'visibility_setting',
			'description_setting',
			'css_class_setting',
			'piperecorder_resolution_setting',
			'piperecorder_width_setting',
			'piperecorder_height_setting',
			'piperecorder_recording_time_setting',
			'piperecorder_options_setting',
			'piperecorder_environment_setting',
		);

	}

	/**
	 * Return the field title.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @return string
	 */
	public function get_form_editor_field_title() {

		return esc_attr__( 'Pipe Recorder', 'gravityformspipe' );

	}

	/**
	 * Returns the field's form editor description.
	 *
	 * @since 1.2
	 *
	 * @return string
	 */
	public function get_form_editor_field_description() {
		return esc_attr__( 'The pipe field allows users to add a pipe video recording to their submission.', 'gravityformspipe' );
	}

	/**
	 * Returns the field's form editor icon.
	 *
	 * This could be an icon url or a dashicons class.
	 *
	 * @since 1.2
	 *
	 * @return string
	 */
	public function get_form_editor_field_icon() {
		return gf_pipe()->get_base_url() . '/images/menu-icon.svg';
	}

	/**
	 * Returns the scripts to be included for this field type in the form editor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses   GF_Field::get_form_editor_field_title()
	 *
	 * @return string
	 */
	public function get_form_editor_inline_script_on_page_render() {

		return sprintf( "function SetDefaultValues_%s(field) {field.label = '%s';}", $this->type, $this->get_form_editor_field_title() ) . PHP_EOL;

	}

	/**
	 * Format the entry value for display on the entry detail page and for the {all_fields} merge tag.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string|array $value    The field value.
	 * @param string       $currency The entry currency code.
	 * @param bool|false   $use_text When processing choice based fields should the choice text be returned instead of the value.
	 * @param string       $format   The format requested for the location the merge is being used. Possible values: html, text or url.
	 * @param string       $media    The location where the value will be displayed. Possible values: screen or email.
	 *
	 * @uses   GFAddOn::maybe_decode_json()
	 *
	 * @return string
	 */
	public function get_value_entry_detail( $value, $currency = '', $use_text = false, $format = 'html', $media = 'screen' ) {

		// If field value is empty, return.
		if ( empty( $value ) ) {
			return '';
		}

		// Get video details.
		$video_details = gf_pipe()->maybe_decode_json( $value );

		// If video details is not an array, return.
		if ( ! is_array( $video_details ) ) {
			return '';
		}

		// If HTML is enabled, return a hyperlink.
		if ( 'html' === $format ) {

			return sprintf(
				'<a href="%s">%s</a>',
				$video_details['video'],
				basename( $video_details['video'] )
			);

		}

		return rgar( $video_details, 'video' );

	}

	/**
	 * Format the entry value for display on the entries list page.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param string|array $value    The field value.
	 * @param array        $entry    The Entry Object currently being processed.
	 * @param string       $field_id The field or input ID currently being processed.
	 * @param array        $columns  The properties for the columns being displayed on the entry list page.
	 * @param array        $form     The Form Object currently being processed.
	 *
	 * @uses   GFAddOn::maybe_decode_json()
	 *
	 * @return string
	 */
	public function get_value_entry_list( $value, $entry, $field_id, $columns, $form ) {

		// If field value is empty, return.
		if ( empty( $value ) ) {
			return '';
		}

		// Get video details.
		$video_details = gf_pipe()->maybe_decode_json( $value );

		// If video details is not an array, return.
		if ( ! is_array( $video_details ) ) {
			return '';
		}

		return sprintf(
			'<a href="%s" target="_blank" title="%s">%s</a>',
			$video_details['video'],
			esc_attr__( 'Click to view', 'gravityformspipe' ),
			basename( $video_details['video'] )
		);

	}

	/**
	 * Format the entry value for when the field/input merge tag is processed. Not called for the {all_fields} merge tag.
	 *
	 * Return a value that is safe for the context specified by $format.
	 *
	 * @since  1.0.1
	 * @access public
	 *
	 * @param string|array $value      The field value. Depending on the location the merge tag is being used the following functions may have already been applied to the value: esc_html, nl2br, and urlencode.
	 * @param string       $input_id   The field or input ID from the merge tag currently being processed.
	 * @param array        $entry      The Entry Object currently being processed.
	 * @param array        $form       The Form Object currently being processed.
	 * @param string       $modifier   The merge tag modifier. e.g. value
	 * @param string|array $raw_value  The raw field value from before any formatting was applied to $value.
	 * @param bool         $url_encode Indicates if the urlencode function may have been applied to the $value.
	 * @param bool         $esc_html   Indicates if the esc_html function may have been applied to the $value.
	 * @param string       $format     The format requested for the location the merge is being used. Possible values: html, text or url.
	 * @param bool         $nl2br      Indicates if the nl2br function may have been applied to the $value.
	 *
	 * @uses   GFAddOn::maybe_decode_json()
	 *
	 * @return string
	 */
	public function get_value_merge_tag( $value, $input_id, $entry, $form, $modifier, $raw_value, $url_encode, $esc_html, $format, $nl2br ) {

		// If the field value is empty, return.
		if ( empty( $value ) ) {
			return '';
		}

		// Get video details.
		$video_details = gf_pipe()->maybe_decode_json( $value );

		// If video details is not an array, return.
		if ( ! is_array( $video_details ) ) {
			return '';
		}

		return rgar( $video_details, 'video' );

	}

}

GF_Fields::register( new GF_Field_Pipe_Recorder() );
