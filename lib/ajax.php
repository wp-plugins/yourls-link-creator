<?php
/**
 * YOURLS Link Creator - Ajax Module
 *
 * Contains our ajax related functions
 *
 * @package YOURLS Link Creator
 */
/*  Copyright 2015 Reaktiv Studios

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License (GPL v2) only.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

if ( ! class_exists( 'YOURLSCreator_Ajax' ) ) {

// Start up the engine
class YOURLSCreator_Ajax
{

	/**
	 * This is our constructor
	 *
	 * @return YOURLSCreator_Ajax
	 */
	public function __construct() {
		add_action( 'wp_ajax_create_yourls',        array( $this, 'create_yourls'       )           );
		add_action( 'wp_ajax_delete_yourls',        array( $this, 'delete_yourls'       )           );
		add_action( 'wp_ajax_stats_yourls',         array( $this, 'stats_yourls'        )           );
		add_action( 'wp_ajax_inline_yourls',        array( $this, 'inline_yourls'       )           );
		add_action( 'wp_ajax_refresh_yourls',       array( $this, 'refresh_yourls'      )           );
		add_action( 'wp_ajax_convert_yourls',       array( $this, 'convert_yourls'      )           );
	}

	/**
	 * Create shortlink function
	 */
	public function create_yourls() {

		// only run on admin
		if ( ! is_admin() ) {
			die();
		}

		// start our return
		$ret = array();

		// verify our nonce
		$check	= check_ajax_referer( 'yourls_editor_create', 'nonce', false );

		// check to see if our nonce failed
		if( ! $check ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NONCE_FAILED';
			$ret['message'] = __( 'The nonce did not validate.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail if the API key or URL have not been entered
		if(	false === $api = YOURLSCreator_Helper::get_yourls_api_data() ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_API_DATA';
			$ret['message'] = __( 'No API data has been entered.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail without a post ID
		if( empty( $_POST['post_id'] ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_POST_ID';
			$ret['message'] = __( 'No post ID was present.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// now cast the post ID
		$post_id    = absint( $_POST['post_id'] );

		// do a quick check for a URL
		if ( false !== $link = YOURLSCreator_Helper::get_yourls_meta( $post_id, '_yourls_url' ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'URL_EXISTS';
			$ret['message'] = __( 'A URL already exists.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// check for keyword
		$keyword    = ! empty( $_POST['keyword'] ) ? esc_sql( $_POST['keyword'] ) : '';

		// get my post URL and title
		$url    = get_permalink( $post_id );
		$title  = get_the_title( $post_id );

		// set my args for the API call
		$args   = array( 'url' => esc_url( $url ), 'title' => esc_attr( $title ), 'keyword' => $keyword );

		// make the API call
		$build  = YOURLSCreator_Helper::run_yourls_api_call( 'shorturl', $args );

		// bail if empty data
		if ( empty( $build ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'EMPTY_API';
			$ret['message'] = __( 'There was an unknown API error.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail error received
		if ( false === $build['success'] ) {
			$ret['success'] = false;
			$ret['errcode'] = $build['errcode'];
			$ret['message'] = $build['message'];
			echo json_encode( $ret );
			die();
		}

		// we have done our error checking and we are ready to go
		if( false !== $build['success'] && ! empty( $build['data']['shorturl'] ) ) {

			// get my short URL
			$shorturl   = esc_url( $build['data']['shorturl'] );

			// update the post meta
			update_post_meta( $post_id, '_yourls_url', $shorturl );
			update_post_meta( $post_id, '_yourls_clicks', '0' );

			// and do the API return
			$ret['success'] = true;
			$ret['message'] = __( 'You have created a new YOURLS link.', 'wpyourls' );
			$ret['linkurl'] = $shorturl;
			$ret['linkbox'] = YOURLSCreator_Helper::get_yourls_linkbox( $shorturl, $post_id );
			echo json_encode( $ret );
			die();
		}

		// we've reached the end, and nothing worked....
		$ret['success'] = false;
		$ret['errcode'] = 'UNKNOWN';
		$ret['message'] = __( 'There was an unknown error.', 'wpyourls' );
		echo json_encode( $ret );
		die();
	}

	/**
	 * Delete shortlink function
	 */
	public function delete_yourls() {

		// only run on admin
		if ( ! is_admin() ) {
			die();
		}

		// start our return
		$ret = array();

		// verify our nonce
		$check	= check_ajax_referer( 'yourls_editor_delete', 'nonce', false );

		// check to see if our nonce failed
		if( ! $check ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NONCE_FAILED';
			$ret['message'] = __( 'The nonce did not validate.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail if the API key or URL have not been entered
		if(	false === $api = YOURLSCreator_Helper::get_yourls_api_data() ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_API_DATA';
			$ret['message'] = __( 'No API data has been entered.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail without a post ID
		if( empty( $_POST['post_id'] ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_POST_ID';
			$ret['message'] = __( 'No post ID was present.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// now cast the post ID
		$post_id    = absint( $_POST['post_id'] );

		// do a quick check for a URL
		if ( false === $link = YOURLSCreator_Helper::get_yourls_meta( $post_id, '_yourls_url' ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_URL_EXISTS';
			$ret['message'] = __( 'There is no URL to delete.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// passed it all. go forward
		delete_post_meta( $post_id, '_yourls_url' );

		// and do the API return
		$ret['success'] = true;
		$ret['message'] = __( 'You have removed your YOURLS link.', 'wpyourls' );
		$ret['linkbox'] = YOURLSCreator_Helper::get_yourls_subbox( $post_id );
		echo json_encode( $ret );
		die();
	}

	/**
	 * retrieve stats
	 */
	public function stats_yourls() {

		// only run on admin
		if ( ! is_admin() ) {
			die();
		}

		// start our return
		$ret = array();

		// bail if the API key or URL have not been entered
		if(	false === $api = YOURLSCreator_Helper::get_yourls_api_data() ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_API_DATA';
			$ret['message'] = __( 'No API data has been entered.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail without a post ID
		if( empty( $_POST['post_id'] ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_POST_ID';
			$ret['message'] = __( 'No post ID was present.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// now cast the post ID
		$post_id    = absint( $_POST['post_id'] );

		// verify our nonce
		$check	= check_ajax_referer( 'yourls_inline_update_' . absint( $post_id ), 'nonce', false );

		// check to see if our nonce failed
		if( ! $check ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NONCE_FAILED';
			$ret['message'] = __( 'The nonce did not validate.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// get my click number
		$clicks = YOURLSCreator_Helper::get_single_click_count( $post_id );

		// bad API call
		if ( empty( $clicks['success'] ) ) {
			$ret['success'] = false;
			$ret['errcode'] = $clicks['errcode'];
			$ret['message'] = $clicks['message'];
			echo json_encode( $ret );
			die();
		}

		// got it. update the meta
		update_post_meta( $post_id, '_yourls_clicks', $clicks['clicknm'] );

		// and do the API return
		$ret['success'] = true;
		$ret['message'] = __( 'Your YOURLS click count has been updated', 'wpyourls' );
		$ret['clicknm'] = $clicks['clicknm'];
		echo json_encode( $ret );
		die();
	}

	/**
	 * Create shortlink function inline. Called on ajax
	 */
	public function inline_yourls() {

		// only run on admin
		if ( ! is_admin() ) {
			die();
		}

		// start our return
		$ret = array();

		// bail if the API key or URL have not been entered
		if(	false === $api = YOURLSCreator_Helper::get_yourls_api_data() ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_API_DATA';
			$ret['message'] = __( 'No API data has been entered.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail without a post ID
		if( empty( $_POST['post_id'] ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_POST_ID';
			$ret['message'] = __( 'No post ID was present.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// now cast the post ID
		$post_id    = absint( $_POST['post_id'] );

		// verify our nonce
		$check	= check_ajax_referer( 'yourls_inline_create_' . absint( $post_id ), 'nonce', false );

		// check to see if our nonce failed
		if( ! $check ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NONCE_FAILED';
			$ret['message'] = __( 'The nonce did not validate.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// do a quick check for a URL
		if ( false !== $link = YOURLSCreator_Helper::get_yourls_meta( $post_id, '_yourls_url' ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'URL_EXISTS';
			$ret['message'] = __( 'A URL already exists.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// get my post URL and title
		$url    = get_permalink( $post_id );
		$title  = get_the_title( $post_id );

		// set my args for the API call
		$args   = array( 'url' => esc_url( $url ), 'title' => esc_attr( $title ) );

		// make the API call
		$build  = YOURLSCreator_Helper::run_yourls_api_call( 'shorturl', $args );

		// bail if empty data
		if ( empty( $build ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'EMPTY_API';
			$ret['message'] = __( 'There was an unknown API error.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail error received
		if ( false === $build['success'] ) {
			$ret['success'] = false;
			$ret['errcode'] = $build['errcode'];
			$ret['message'] = $build['message'];
			echo json_encode( $ret );
			die();
		}

		// we have done our error checking and we are ready to go
		if( false !== $build['success'] && ! empty( $build['data']['shorturl'] ) ) {

			// get my short URL
			$shorturl   = esc_url( $build['data']['shorturl'] );

			// update the post meta
			update_post_meta( $post_id, '_yourls_url', $shorturl );
			update_post_meta( $post_id, '_yourls_clicks', '0' );

			// and do the API return
			$ret['success'] = true;
			$ret['message'] = __( 'You have created a new YOURLS link.', 'wpyourls' );
			$ret['rowactn'] = '<span class="update-yourls">' . YOURLSCreator_Helper::update_row_action( $post_id ) . '</span>';
			echo json_encode( $ret );
			die();
		}

		// we've reached the end, and nothing worked....
		$ret['success'] = false;
		$ret['errcode'] = 'UNKNOWN';
		$ret['message'] = __( 'There was an unknown error.', 'wpyourls' );
		echo json_encode( $ret );
		die();
	}

	/**
	 * run update job to get click counts via manual ajax
	 */
	public function refresh_yourls() {

		// only run on admin
		if ( ! is_admin() ) {
			die();
		}

		// start our return
		$ret = array();

		// verify our nonce
		$check	= check_ajax_referer( 'yourls_refresh_nonce', 'nonce', false );

		// check to see if our nonce failed
		if( ! $check ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NONCE_FAILED';
			$ret['message'] = __( 'The nonce did not validate.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// bail if the API key or URL have not been entered
		if(	false === $api = YOURLSCreator_Helper::get_yourls_api_data() ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_API_DATA';
			$ret['message'] = __( 'No API data has been entered.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// fetch the IDs that contain a YOURLS url meta key
		if ( false === $items = YOURLSCreator_Helper::get_yourls_post_ids() ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_POST_IDS';
			$ret['message'] = __( 'There are no items with stored URLs.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// loop the IDs
		foreach ( $items as $item_id ) {

			// get my click number
			$clicks = YOURLSCreator_Helper::get_single_click_count( $item_id );

			// bad API call
			if ( empty( $clicks['success'] ) ) {
				$ret['success'] = false;
				$ret['errcode'] = $clicks['errcode'];
				$ret['message'] = $clicks['message'];
				echo json_encode( $ret );
				die();
			}

			// got it. update the meta
			update_post_meta( $item_id, '_yourls_clicks', $clicks['clicknm'] );
		}

		// and do the API return
		$ret['success'] = true;
		$ret['message'] = __( 'The click counts have been updated', 'wpyourls' );
		echo json_encode( $ret );
		die();
	}

	/**
	 * convert from Ozh (and Otto's) plugin
	 */
	public function convert_yourls() {

		// only run on admin
		if ( ! is_admin() ) {
			die();
		}

		// verify our nonce
		$check	= check_ajax_referer( 'yourls_convert_nonce', 'nonce', false );

		// check to see if our nonce failed
		if( ! $check ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NONCE_FAILED';
			$ret['message'] = __( 'The nonce did not validate.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// filter our key to replace
		$key = apply_filters( 'yourls_key_to_convert', 'yourls_shorturl' );

		// fetch the IDs that contain a YOURLS url meta key
		if ( false === $items = YOURLSCreator_Helper::get_yourls_post_ids( $key ) ) {
			$ret['success'] = false;
			$ret['errcode'] = 'NO_KEYS';
			$ret['message'] = __( 'There are no meta keys to convert.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// set up SQL query
		global $wpdb;

		// prepare my query
		$setup  = $wpdb->prepare("
			UPDATE $wpdb->postmeta
			SET    meta_key = '%s'
			WHERE  meta_key = '%s'
			",
			esc_sql( '_yourls_url' ), esc_sql( $key )
		);

		// run SQL query
		$query = $wpdb->query( $setup );

		// start our return
		$ret = array();

		// no matches, return message
		if( $query == 0 ) {
			$ret['success'] = false;
			$ret['errcode'] = 'KEY_MISSING';
			$ret['message'] = __( 'There are no keys matching this criteria. Please try again.', 'wpyourls' );
			echo json_encode( $ret );
			die();
		}

		// we had matches. return the success message with a count
		if( $query > 0 ) {
			$ret['success'] = true;
			$ret['errcode'] = null;
			$ret['updated'] = $query;
			$ret['message'] = sprintf( _n( '%d key has been updated.', '%d keys have been updated.', $query, 'wpyourls' ), $query );
			echo json_encode( $ret );
			die();
		}
	}

// end class
}

// end exists check
}

// Instantiate our class
new YOURLSCreator_Ajax();

