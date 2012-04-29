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
class lavaPlugin
{
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
			$this->_plugin_vendor = $this->_instantiate_class( $class_name );
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
		$main_dirs = array(
			dirname( __FILE__ ),		//check plugin _classes folder and sub dirs
			dirname( $this->_get_plugin_file_path() ) . '\\_classes'   //check lava _classes folder and sub dirs

		);

		$subDirs = array(
			'',
			'_ajax',
			'_extensions',
			'_external',
			'_pages',
			'_settings',
			'_skins',
			'_tables'
		);


		foreach( $mainDirs as $mainDir ) {
			foreach( $subDirs as $subDir ) {
				$file_path = "{$mainDir}\\{$subDir}\\{$className}.php";
				if( file_exists( $file_path ) and ! class_exists( $className ) ) {
					include_once( $file_path );
				}
			}
		}
	}






	/**
	 * Accessor methods for plugin data
	 */

	function _getPluginFilePath() {
		return $this->_plugin_file_path;
	}

	function _getPluginName() {
		return $this->_plugin_name;
	}

	function _getPluginId()

	function _getPluginVersion() {
		return $this->_plugin_version;
	}

	function _getPluginVendor() {
		return $this->_plugin_vendor;
	}

	function _getPluginCallbacks() {
		return $this->_plugin_callbacks;
	}




	/**
	 * _request function.
	 *	Determines whether the current request matches the argument
	 *
	 * @return lavaPlugin
	 *
	 * @since 1.0.0
	 */
	function _request( $request )
	{
		switch( $request )
		{
			case "admin":
				return is_admin();
			break;
			default:
				return true;
		}
	}

	/**
	 * _slug function.
	 *
	 * @return ->pluginSlug
	 *
	 * @since 1.0.0
	 */
	function _slug( $append = null )
	{
		$append = empty( $append )? "" : "_{$append}";
		return $this->pluginSlug . $append;
	}

	/**
	 * _version function.
	 *
	 * @return ->pluginVersion
	 *
	 * @since 1.0.0
	 */
	function _version()
	{
		return $this->pluginVersion;
	}

	/**
	 * _file function.
	 *
	 * @return ->pluginFile
	 *
	 * @since 1.0.0
	 */
	function _file()
	{
		return $this->pluginFile;
	}






	/**
	 * _new function.
	 *
	 * The _new function is used for instantiating new classes - it is needed for chaining to work
	 *
	 * @access private
	 * @param mixed $className
	 * @param array $arguments
	 *
	 * @return new class
	 *
	 * @since 1.0.0
	 */
	function _new( $className, $arguments = array() )
	{
		return new $className( $this, $arguments );
	}

	/**
	 * _framework function
	 *
	 * Function used for translation purposes
	 *
	 * @return framework version
	 *
	 * @since 1.0.0
	 */
	function _framework()
	{
		return "lavaPlugin";
	}

	/**
	 * _handle function.
	 *
	 *
	 *
	 * @access private
	 * @param mixed $what
	 * @param bool $reset
	 *
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function _handle( $what, $reset )
	{
		$pointer = "_" . strtolower( $what );
		if( !isset( $this->$pointer ) )
		{
			$this->$pointer = $this->_new( "lava$what" );
		}
		if( $reset == true )
		{
			return $this->$pointer->lavaReset();
		}
		else
		{
			return $this->$pointer->getThis();
		}
	}

	/**
	 * _ajax function.
	 *
	 * @return lavaAjaxHandlers
	 *
	 * @since 1.0.0
	 */
	function _ajax( $reset = true )
	{
		return $this->_handle( "AjaxHandlers", $reset );
	}

	/**
	 * _settings function.
	 *
	 * @return lavaSettings
	 *
	 * @since 1.0.0
	 */
	function _settings( $reset = true )
	{
		return $this->_handle( "Settings", $reset );
	}

	/**
	 * _skins function.
	 *
	 * @return lavaSkins
	 *
	 * @since 1.0.0
	 */
	function _skins( $reset = true )
	{
		return $this->_handle( "Skins", $reset );
	}

	/**
	 * _pages function.
	 *
	 * @return lavaPages
	 *
	 * @since 1.0.0
	 */
	function _pages( $reset = true)
	{
		return $this->_handle( "Pages", $reset );
	}

	/**
	 * _messages function.
	 *
	 * @return lavaPages
	 *
	 * @since 1.0.0
	 */
	function _messages( $reset = true)
	{
		return $this->_handle( "Messages", $reset );
	}

	/**
	 * _tables function.
	 *
	 * @return lavaTables
	 *
	 * @since 1.0.0
	 */
	function _tables( $reset = true)
	{
		return $this->_handle( "Tables", $reset );
	}

	function _misc( $reset = true )
	{
		return $this->_handle( "MiscFunctions", $reset);
	}

	function _vendor( $reset = true )
	{
		return $this->pluginVendor;
	}

}
?>