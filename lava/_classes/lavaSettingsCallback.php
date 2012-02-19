<?php
/**
 * The lava Settings Callback class
 * 
 * This class has all the callback methods involved with settings
 * 
 * @package Lava
 * @subpackage lavaSettingsCallback
 * 
 * @author Daniel Chatfield
 * @copyright 2011
 * @version 1.0.0
 */
 
/**
 * lavaSettingsCallback
 * 
 * @package Lava
 * @subpackage LavaSettingsCallback
 * @author Daniel Chatfield
 * 
 * @since 1.0.0
 */
class lavaSettingsCallback extends lavaBase
{
    /**
     * lavaSettingsCallback::lavaConstruct()
     * 
     * This method is called by the __construct method of lavaBase and handles the construction
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function lavaConstruct()
    {
        //settingActions
        $hookTag = "settingActions";
        add_filter( $this->_slug( "{$hookTag}-type/password" ), array( $this, "addShowPassword" ) );
        add_filter( $this->_slug( "{$hookTag}" ), array( $this, "addResetToDefault" ) );

        //settingControl
        $hookTag = "settingControl";
        add_filter( $this->_slug( "{$hookTag}-type/timeperiod" ), array( $this, "addTimePeriodSelector" ), 10, 2 );
        add_filter( $this->_slug( "{$hookTag}-type/password" ), array( $this, "addPasswordWrapper" ), 10, 2 );
        add_filter( $this->_slug( "{$hookTag}-type/checkbox" ), array( $this, "addCheckboxUx" ), 10, 2 );
        add_filter( $this->_slug( "{$hookTag}-type/text" ), array( $this, "addTextWrapper" ), 10, 2 );
        add_filter( $this->_slug( "{$hookTag}-type/select" ), array( $this, "addSelectUx" ), 10, 2 );

        //settingsHiddenInputs
        $hookTag = "settingsHiddenInputs";
        add_action( $this->_slug( "{$hookTag}"), array( $this, "nonces") );
    }


    /**
     * lavaSettingsCallback::addResetToDefault()
     * 
     * Adds the "reset to default" html to the setting actions
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function addResetToDefault( $settingActions )
    {
        $settingActions .=      '<span class="action js-only reset-setting flex-3">' . __( "Reset to default", $this->_framework() ) . '</span>'.
                                '<span style="display:none" class="action js-only undo-reset-setting flex-3">' . __( "Undo Reset", $this->_framework() ) . '</span>';
        return $settingActions;
    }

    /**
     * lavaSettingsCallback::addShowPassword()
     * 
     * Adds the "show password" html to the setting actions
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function addShowPassword( $settingActions )
    {
        $settingActions =      '<span class="js-only action show-password-handle flex-1">' . __( "Show Password", $this->_framework() ) . '</span>'.
                                '<span style="display:none" class="js-only action hide-password-handle flex-1">' . __( "Hide Password", $this->_framework() ) . '</span>'.$settingActions;
        return $settingActions;
    }

    /**
     * lavaSettingsCallback::addTimePeriodSelector()
     * 
     * Adds the "show password" html to the setting actions
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function addTimePeriodSelector( $settingControl, $theSetting )
    {
        $seconds = $theSetting->getValue( true );

        $selectedAttr = 'selected="selected"';

        $weeksSelected = $daysSelected = $hoursSelected = $minutesSelected = "";
        if( $seconds % ( 60 * 60 * 24 * 7 ) == 0 )
        {
            $weeksSelected = $selectedAttr;
            $theValue = round( $seconds / ( 60 * 60 * 24 * 7 ) );
        }
        elseif( $seconds % ( 60 * 60 * 24 ) == 0 )
        {
            $daysSelected = $selectedAttr;
            $theValue = round( $seconds / ( 60 * 60 * 24 ) );
        }
        elseif( $seconds % ( 60 * 60 ) == 0 )
        {
            $hoursSelected = $selectedAttr;
            $theValue = round( $seconds / ( 60 * 60  ) );
        }
        else
        {
            $minutesSelected = $selectedAttr;
            $theValue = round( $seconds / 60 );
        }
        $settingControl .=  '<div class="input-cntr show-status clearfix js-only">'.
                                '<div class="validation" data-state="not-invoked"></div>'.
                                '<input class="time-period-ux" type="text" value="' . $theValue . '"/>'.
                            '</div>'.
                            
                            '<select class="scale-selector js-only">'.
                                '<option ' . $minutesSelected . ' value="' . 60 . '" >' . __( "Minutes"/* used as part of an input "[input] Minutes" */, $this->_framework() ) . '</option>'.
                                '<option ' . $hoursSelected . ' value="' . 60 * 60 . '" >' . __( "Hours"/* used as part of an input "[input] Hours" */, $this->_framework() ) . '</option>'.
                                '<option ' . $daysSelected . ' value="' . 60 * 60 * 24 . '" >' . __( "Days"/* used as part of an input "[input] Days" */, $this->_framework() ) . '</option>'.
                                '<option ' . $weeksSelected . ' value="' . 60 * 60 * 24 * 7 . '" >' . __( "Weeks"/* used as part of an input "[input] Weeks" */, $this->_framework() ) . '</option>'.
                            '</select>';
        return $settingControl;
    }

    /**
     * lavaSettingsCallback::addPasswordWrapper()
     * 
     * Adds the wrapping html to the password input
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function addPasswordWrapper( $settingControl, $theSetting )
    {
        $placeholder = 'placeholder="'. $theSetting->properties['placeholder'] .'"';
        $settingControl =  '<div class="input-cntr show-status clearfix" data-show="password">'.
                                '<div class="validation" data-state="not-invoked"></div>'.
                                '<input '.$placeholder.' type="text" class="password-show" value="' . $theSetting->getValue( true ) . '"/>'.
                                $settingControl.
                            '</div>';
        return $settingControl;
    }

    /**
     * lavaSettingsCallback::addTextWrapper()
     * 
     * Adds the wrapping html to the text input
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function addTextWrapper( $settingControl, $theSetting )
    {
        $settingKey = $theSetting->getKey();
        $settingWho = $theSetting->who;
        $pluginSlug =  $this->_slug();
        $settingInputName = "{$pluginSlug}[{$settingWho}/{$settingKey}]";
        $settingInputID = "{$pluginSlug}-{$settingWho}-{$settingKey}";

        $placeholder = 'placeholder="'. $theSetting->properties['placeholder'] .'"';
        $settingControl =  '<div class="input-cntr show-status clearfix">'.
                                '<div class="validation" data-state="not-invoked"></div>'.
                                '<input id="' . $settingInputID . '" name="' . $settingInputName . '"  '.$placeholder.' type="text" value="' . $theSetting->getValue( true ) . '"/>'.
                            '</div>';
        return $settingControl;
    }

    /**
     * lavaSettingsCallback::addCheckboxUx()
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function addCheckboxUx( $settingControl, $theSetting )
    {
        $checked = "unchecked";
        if( $theSetting->getValue( true ) == "on")
        {
            $checked = 'checked';
        }
        $settingControl .=  '<div title ="' . __( /* In context of a checkbox slider */"Click to enable/disable ", $this->_framework() ) . '" class="js-only tiptip checkbox-ux '.$checked.'"></div>';
        return $settingControl;
    }

	/**
     * lavaSettingsCallback::addSelectUx()
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function addSelectUx( $settingControl, $theSetting )
    {
		$settingKey = $theSetting->getKey();
        $settingWho = $theSetting->who;
        $pluginSlug =  $this->_slug();
        $settingInputName = "{$pluginSlug}[{$settingWho}/{$settingKey}]";
        $settingInputID = "{$pluginSlug}-{$settingWho}-{$settingKey}";
		$options = $theSetting->getProperty( "setting-options" );
		$value = $theSetting->getValue();

		if( !is_array($options) ) {
			$options = array();
		}

		$settingControl = '<select id="' . $settingInputID . '" name="' . $settingInputName . '" >';
								foreach( $options as $option ) {
									$selected = 'data-bob="test"';
									if( $value == $option['value'] ) {
										$selected = 'selected="selected"';
									}
									$settingControl .= '<option ' . $selected . ' value="' . $option['value'] . '" >' . $option['name'] . '</option>';
								}
		$settingControl .='</select>';
        return $settingControl;
    }

    /**
     * lavaSettingsCallback::nonces()
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function nonces()
    {
        wp_nonce_field();//set the referrer field
        $capabilities = array(
            "manage_options" => "setting-nonce"
        );
        $otherNonces = array(
            "purpose" => "save",
            "reset-scope" => "total"
        );
        if( is_network_admin() )
        {
            $capabilities["manage_network_options"] = "network-setting-nonce";
        }
        foreach( $capabilities as $capability => $name )
        {
            if( current_user_can( $capability ) )
            {
                $action = $this->_slug( $name );
                wp_nonce_field( $action, $name, false );
            }
        }
        foreach( $otherNonces as $name => $value )
        {
            echo "<input class=\"lava-form-$name\" type=\"hidden\" name=\"$name\" value=\"$value\" />";
        }
    }

    /**
     * lavaSettingsCallback::removeActions()
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function removeActions( $settingActions, $theSetting )
    {
        $settingActions = "";
        return $settingActions;
    }

    
    /**
     * lavaSettingsCallback::()
     * 
     * @return void
     * 
     * @since 1.0.0
     *
    function (  )
    {
        
    }*/
}
?>