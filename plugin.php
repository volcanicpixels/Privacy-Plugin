<?php
/*
Plugin Name: Private Blog
Plugin URI: http://www.volcanicpixels.com/password-protect-wordpress-plugin/
Description: Private Blog is a wordpress plugin which allows you to password protect all of your wordpress blog including all posts and feeds with a single password.
Version: 4.01
Author: Daniel Chatfield
Author URI: http://www.volcanicpixels.com
License: GPLv2
*/
?>
<?php
//error_reporting(E_ALL);//used for debug only (NOT PRODUCTION)
include( dirname( __FILE__ ) ."/lava/lava.php" );

$pluginName = "Private Blog";
$pluginVersion = "4.01";

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
        ->addTag( "is-premium" )
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
        ->addTag( "is-premium" )
    ->addSetting( "logout_link" )
        ->setName( __( "Add Logout link to navigation", $pluginSlug ) )
        ->setType( "checkbox" )
        ->setDefault( "off" )
        ->setHelp( __( "When enabled, the plugin will attempt to put a logout link in the navigation", $pluginSlug ) )
        ->addTag( "is-premium" )
        ->settingToggle( "logout_link_menu" )
	->addSetting( "logout_link_menu" )
        ->setType( "select" )
		->addTag( "no-margin" )
	->addSetting( "rss_feed_visible" )
		->setName( __( "Make RSS Feeds public", $pluginSlug ) )
		->setType( "checkbox" )
		->setDefault( "off" )
		->setHelp( __( "When enabled, the RSS feed (which contains post content) will be publicly available", $pluginSlug ) )
        ->addTag( 'is-premium' )
    ->addSetting( "record_logs" )
        ->setName( __( "Create a log of all logins and logouts", $pluginSlug ) )
        ->setType( "checkbox" )
        ->setDefault( "off" )
        ->addTag( "is-premium" )
        ->setHelp( __( "When enabled, every attempt to login will be logged", $pluginSlug ) )
;


$thePlugin->_tables()
    ->addTable( "access_logs" )
        ->addField( "id" )
            ->setType( "mediumint" )
            ->setMaxLength( 9 )
            ->setAutoIncrement( true )
        ->addField( "timestamp" )//timestamp of entry
            ->setType( "timestamp" )
        ->addField( "password" )// the number of the password used (0 if NA)
        ->addField( "password_name" )//The name of that password at the time of entry
        ->addField( "password_color" )//The color of the password at time of entry
        ->addField( "action" )//The action (login, logout, login attempt)
        ->addField( "user_agent")//The user agent
            ->setType( "text" )
        ->addField( "device" )
        ->addField( "browser" )//The browser (as pmdarsed at time of entry)
        ->addField( "operating_system" )//The OS (as parsed at time of entry)
        ->addField( "ip_address" )
;


$thePlugin->_pages()
    ->addScript( $thePlugin->_slug( "uservoice" ), "http://widget.uservoice.com/tVw9FecEfqZnVhHj01zqsw.js" ) 
    ->addSettingsPage()
    ->addSkinsPage()
        ->setTitle( __( "Login Page skin", $pluginSlug ) )
    ->addPage( "access_logs", "PrivateBlogAccessLogs" )
        ->setTitle( __( "Access Logs", $pluginSlug ) )
        ->setDataSource( "access_logs" )
        ->setDisplayOrder( "timestamp;action;password_name;browser;operating_system;device;ip_address" )
        ->setOrderBy( "timestamp DESC" )/*
    ->addPageFromTemplate( "custom", "custom" )
        ->setTitle( __( "Plugin Customisations", $pluginSlug ) )*/
;

$thePlugin->_pages()
    ->addCustomScripts()
    ->addCustomStyles()
;

?>