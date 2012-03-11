<?php
/*
Title: Default with options
Author: Daniel Chatfield
*/
$thePlugin = lava::currentPlugin();
$skinUrl = $thePlugin->_skins()->getSkin( __FILE__ )->skinUrl();
$thePlugin->_skins()
    ->getSkin( __FILE__ )
    ->addSkinSetting( "logo" )
        ->setName( __( "Logo", $thePlugin->_slug() ) )
        ->setType( "image" )
        ->setDefault( $skinUrl . 'static/images/logo.png' )
    ->addSkinSetting( "enable_message" )
        ->setName( __( "Display a message", $thePlugin->_slug() ) )
        ->setType( "checkbox" )
        ->setDefault( "off" )
        ->settingToggle( "message" )
    ->addSkinSetting( "message" )
        ->setType( "textarea" )
        ->addTag( "no-margin" )
        ->addTag( "align-center" )
    ->addPresetSkinSetting( "custom_css" )
;
?>