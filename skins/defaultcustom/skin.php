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
    ->addSkinSetting( "background_color" )
        ->setName( __( "Background Colour", $thePlugin->_slug() ) )
        ->setType( "color" )
        ->setDefault( "#F9F9F9" )
    ->addPresetSkinSetting( "custom_css" )
;
?>