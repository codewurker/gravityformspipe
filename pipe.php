<?php
/**
 * Plugin Name: Gravity Forms Pipe Add-On
 * Plugin URI: https://gravityforms.com
 * Description: Record videos on your website with Pipe and add them to your Gravity Forms entries.
 * Version: 1.4.0
 * Author: Gravity Forms
 * Author URI: https://gravityforms.com
 * License: GPL-2.0+
 * Text Domain: gravityformspipe
 * Domain Path: /languages
 *
 * ------------------------------------------------------------------------
 * Copyright 2009-2023 Rocketgenius, Inc.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA
 **/

if ( ! defined( 'ABSPATH' ) ) {
	die();
}

define( 'GF_PIPE_VERSION', '1.4.0' );

// If Gravity Forms is loaded, bootstrap the Pipe Add-On.
add_action( 'gform_loaded', array( 'GF_Pipe_Bootstrap', 'load' ), 5 );

/**
 * Class GF_Pipe_Bootstrap
 *
 * Handles the loading of the Pipe Add-On and registers with the Add-On framework.
 */
class GF_Pipe_Bootstrap {

	/**
	 * If the Add-On Framework exists, Pipe Add-On is loaded.
	 *
	 * @access public
	 * @static
	 */
	public static function load() {

		if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
			return;
		}

		require_once( 'class-gf-pipe.php' );

		GFAddOn::register( 'GF_Pipe' );

	}

}

/**
 * Returns an instance of the GF_Pipe class
 *
 * @see    GF_Pipe::get_instance()
 *
 * @return GF_Pipe
 */
function gf_pipe() {
	return GF_Pipe::get_instance();
}
