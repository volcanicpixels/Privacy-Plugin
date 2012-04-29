<?php
/**
 * The main lava class.
 *
 * @package Lava
 *
 * @author Daniel Chatfield
 * @copyright 2012
 * @version 1.0.0
 *
 */


if( !class_exists( "Lava" ) ):
class Lava
{

	private static $_plugin_instances = array();
	private static $_current_plugin;

	/**
	 * @static
	 * @param string $plugin_file
	 * @param string $plugin_name
	 * @param float $plugin_version
	 * @return lavaPlugin
	 *
	 * @since 1.0.0
	 */
	static function _new_plugin( $plugin_file_path = __file__, $plugin_name = "Some Plugin", $plugin_version = 1 )
	{
		if( !class_exists( "Lava_Plugin" ) )
		{
			require_once( dirname( __FILE__ ) . "/_classes/lava-plugin.php" );
		}


		$plugin_id = strtolower( str_replace( " ", "_", $plugin_name ) );

		if( !isset( self::$_plugin_instances[ $plugin_id ] ) )
		{
			self::$_plugin_instances[ $plugin_id ] = new Lava_Plugin( $plugin_file_path, $plugin_name, $plugin_version );
		}

		return self::$_plugin_instances[ $plugin_id ];

	}

	/**
	 * The fetchPlugin function returns the specified plugin instance or false if it has not been declared. This function should be used within a callback to ensure all plugins have been defined.
	 *
	 * @access public
	 * @static
	 * @param mixed $plugin_name
	 * @return lavaPlugin
	 *
	 * @since 1.0.0
	 */
	static function _get_plugin( $plugin_name )
	{
		$plugin_id = strtolower( str_replace( " ", "_", $plugin_id ) );

		if( isset( self::$_instances[ $plugin_id ] ) )
		{
			return self::$_instances[ $plugin_id ];
		}

		return false;
	}

	static function _plugin_exists( $plugin_name )
	{
		$plugin_id = strtolower( str_replace( " ", "_", $plugin_name ) );

		if( isset( self::$_instances[ $plugin_id ] ) )
		{
			return true;
		}

		return false;
	}

	static function _set_current_plugin( $the_plugin )
	{
		
		self::$_current_plugin = $the_plugin;
	}

	static function _get_current_plugin( $the_plugin )
	{
		return self::$_current_plugin;
	}
}

endif;

?>