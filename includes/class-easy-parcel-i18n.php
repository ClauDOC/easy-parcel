<?php

/**
 * Define the internationalization functionality
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @link       https://claudio-lombardo.it
 * @since      1.0.0
 *
 * @package    Easy_Parcel
 * @subpackage Easy_Parcel/includes
 */

/**
 * Define the internationalization functionality.
 *
 * Loads and defines the internationalization files for this plugin
 * so that it is ready for translation.
 *
 * @since      1.0.0
 * @package    Easy_Parcel
 * @subpackage Easy_Parcel/includes
 * @author     Dr. Claudio Lombardo <claudio-lombardo@outlook.it>
 */
class Easy_Parcel_i18n {


	/**
	 * Load the plugin text domain for translation.
	 *
	 * @since    1.0.0
	 */
	public function load_plugin_textdomain() {

		load_plugin_textdomain(
			'easy-parcel',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
