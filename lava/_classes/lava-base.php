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
class lavaBase
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
	 * @param array $arguments
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function __construct( $the_plugin, $arguments = array() )
	{
		$this->_the_plugin = $the_plugin;

		if( method_exists( $this, '_construct' ) )//call the sub classes construct argument
		{
			$callback = array( $this, '_construct' );
			call_user_func_array( $callback, $arguments );
		}

		$this->_registerHookMethods();
	}

	/**
	 * This method implements chaining (allows lavaPlugin method calls to be called from any class)
	 *
	 * @magic
	 * @param string $method_name
	 * @param array $arguments
	 * @return void
	 *
	 * @since 1.0.0
	 */
	function __call( $method_name, $arguments = array() )
	{
		/* Lets see whether we have a child */
		if( $this->_hasChild() ){
			//right, we have a child but does it have this method
				$child = $this->_getChild();
				if( method_exists( $child, $method_name ) ) {
					$callback = array( $child, $method_name );
					return call_user_func_array( $callback , $arguments)
				}
		}

		/* Lets see if we have any parents */
		 elseif( $this->_hasParent() ){
			//right, we have a parent but does it have this method
				$child = $this->_getChild();
				if( method_exists( $child, $method_name ) ) {
					$callback = array( $child, $method_name );
					return call_user_func_array( $callback , $arguments)
				}
		}

		/* Lets check whether we have _parent_ methods */
		elseif( method_exists( $this, "_parent_{$method_name}") ) {
			$callback = array( $this, "parent_{$method_name}" );
			return call_user_func_array( $callback, $arguments );
		}

		/* Check plugin instance */
		elseif( method_exists( $this->_the_plugin, $method_name ) ) {
			$callback = array( $this->_the_plugin, $method_name );
			return call_user_func_array( $callback, $arguments );
		}

		else {
			/* We couldn't find anywhere to send this request */

			if( $this->throw_error_if_method_is_missing ) {
				echo "<h2>LavaError thrown on line 110 of lavaBase.php</h2> <br/>";
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

	function _setReturnObject( $object = $this ) {
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
		return $this->_forget( '_child' )
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
		return $this->_forget( '_parent' )
	}



	/**
	 * Methods to access controller classes
	 */

	function _funcs( $remove_child = true ) {
		return $this->_functions( $remove_child );
	}

	function _functions( $remove_child = true ) {
		$class_name = "Functions";
		return $this->_the_plugin->_fetchSingleton( $class_name, $remove_child );
	}

	function _pages( $remove_child = true )
		$class_name = "Pages";
		return $this->_the_plugin->_fetchSingleton( $class_name, $remove_child );
	}

	function _settings( $remove_child = true )
		$class_name = "Settings";
		return $this->_the_plugin->_fetchSingleton( $class_name, $remove_child );
	}

	function _skins( $remove_child = true )
		$class_name = "Skins";
		return $this->_the_plugin->_fetchSingleton( $class_name, $remove_child );
	}



	/**
	 * Registers methods with hook names (e.g. _adminInit() ) to be called when that hook is called
	 */

	function _registerHookMethods() {
		if( $this->_should_register_hook_methods == true ) {
			$this->_funcs()->_registerHookMethods( $this );
		}
	}
































	//meant to be overridden - so a class can forward a request to something else
	function getThis() {
		return $this;
	}


	/**
	 * lavaContext function.
	 *
	 * adds/removes context
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 */
	final function lavaContext( $context = null, $handle = "current" )
	{
		if( null != $context)
		{
			$this->chain[ $handle ] = $context;
		}
		if( array_key_exists($handle, $this->chain) ) {
			return $this->chain[ $handle ];
		} else {
			return $this;
		}
	}

	/**
	 * lavaContext function.
	 *
	 * adds/removes context
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 */
	final function setContext( $context = null, $handle = "current" )
	{
		$this->chain[ $handle ] = $context;
	}

	/**
	 * lavaContext function.
	 *
	 * adds/removes context
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 */
	final function getContext( $handle = "current" )
	{
		if( array_key_exists( $handle, $this->chain ) )
		{
			return $this->chain[ $handle ];
		}
		return null;
	}

	/**
	 * withinContext function.
	 *
	 * Sets the parent handler (adds to chain for method lookups)
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 */
	final function withinContext( $context )
	{
		$this->setContext( $context, "parent" );

		return $this;
	}

	/**
	 * clearLavaContext function.
	 *
	 * adds/removes context
	 *
	 * @return $this
	 *
	 * @since 1.0.0
	 */
	final function clearContext( $handle = "current" )
	{
		$this->chain[ $handle ] = null;
	}

	/**
	 * lavaRemember function.
	 *
	 * The lavaRemember function stores data as a key>value pair as a protected property to a class
	 *
	 * @param string $key
	 * @param $value (default: null)
	 * @return $this || $value || false
	 *
	 * @since 1.0.0
	 */
	function lavaRemember( $key, $value = null )
	{
		$this->memory[ $key ] = $value;
		return $this;
	}

	function lavaRecall( $key, $default = null ) {
		if( array_key_exists( $key, $this->memory ) ) {
			return $this->memory[ $key ];
		} else {
			return $default;
		}
	}

	function lavaDestroy( $key ) {
		if( array_key_exists( $key, $this->memory ) ) {
			unset( $this->memory[ $key ] );
		}
	}

	function remember( $key, $value = null )
	{
		return $this->lavaRemember( $key, $value );
	}

	function recall( $key, $default = null ) {
		return $this->lavaRecall( $key, $default );
	}

	function forget( $key ) {
		return $this->lavaDestroy( $key );
	}



	function addWPAction( $hookTags, $methodNames = "", $priority = 10, $debug = false ) {
		if( !is_array( $hookTags ) ) {
			$hookTags = array( $hookTags );
		}
		if( !is_array( $methodNames ) ) {
			$methodNames = array( $methodNames );
		}
		foreach( $hookTags as $hookTag ) {

			foreach( $methodNames as $methodName ) {
				$_methodName = $methodName;
				if( empty( $_methodName) ) {
					$_methodName = $hookTag;
				}
				//if( $debug) { echo $hookTag; echo "<br>"; echo $_methodName;echo "<br>"; }
				add_action( $hookTag, array( $this, $_methodName ), $priority );
			}
		}
		//if( $debug ) exit;
	}

	function addWPFilter( $hookTags, $methodNames = "", $priority = 10, $args = 1 ) {
		if( !is_array( $hookTags ) ) {
			$hookTags = array( $hookTags );
		}
		if( !is_array( $methodNames ) ) {
			$methodNames = array( $methodNames );
		}
		foreach( $hookTags as $hookTag ) {

			foreach( $methodNames as $methodName ) {
				$_methodName = $methodName;
				if( empty( $_methodName) ) {
					$_methodName = $hookTag;
				}
				//if( $debug) { echo $hookTag; echo "<br>"; echo $_methodName;echo "<br>"; }
				add_filter( $hookTag, array( $this, $_methodName ), $priority, $args );
			}
		}
		//if( $debug ) exit;
	}

	function addAction( $hookTags, $methodNames = "", $priority = 10 ) {
		if( !is_array( $hookTags ) ) {
			$hookTags = array( $hookTags );
		}
		if( !is_array( $methodNames ) ) {
			$methodNames = array( $methodNames );
		}
		foreach( $hookTags as $hookTag ) {

			foreach( $methodNames as $methodName ) {
				$_methodName = $methodName;
				if( empty( $_methodName) ) {
					$_methodName = $hookTag;
				}
				add_action( $this->_slug( $hookTag ), array( $this, $_methodName ), $priority );
			}
		}
	}


	function addFilter( $hookTags, $methodNames = "", $priority = 10, $args = 1 ) {

		if( !is_array( $hookTags ) ) {
			$hookTags = array( $hookTags );
		}
		if( !is_array( $methodNames ) ) {
			$methodNames = array( $methodNames );
		}
		foreach( $hookTags as $hookTag ) {

			foreach( $methodNames as $methodName ) {
				$_methodName = $methodName;
				if( empty( $_methodName) ) {
					$_methodName = $hookTag;
				}
				add_filter( $this->_slug( $hookTag ), array( $this, $_methodName ), $priority, $args );
			}
		}
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

	function applyFilters( $hookTag, $argument = "", $args = null, $debug = false )
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
				$argument = apply_filters( $theHook, $argument, $args );
			}
		}

		return $argument;
	}

	 /**
	 * runFilters function.
	 *
	 * Runs the filters with all the parameters
	 *
	 * @param string $hookTag
	 * @param $args (default: null)
	 *
	 * @since 1.0.0
	 */
	function runFilters( $hookTag, $argument = "", $args = null, $debug = false )
	{
		return $this->applyFilters( $hookTag, $argument, $args, $debug );
	}

	function hookTags()
	{
		return array( " " );
	}
}
?>