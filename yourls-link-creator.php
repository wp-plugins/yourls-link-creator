<?php
/*
Plugin Name: YOURLS Link Creator
Plugin URI: http://andrewnorcross.com/plugins/yourls-link-creator/
Description: Creates a shortlink using YOURLS and stores as postmeta.
Version: 2.0.3
Author: Andrew Norcross
Author URI: http://andrewnorcross.com

	Copyright 2012 Andrew Norcross

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if( ! defined( 'YOURLS_BASE' ) ) {
	define( 'YOURLS_BASE', plugin_basename(__FILE__) );
}

if( ! defined( 'YOURS_DIR' ) ) {
	define( 'YOURS_DIR', plugin_dir_path( __FILE__ ) );
}

if( ! defined( 'YOURS_VER' ) ) {
	define( 'YOURS_VER', '2.0.3' );
}

// Start up the engine
class YOURLSCreator
{
	/**
	 * Static property to hold our singleton instance
	 * @var Code_Docs_Core
	 */
	static $instance = false;

	/**
	 * This is our constructor
	 *
	 * @return YOURLSCreator
	 */
	private function __construct() {
		add_action( 'plugins_loaded',               array( $this, 'textdomain'          )           );
		add_action( 'plugins_loaded',               array( $this, 'load_files'          )           );

		register_activation_hook        ( __FILE__, array( $this, 'schedule_cron'       )           );
		register_deactivation_hook      ( __FILE__, array( $this, 'remove_cron'         )           );
	}

	/**
	 * If an instance exists, this returns it.  If not, it creates one and
	 * retuns it.
	 *
	 * @return
	 */
	public static function getInstance() {

		// load an instance if not already initalized
		if ( ! self::$instance ) {
			self::$instance = new self;
		}

		// return the instance
		return self::$instance;
	}

	/**
	 * load textdomain for international goodness
	 *
	 * @return YOURLSCreator
	 */
	public function textdomain() {
		load_plugin_textdomain( 'wpyourls', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}

	/**
	 * call our files in the appropriate place
	 *
	 * @return [type] [description]
	 */
	public function load_files() {

		// load our back end
		if ( is_admin() ) {
			require_once( 'lib/admin.php' );
			require_once( 'lib/settings.php' );
			require_once( 'lib/ajax.php' );
		}

		// load our front end
		if ( ! is_admin() ) {
			require_once( 'lib/front.php' );
		}

		// load our global
		require_once( 'lib/global.php' );

		// load our helper file
		require_once( 'lib/helper.php' );
	}

	/**
	 * add our scheduled cron job
	 *
	 * @return [type] [description]
	 */
	public function schedule_cron() {
		if ( ! wp_next_scheduled( 'yourls_cron' ) ) {
			wp_schedule_event( time(), 'hourly', 'yourls_cron' );
		}
	}

	/**
	 * remove the cron job on deactivation
	 *
	 * @return [type] [description]
	 */
	public function remove_cron() {

		// fetch the timestamp
		$stamp = wp_next_scheduled( 'yourls_cron' );

		// remove the job
		wp_unschedule_event( $stamp, 'yourls_cron' );
	}

/// end class
}

// Instantiate our class
$YOURLSCreator = YOURLSCreator::getInstance();
