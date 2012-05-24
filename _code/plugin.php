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
error_reporting(E_ALL);
require( dirname( __FILE__ ) ."/_lava/lava.php" );

class Volcanic_Pixels_Privacy_Plugin extends Lava_Plugin {

	public $_plugin_name = "Private Blog";
	public $_plugin_version = 4.05;

	function _register_settings() {
		$this->_settings()
				->_add_setting( 'enabled', 'checkbox' )
		;
	}

	function _register_pages() {
		$this->_pages()
				->_add_settings_page()
				->_add_skins_page()
					->_set_page_title( $this->__( 'Login Page Skin' ) )
		;
	}
}

$the_plugin = new Volcanic_Pixels_Privacy_Plugin( __FILE__ );

$the_plugin->_register_action_methods( $the_plugin );

?>