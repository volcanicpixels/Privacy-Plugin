<?php
class lavaMiscFunctions extends lavaBase
{
    function current_context_url( $path )
    {
        if( is_multisite() and defined( 'WP_NETWORK_ADMIN' ) and WP_NETWORK_ADMIN == true )
        {
            return network_admin_url( $path );
        }
        return admin_url( $path );
    }
}
?>