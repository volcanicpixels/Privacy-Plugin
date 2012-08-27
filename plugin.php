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
/*
This plugin uses a framework to do most of the heavy lifting, you should be able to follow the code though.
*/
include( dirname( __FILE__ ) ."/lava/lava.php" );

class Volcanic_Pixels_Private_Blog extends Lava_Plugin {

	public $_plugin_name = "Private Blog";
	public $_plugin_version = 4.05;

	/*
		An array of actions that if a method by the same name exists on a plugin class it will be automagically registered.
	*/
	public $_plugin_actions = array(
		'do_logout',
		'do_login',
		'do_login_display',
		'do_feed_message'
	);
	/*
		Same as above but with filters
	*/

	public $_plugin_filters = array(
		'get_query_args',
		'is_authorised',
		'is_logout_request',
		'is_login_request'
	);

	###########################################
	##	lava hooks - these are functions called by the framework (most of them are directly linked to WordPress hooks)
	###########################################

	function _init() {
		parent::_init();

		$hooks = array(
			'do_feed',
			'do_feed_rdf',
			'do_feed_rss',
			'do_feed_rss2',
			'do_feed_atom'
		);

		if( $this->_settings()->_get_value_for( 'enable_public_rss_feeds', 'off' ) != 'on' ) {
			$this->_add_action( $hooks, 'do_feed', 1 );
		}


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

	function do_feed() {
		$this->_do_action_unless( 'feed_message', 'authorised', false, true );
	}

	function do_feed_message() {
		wp_die( __('The feed for this website is protected, please visit our <a href="'. get_bloginfo('url') .'">website</a> to login first!') );
	}

	function get_query_args( $args ) {
		$new = array(
			'logged_in',
			'logged_out',
			'incorrect_credentials',
			$this->_namespace( 'action' ),
			$this->_namespace( 'logout' )
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
		$fingerprint = array(
			'password_expiration' => 0
		);
		$this->_merge_fingerprint( $fingerprint );
		$args = $this->_apply_plugin_filters( 'get_query_args', array() );
		$url = remove_query_arg( $args );
		$url = add_query_arg( 'logged_out', '', $url );
		wp_redirect( $url );
		exit;
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
				'password_expiration'  => current_time( 'timestamp' ) + $this->_settings()->_get_value_for( 'login_duration', 60*60*24 )
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
		$fingerprint = $this->_get_fingerprint( 'password_expiration' );
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