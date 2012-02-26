<?php
class lavaMiscFunctions extends lavaBase
{
	function lavaConstruct() {
		$this->addAutoMethods();
	}

    function current_context_url( $path )
    {
        if( is_multisite() and defined( 'WP_NETWORK_ADMIN' ) and WP_NETWORK_ADMIN == true )
        {
            return network_admin_url( $path );
        }
        return admin_url( $path );
    }

    function addAutoMethods() {
    	$objects = array(
    		$this,
    		$this->_this()->pluginCallbacks,
    		$this->_ajax(),
    		$this->_skins()
    	);
    	$autoHooks = array(
			"init" => "init",
			"admin_init" => "adminInit"
		);

		foreach( $objects as $object ) {
			foreach( $autoHooks as $hookTag => $actions ) {
				if( !is_array( $actions ) ) {
					$actions = array( $actions );
				}
				foreach( $actions as $action ) {
					if( method_exists( $object, $action ) ) {
						$callback = array( $object, $action ); 
						add_action( $hookTag, $callback );
					}
				}
			}
		}
    }

    function _registerActions() {
    	$hooks = array();

    	foreach( $hooks as $hook ) {
    		add_action( $hook, array( $this, $hook ) );
    	}
    }

    
}
?>