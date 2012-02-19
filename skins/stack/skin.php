<?php
/*
Title: Stack
Author: Daniel Chatfield
*/
$thePlugin = lava::currentPlugin();
$thePlugin->_skins()
    ->getSkin( __FILE__ )
    ->addSkinSetting( "logo" )
        ->setName( __( "Logo", $thePlugin->_slug() ) )
        ->setType( "image" )
    ->addSkinSetting( "background_color" )
        ->setName( __( "Background Colour", $thePlugin->_slug() ) )
        ->setType( "color" )
        ->setDefault( "#F9F9F9" )
;
?>