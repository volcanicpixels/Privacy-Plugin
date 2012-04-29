<?php
/**
 * Base class that all classes extend
 *
 * @package Lava
 * @subpackage Base
 * @author Daniel Chatfield
 *
 * @since 1.0.0
 */
class Lava_Base
{
	protected $_the_plugin;
	protected $_memory = array();
	public $_suffixes = array( '/pre', '', '/post' );//@deprecated
	/*
		If a method is called that doesn't exist an error will be chucked out
	*/
	public $_should_throw_error_if_method_is_missing = true;
	/* If this is true then some methods will get auto called at the appropriate time */
	public $_should_register_hook_methods = false;


	/**
	 * Stores the plugin instance into a property so that chaining can be implemented.
	 *
	 * @magic
	 * @param lavaPlugin $the_plugin
	 * @param array $args
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function __construct( $the_plugin, $args = array() )
	{
		$this->_the_plugin = $the_plugin;

		if( method_exists( $this, '_construct' ) )//call the sub classes construct method
		{
			$callback = array( $this, '_construct' );
			call_user_func_array( $callback, $args );
		}

		$this->_registerHookMethods();
	}

	/**
	 * This method implements chaining (allows lavaPlugin method calls to be called from any class)
	 *
	 * @magic
	 * @param string $method_name
	 * @param array $args
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function __call( $method_name, $args = array() )
	{
		/* Lets see whether we have a child */
		if( $this->_hasChild() ){
			//right, we have a child but does it have this method
				$child = $this->_getChild();
				if( method_exists( $child, $method_name ) ) {
					$callback = array( $child, $method_name );
					return call_user_func_array( $callback , $args);
				}
		}

		/* Lets see if we have any parents */
		 elseif( $this->_hasParent() ){
			//right, we have a parent but does it have this method
				$child = $this->_getChild();
				if( method_exists( $child, $method_name ) ) {
					$callback = array( $child, $method_name );
					return call_user_func_array( $callback , $args);
				}
		}

		/* Lets check whether we have _parent_ methods */
		elseif( method_exists( $this, "_parent_{$method_name}") ) {
			$callback = array( $this, "parent_{$method_name}" );
			return call_user_func_array( $callback, $args );
		}

		/* Check plugin instance */
		elseif( method_exists( $this->_the_plugin, $method_name ) ) {
			$callback = array( $this->_the_plugin, $method_name );
			return call_user_func_array( $callback, $args );
		}

