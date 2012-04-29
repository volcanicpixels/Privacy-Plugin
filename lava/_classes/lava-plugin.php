<?php
/**
 * Plugin Class
 *
 * @package Lava
 * @subpackage Plugin
 * @author Daniel Chatfield
 *
 * @since 1.0.0
 */
class Lava_Plugin
{
	public $_singletons = array();
	/**
	 * Constructor function called at initialization
	 *
	 * @access public
	 * @param __FILE__ $plugin_file_path
	 * @param string $plugin_name
	 * @param float $plugin_version
	 * @param boolean $load_vendor
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function __construct( $plugin_file_path, $plugin_name, $plugin_version, $load_vendor = true )
	{
		$this->_plugin_file_path = $plugin_file_path;
		$this->_plugin_name = $plugin_name;
		$this->_plugin_version = $plugin_version;
		$this->_plugin_id = strtolower( str_replace( ' ', '_', $plugin_name ) );
		$this->_plugin_callbacks = null;
		$this->_plugin_vendor = null;

		//Add the class autoloader
		spl_autoload_register( array( $this, '__autoload' ) );


		//If pluginCallbacks exist then lets include them
		$plugin_callbacks_file_path = dirname( $plugin_file_path ).'/plugin-callbacks.php';

		if( file_exists( $plugin_callbacks_file_path ) )
		{
			include( $plugin_callbacks_file_path );
			$class_name = $this->_namespace( 'callbacks' );
			$this->_plugin_callbacks = $this->_instantiate_class( $class_name );
		}

		//initialise this class so that hooks are registered
		$this->_funcs();

		if( $load_vendor ) {
			require_once( dirname( $plugin_file_path ) .  '/vendor.php' );
			$class_name = $this->_namespace( 'vendor' );
			$this->_plugin_vendor = $this->_instantiate_class( $class_name, array(), 'noprefix' );
		}
	}

	/**
	 * Defines what to do when a non-declared class is referenced
	 *
	 * @access public
	 * @param string $className
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function __autoload( $class_name )
	{
		$file_name = strtolower( str_replace( '_' , '-', $class_name ) );
		$main_dirs = array(
			dirname( __FILE__ ),		//check plugin _classes folder and sub dirs
			dirname( $this->_get_plugin_file_path() ) . '\\_classes'   //check lava _classes folder and sub dirs

		);

		$sub_dirs = array(
			'',
			'_ajax',
			'_extensions',
			'_external',
			'_pages',
			'_settings',
			'_skins',
			'_tables'
		);


		foreach( $main_dirs as $main_dir ) {
			foreach( $sub_dirs as $sub_dir ) {
				$file_path = "{$main_dir}\\{$sub_dir}\\{$file_name}.php";
				if( file_exists( $file_path ) and ! class_exists( $class_name ) ) {
					include_once( $file_path );
				}
			}
		}
	}

	function _instantiate_class( $class_name, $args = array(), $should_prefix = 'prefix' ) {
		if( 'prefix' == $should_prefix )
			$class_name = "Lava_" . $class_name;

		return new $class_name( $this, $args );
	}


	function _get_singleton( $class_name, $remove_child ) {
		if( array_key_exists( $class_name , $this->_singletons ) ) {
			return $this->_singletons[ $class_name ];
		} else {
			return $this->_singletons = $this->_instantiate_class( $class_name );
		}
	}

	function _namespace( $append = null ) {
		$namespace = $this->_get_plugin_id();
		if( ! is_null( $append ) ) {
			$namespace .= "_{$append}";
		}

		return $namespace;
	}



	/**
	 * Accessor methods for plugin data
	 */

	function _get_plugin_file_path() {
		return $this->_plugin_file_path;
	}

	function _get_plugin_name() {
		return $this->_plugin_name;
	}

	function _get_plugin_id() {
		return strtolower( str_replace( ' ', '_', $this->_get_plugin_name() ) );
	}

	function _get_plugin_version() {
		return $this->_plugin_version;
	}

	function _get_plugin_vendor() {
		return $this->_plugin_vendor;
	}

	function _get_plugin_callbacks() {
		return $this->_plugin_callbacks;
	}


	/**
	 * Methods to access controller classes
	 */

	function _funcs( $remove_child = true ) {
		return $this->_functions( $remove_child );
	}

	function _functions( $remove_child = true ) {
		$class_name = 'Functions';
		return $this->_get_singleton( $class_name, $remove_child );
	}

	function _pages( $remove_child = true ) {
		$class_name = 'Pages';
		return $this->_get_singleton( $class_name, $remove_child );
	}

	function _settings( $remove_child = true ) {
		$class_name = 'Settings';
		return $this->_get_singleton( $class_name, $remove_child );
	}

	function _skins( $remove_child = true ) {
		$class_name = "Skins";
		return $this->_get_singleton( $class_name, $remove_child );
	}

}
?>