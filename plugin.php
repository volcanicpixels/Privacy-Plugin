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
		'do_authentication',
		'do_login_display'
	);

	public $_plugin_filters = array(
		'is_authorised'
	);

	###########################################
	##	lava hooks
	###########################################

	function _init() {
		parent::_init();
	}

	function _get_header() {
		$this->_do_plugin_action( 'do_authentication' );
	}

	function _register_widgets() {
		die('asd');
	}

	###########################################
	##	lava hooks
	###########################################

	/*
		Plugin hooks
	*/
	function do_authentication() {
		$is_authorised = $this->_apply_plugin_filters( 'is_authorised', false );
		if( ! $is_authorised ) {
			$this->_do_plugin_action( 'do_login_display' );
			exit; //better to be safe
		}
	}

	function is_authorised( $current ) {
		return false;
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