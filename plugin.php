<?php
/*
Plugin Name: Private Blog
Plugin URI: http://www.volcanicpixels.com/password-protect-wordpress-plugin/
Description: Private Blog is a wordpress plugin which allows you to password protect all of your wordpress blog including all posts and feeds with a single password.
Version: 4.05
Author: Daniel Chatfield
Author URI: http://www.volcanicpixels.com/
License: GPLv2
*/
?>
<?php
include( dirname( __FILE__ ) ."/lava/lava.php" );

class Volcanic_Pixels_Private_Blog extends Lava_Plugin {

	public $_plugin_name = "Private Blog";
	public $_plugin_version = 4.05;

	public $_plugin_actions = array(
		'do_logout',
		'do_login',
		'do_login_display'
	);

	public $_plugin_filters = array(
		'get_query_args',
		'is_authorised',
		'is_logout_request',
		'is_login_request'
	);

	###########################################
	##	lava hooks
	###########################################

	function _init() {
		parent::_init();
		$this->_set_fingerprint_cookie();
		$this->_do_action_if( 'logout', 'logout_request' );
		$this->_do_action_if( 'login', 'login_request' );
	}

	function _get_header() {
		// [action], [condition], [default], [should_terminate] - make sure the page is terminated after printing the login template
		$this->_do_action_unless( 'login_display', 'authorised', false, true );
	}

	###########################################
	##	Plugin hooks
	###########################################

	function get_query_args( $args ) {
		$new = array(
			'logged_in',
			'logged_out',
			'incorrect_credentials'
		);
		return array_merge( $args, $new );
	}

	/*
		Checks whether user is logging out
	*/
	function is_logout_request( $current ) {
		//die('gary');
		if( $this->_request_var( 'logout', false ) or $this->_request_var( 'action', '' ) == 'logout' ) {
			$current = true;
		}
		return $current;
	}

	function do_logout() {
		die('test');
	}

	function is_login_request( $current ) {
		if( $this->_request_var( 'login', false ) or $this->_request_var( 'action', '' ) == 'login' ) {
			$current = true;
		}
		return $current;
	}

	function do_login() {
		// check password against setting
		$password = $this->_settings()->_get_value_for( 'password' );
		if( $this->_request_var( 'password' ) == $password ) {
			$fingerprint = array(
				'password' => $password,
				'_expiration'  => $this->_settings()->_get_value_for( 'login_duration', 60*60*24 )
			);
			$this->_merge_fingerprint( $fingerprint );
			$args = $this->_apply_plugin_filters( 'get_query_args', array() );
			$url = remove_query_arg( $args );
			$url = add_query_arg( 'logged_in', '', $url );
			wp_redirect( $url );
			exit;
		} else {
			//details incorrect
			$args = $this->_apply_plugin_filters( 'get_query_args', array() );
			$url = remove_query_arg( $args );
			$url = add_query_arg( 'incorrect_credentials', '', $url );
			wp_redirect( $url );
			exit;
		}
	}


	function is_authorised( $current ) {
		$fingerprint = $this->_get_fingerprint();
		if( array_key_exists( 'password', $fingerprint ) ) {
			if( $fingerprint['password'] == $this->_settings()->_get_value_for('password') ) {
				$current = true;
			}
		}
		return $current;
	}


	/*
		Displays the login page
	*/
	function do_login_display() {
		$this->_skins()->_display_template( 'login' );
	}
}

$the_plugin = new Volcanic_Pixels_Private_Blog( __FILE__ );


?>