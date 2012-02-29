<?php
/*
Plugin Name: Private Blog
Plugin URI: http://go.volcanicpixels.com/privacy-plugin/
Description: Private Blog is a wordpress plugin which allows you to password protect all of your wordpress blog including all posts and feeds with a single password.
Version: 4.0 beta
Author: Daniel Chatfield
Author URI: http://www.volcanicpixels.com
License: GPLv2
*/
?>
<?php
error_reporting(E_ALL);
include( dirname( __FILE__ ) ."/lava/lava.php" );

$pluginName = "Private Blog";
$pluginVersion = "4.0 beta";

$thePlugin = lava::newPlugin( __FILE__, $pluginName, $pluginVersion );
$pluginSlug = $thePlugin->_slug();


/**
 * Define the plugin settings:
 *      Enabled
 *      Multiple Passwords
 *      Passwords
 *      Login Duration
 *      Add logout button
 */
global $maxPasswords;
$maxPasswords = 10;

$thePlugin->_settings()     
    ->addSetting( "enabled" )
        ->setName( __( "Enable Password Protection", $pluginSlug ) )
        ->setType( "checkbox" )
        ->setDefault( "on" )
        ->setHelp( __( "When enabled visitors to your site will need to login to access it.", $pluginSlug ) )
    ->addSetting( "multiple_passwords" )
        ->setName( __( "Enable multiple passwords", $pluginSlug ) )
        ->setType( "checkbox" )
        ->setDefault( "off" )
        ->setHelp( sprintf( __( "When enabled, upto %s different passwords can be set.", $pluginSlug ), 10 ) )
;


for( $i = 1; $i <= $maxPasswords; $i++ )
{
    $default = ( 1 == $i )? "password" : "";//set the default for the first password and leave the rest blank
    $name = ( 1 == $i )? __( "Password", $pluginSlug ) : ""; //set the name for the first password and leave the rest blank
    $namePlural = __( "Passwords", $pluginSlug );
    $tag = ( 1 != $i )? "multi-password" : "";//add the "multi-pasword" tag to all the passswords except number 1
    
    $colourArray = array(
        "#26d2e1",//light blue
        "#e10808",//red
        "#e17812",//orange
        "#a4e19c",//light green
        "#FEDA71", //light yellow
        "#f0518b", //pink
        "#5d5042", //turd
        "#ab6fd1", //purple
        "#69aeb4", //turqoise
        "#97dd10" //grass green
    );
    $numberColours = count( $colourArray );
    $colour = $colourArray[ ($i - 1) % $numberColours ];//cycle through the pre-defined colours. Flexible code allows for more colours to be defined easily and more passwords.
    
    $thePlugin->_settings()
        ->addSetting( "password".$i."_value" )
            ->setName( $name )
            ->setType("password")
            ->setDefault( $default )
            ->setProperty('placeholder', __( "Leave blank to disable", $pluginSlug ) )
            ->addTag( $tag )//makes it easy to select all multi password settings
            ->addTag( "password-label" )
            ->bindData( "name-singular", $name )
            ->bindData( "name-plural", $namePlural )
            ->bindData( "pass-short-name", "password".$i )
        ->addSetting( "password".$i."_name" )
            ->setType("text")
            ->setDefault( $i )
            ->setVisibility( false )
        ->addSetting( "password".$i."_colour" )
            ->setType("text")
            ->setDefault( $colour )
            ->setVisibility( false )
    ;
}

$defaultTimeout = 60*60*24;//1 day

$thePlugin->_settings()
    ->addSetting( "timeout_length" )
        ->setName( __( "Duration that user stays logged in", $pluginSlug ) )
        ->setType( "timeperiod" )
        ->setHelp( __( "The length of inactivity before the user must login again. Set to 0 to timeout when browser closes.", $pluginSlug ) )
        ->setDefault( $defaultTimeout )
    ->addSetting( "logout_link" )
        ->setName( __( "Add Logout link to navigation", $pluginSlug ) )
        ->setType( "checkbox" )
        ->setDefault( "off" )
        ->setHelp( __( "When enabled, the plugin will attempt to put a logout link in the navigation", $pluginSlug ) )
        ->addTag( "labs" ) //display a warning that this feature is experimental
	->addSetting( "logout_link_menu" )
        ->setType( "select" )
		->addTag( "no-margin" )
	->addSetting( "rss_feed_visible" )
		->setName( __( "Make RSS Feeds public", $pluginSlug ) )
		->setType( "checkbox" )
		->setDefault( "off" )
		->setHelp( __( "When enabled, the RSS feed (which contains post content) will be publicly available", $pluginSlug ) )
;

/*
$thePlugin->_tables()
    ->addTable( "access_logs" )
        ->addField( "timestamp" )//timestamp of entry
            ->type( "timestamp" )
        ->addField( "password" )// the number of the password used (0 if NA)
            ->type( "int" )
        ->addField( "password_name" )//The name of that password at the time of entry
        ->addField( "password_color" )//The color of the password at time of entry
        ->addField( "password_attempt" )//The password used if unsuccessful
        ->addField( "action" )//The action (login, logout, login attempt)
        ->addField( "user_agent")//The user agent
        ->addField( "browser" )//The browser (as parsed at time of entry)
        ->addField( "operating_system" )//The OS (as parsed at time of entry)
        ->addField( "country_code" )//The country code (as parsed at time of entry)
        ->addField( "ip_address" )
;*/


$thePlugin->_pages()
    ->addScript( $thePlugin->_slug( "uservoice" ), "http://widget.uservoice.com/tVw9FecEfqZnVhHj01zqsw.js" ) 
    ->addSettingsPage()
    ->addSkinsPage()
        ->setTitle( __( "Login Page skin", $pluginSlug ) )
		/*
    ->addTablePage( "access_logs" )
        ->setTitle( __( "Access Logs", $pluginSlug ) )
        ->setDataSource( "access_logs" )
        ->setDisplayOrder( "action;password;password_name;password_color;password_attempt;browser;operating_system;country_code;ip_address" )
    ->addPageFromTemplate( "custom", "custom" )
        ->setTitle( __( "Plugin Customisations", $pluginSlug ) )*/
;

$thePlugin->_pages()
    ->addCustomScripts()
    ->addCustomStyles()
;
?>