		else {
			/* We couldn't find anywhere to send this request */

			if( $this->_should_throw_error_if_method_is_missing ) {
				echo "<h2>LavaError thrown in lavaBase.php</h2> <br/>";
				echo "Could not find method '{$method_name}' on object of class '" . get_class( $this ) . "'. We also tried the current child which has class '" . get_class( $this->_getChild() ) . "' and the parent which has class '" . get_class( $this->_getParent() ) . "'.";

				exit;
			} else {
				return $this->_getReturnObject();
			}
		}
	}

	/**
	 * Functions for adding, removing and retrieving data from the class
	 *
	 */

	function _isInMemory( $key ) {
		if( array_key_exists( $key, $this->_memory ) ) {
			return true;
		} else {
			return false;
		}
	}

	function _remember( $key, $value = null )
	{
		$this->_memory[ $key ] = $value;
		return $this;
	}

	function _recall( $key, $default = null ) {
		if( array_key_exists( $key, $this->_memory ) ) {
			return $this->_memory[ $key ];
		} else {
			return $default;
		}
	}

	function _forget( $key ) {
		if( array_key_exists( $key, $this->_memory ) ) {
			unset( $this->_memory[ $key ] );
		}
	}

	/**
	 * Methods for getting and setting the object that is returned if a method that doesn't exist is called.
	 */

	function _getReturnObject() {
		return $this->_recall( '_return_object', $this );
	}

	function _setReturnObject( $object = null ) {
		if( is_null( $object ) ) {
			$object = $this;
		}
		return $this->_remember( '_return_object', $object );
	}


	/**
	 * Accessor methods for family
	 */

	function _hasChild() {
		return $this->_isInMemory( '_child' );
	}

	function _getChild() {
		return $this->_recall( '_child' );
	}

	function _setChild( $child ) {
		return $this->_remember( '_child', $child );
	}

	function _killChild() {
		return $this->_forget( '_child' );
	}


	function _hasParent() {
		return $this->_isInMemory( '_parent' );
	}

	function _getParent() {
		return $this->_recall( '_parent' );
	}

	function _setParent( $parent ) {
		return $this->_remember( '_parent', $parent );
	}

	function _killParent() {
		return $this->_forget( '_parent' );
	}



	/**
	 * Registers methods with hook names (e.g. _adminInit() ) to be called when that hook is called
	 */

	function _registerHookMethods() {
		if( $this->_should_register_hook_methods == true ) {
			$this->_funcs()->_registerHookMethods( $this );
		}
	}


	/**
	 * Filter and action methods
	 */


	/**
	 * If the hook name is the same as the method then the method parameter can be ommitted
	 */
	function _add_action( $hooks, $methods = '', $priority = 10, $how_many_args = 0, $should_namespace = false, $is_filter = false ) {

		if( !is_array( $hooks ) )
			$hooks = array( $hooks );

		if( !is_array( $methods ) )
			$methods = array( $methods );

		foreach( $hooks as $hook ) {

			foreach( $methods as $method ) {

				if( empty( $method ) )
					$method = $hook;

				if( $should_namespace ) {
					$method = $this	->_namespace( $method );
					$hook = $this	->_namespace( $hook   );
				}

				$callback = $method;

				if( ! is_array( $callback ) ) {
					$callback = array( $this, $callback );
				}
				if( $is_filter )
					add_filter( $hook, $callback, $priority, $how_many_args );
				else
					add_action( $hook, $callback, $priority, $how_many_args );
			}
		}

		return $this;
	}



	function _add_filter( $hooks, $methods = '', $priority = 10, $how_many_args = 1, $should_namespace = false ) {
		return $this->_add_action( $hooks, $methods, $priority, $how_many_args, $should_namespace, true );
	}

	function _add_lava_action( $hooks, $methods = '', $priority = 10, $how_many_args ) {
		return $this->_add_action( $hooks, $methods, $priority, $how_many_args ,true );
	}

	function _add_lava_filter( $hooks, $methods = '', $priority = 10, $how_many_args = 1 ) {
		return $this->_add_filter( $hooks, $methods, $priority, $how_many_args, true );
	}

	function _do_action( $hooks, $args = array(), $should_namespace = false ) {

		if( ! is_array( $hooks ) )
			$hooks = array( $hooks );

		foreach ( $hooks as $hook ) {

			if( $should_namespace ) {
				$hook = $this->_namespace( $hook );
			}
			$callback = "do_action";

			$_args = $args;
			array_unshift( $_args, $hook );

			call_user_func_array( $callback , $_args );
		}

		return $this;
	}

	function _do_lava_action( $hooks, $args = array() ) {
		return $this->_do_action( $hooks, $args, true );
	}

	function _apply_filters_( $hooks, $value, $args = array(), $should_namespace = false ) {
		if( ! is_array( $hooks ) )
			$hooks = array( $hooks );

		foreach ( $hooks as $hook ) {

			if( $should_namespace ) {
				$hook = $this->_namespace( $hook );
			}
			$callback = "apply_filters";

			$_args = $args;
			array_unshift( $_args, $value );
			array_unshift( $_args, $hook );

			$value = call_user_func_array( $callback , $_args );
		}

		return $value;
	}

	function _apply_filters( $hooks, $value = '' ) {
		$args = func_get_args();

		if( func_num_args() >= 2 ) {
			unset( $args[0] );
			unset( $args[1] );
		}

		return $this->_apply_filters_( $hooks, $value, $args, false );
	}

	function _apply_lava_filters( $hooks, $value = '' ) {
		$args = func_get_args();

		if( func_num_args() >= 2 ) {
			unset( $args[0] );
			unset( $args[1] );
		}

		return $this->_apply_filters_( $hooks, $value, $args, true );
	}
	


























	/**
	 * runActions function.
	 *
	 * Runs the actions with all the parameters
	 *
	 * @param string $key
	 * @param $value (default: null)
	 *
	 * @since 1.0.0
	 */
	function runActions( $hookTag, $debug = false )
	{
		$hooks = array_unique( $this->hookTags() );
		$suffixes = array_unique( $this->suffixes );

		foreach( $suffixes as $suffix)
		{
			foreach( $hooks as $hook )
			{
				if( $hook == " " ) {
					$hook = "";
				} else {
					$hook = "-".$hook;
				}
				if( $debug )
				{
					echo $this->_slug( "{$hookTag}{$hook}{$suffix}" ) . "\n";
				}
				do_action( $this->_slug( "{$hookTag}{$hook}{$suffix}" ), $this );
			}
		}
	}

	function applyFilters( $hookTag, $value = "", $args = null, $debug = false )
	{
		if( is_null( $args ) ) {
			$args = $this;
		}

		$hooks = array_unique( $this->hookTags() );
		$suffixes = array_unique( $this->suffixes );

		foreach( $suffixes as $suffix)
		{
			foreach( $hooks as $hook )
			{
				if( $hook == " " ) {
					$hook = "";
				} else {
					$hook = "-".$hook;
				}
				//echo( $this->_slug( "{$hookTag}{$hook}{$suffix}" ). "<br/>" );
				$theHook = $this->_slug( "{$hookTag}{$hook}{$suffix}" );
				if( $debug ){ echo( "$theHook<br>" ); }
				$value = apply_filters( $theHook, $value, $args );
			}
		}

		return $value;
	}


	function hookTags()
	{
		return array( " " );
	}
}
?>