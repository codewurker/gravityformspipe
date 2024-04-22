<?php

// If Gravity Forms is not loaded, exit.
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
	 * Returns the field inner markup.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @param array        $form  Form object.
	 * @param string|array $value Field value. From default/dynamic population, $_POST, or a resumed incomplete submission.
	 * @param null|array   $entry Entry object currently being edited. Defaults to null.
	 *
	 * @uses GF_Field::is_form_editor()
	 *
	 * @return string
	 */
	public function get_field_input( $form, $value = '', $entry = null ) {

		// If we are in the form editor, display an error message.
		if ( $this->is_form_editor() ) {
			return sprintf(
				'<div class="ginput_container"><p>%s</p></div>',
				esc_html__( 'Pipe Recorder field is unavailable because a valid account hash has not been configured.', 'gravityformspipe' )
			);
		}

		return;

	}

	/**
	 * Returns the message to be displayed in the form editor sidebar.
	 *
	 * @since  1.4.0
	 *
	 * @return string
	 */
	public function get_field_sidebar_messages() {
		return esc_html__( 'Pipe Recorder field is unavailable because a valid account hash has not been configured.', 'gravityformspipe' );
	}

	/**
	 * Returns the field button properties for the form editor.
	 *
	 * @since  1.0
	 * @access public
	 *
	 * @uses GF_Field::get_form_editor_field_title()
	 *
	 * @return array
	 */
	public function get_form_editor_button() {

		return array();

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

		return array( 'label_setting' );

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
			return;
		}

		// Get video details.
		$video_details = json_decode( $value, true );

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

// Register field with Gravity Forms.
GF_Fields::register( new GF_Field_Pipe_Recorder() );
