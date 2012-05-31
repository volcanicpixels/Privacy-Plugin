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

class Volcanic_Pixels_Privacy_Plugin extends Lava_Plugin {

	public $plugin_name = "Private Blog";
	public $plugin_version = 4.05;

	function register_settings() {
		$this->settings()
				->add_setting( 'enabled', 'checkbox' )
		;
	}

	function register_pages() {
		$this->pages()
				->add_settings_page()
				->add_skins_page()
					->set_page_title( $this->__( 'Login Page Skin' ) )
		;
	}
}

$the_plugin = new Volcanic_Pixels_Privacy_Plugin( __FILE__ );

$the_plugin->register_action_methods( $the_plugin );

?>