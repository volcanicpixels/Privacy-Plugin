<?php
class lavaSettingsPage extends lavaPage
{
    public $multisiteSupport = true;
    public $who = "settings";

    function loadPage()
    {
        $this->saveSettings();
        $this->resetSettings();
		add_action( $this->_slug( "toolbar" ), array($this, "toolbarButtons") );
        //queue notifications
        //do redirect
    }

    function displayPage()
    {
        $settings = $this->_settings()->fetchSettings( $this->who );

        $this->doSettings( $settings );
    }
    
    function doSettings( $settings )
    {
        $settings = apply_filters( $this->_slug( $this->who . "settingsOrder" ), $settings );
        $hiddenForms = $this->runFilters( "hiddenForms", array() );
        foreach( $hiddenForms as $form )
        {
            ?>
            <form id="<?php echo $form['id'] ?>" class="invisible">
                <?php foreach( $form['fields'] as $field ): ?>
                    <input type="hidden" name="<?php echo $field['name'] ?>" value="<?php echo $field['value'] ?>" />
                <?php endforeach; ?>
            </form>
            <?php
        }
        echo '<form id="lava-settings-form" class="settings-wrap" method="post">';

        $this->runActions( "settingsHiddenInputs" );

        $this->runActions( "settingsPre" );

        foreach( $settings as $setting )
        {
            //action hook
            echo $setting->doSetting();
            //action hook
        }
        ?>
        <div class="lava-action-tray" style="margin-left:30px; margin-top:20px;">
            <input type="submit" class="lava-btn js-fallback" name="action" value="<?php _e( "Save Settings", $this->_framework() ) ?>" />
        </div>
        <?php
        echo '</form>';
    }

    function saveSettings()
    {
        if( !isset( $_REQUEST['setting-nonce'] ) )
        {//nothing being submitted
            return;
        }
        if( $_REQUEST['purpose'] != "save" )
        {
            //not saving
            return;
        }
        $referrer = wp_referer_field( false );
        $messageNonce = rand( 1000, 9999);
        $redirect = add_query_arg( "message_nonce", $messageNonce );

        if( is_network_admin() and !current_user_can( "manage_network_options") )
        {
            //Queue access denied message
            
        }
        else if( is_admin() and !current_user_can( "manage_options") )
        {
            //Queue access denied message
            
        }
        else
        {//user is authorized to do something
            $redirect = add_query_arg( "action_done", "saved", $redirect );
            if( is_network_admin() )
            {
                //do network save
            }
            elseif( is_admin() )
            {
                $theSettings = $_POST[ $this->_slug() ];
                foreach( $theSettings as $setting => $value )
                {
                    $value = stripslashes( $value );
                    $settingArray = explode( "/", $setting );
                    $this->_settings()
                            ->fetchSetting( $settingArray[1], $settingArray[0] )
                                ->updateValue( $value, true, true )
                    ;
                }
                $this->_settings()->updateCache();
            }
        
        }
        wp_redirect( $redirect );
        exit;
    }

    function resetSettings()
    {
        if( !isset( $_REQUEST['setting-nonce'] ) )
        {//nothing being submitted
            return;
        }
        if( $_REQUEST['purpose'] != "reset" )
        {
            //not resetting
            return;
        }
        $referrer = wp_referer_field( false );
        $messageNonce = rand( 1000, 9999);
        $redirect = add_query_arg( "message_nonce", $messageNonce );
        $redirect = add_query_arg( "action_done", "reset", $redirect );

        if( is_network_admin() and !current_user_can( "manage_network_options") )
        {
            //Queue access denied message
            
        }
        else if( is_admin() and !current_user_can( "manage_options") )
        {
            //Queue access denied message
            
        }
        else
        {//user is authorized to do something

            if( is_network_admin() )
            {
                //do network reset
            }
            elseif( is_admin() )
            {
                $resetScope = $_REQUEST[ 'reset-scope' ];

                switch( $resetScope )
                {
                    case "total":
                        //delete everything and run the plugin activated hook
                        delete_option( $this->_slug( "settings" ) );
                        delete_option( $this->_slug( "config" ) );
                        delete_option( $this->_slug( "messages" ) );
                }
            }
        
        }
        wp_redirect( $redirect );
        exit;
    }

	function toolbarButtons()
	{
		?>
		<div class="toolbar-block toolbar-overground js-only">
			<button class="lava-btn lava-btn-action lava-btn-inline lava-btn-action-red		lava-btn-form-submit" data-form="lava-settings-form"><?php _e( "Save Settings", $this->_framework() ) ?></button>
			<button class="lava-btn lava-btn-action lava-btn-inline lava-btn-action-white	lava-btn-form-submit lava-btn-confirmation not-implemented " data-form="lava-settings-reset"><?php _e( "Reset Settings", $this->_framework() ) ?></button>
		</div>
		<?php
	}

}
?>