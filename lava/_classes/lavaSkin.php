<?php
/**
 * The lava Skin class
 * 
 * @package Lava
 * @subpackage lavaSkin
 * 
 * @author Daniel Chatfield
 * @copyright 2011
 * @version 1.0.0
 */
 
/**
 * lavaSkin
 * 
 * @package Lava
 * @subpackage LavaSkin
 * @author Daniel Chatfield
 * 
 * @since 1.0.0
 */
class lavaSkin extends lavaBase
{
    public $slug;
    public $author;
    public $name;
    public $templates = array();
    
    /**
     * lavaSkin::lavaConstruct( $slug )
     * 
     * This method is called by the __construct method of lavaBase and handles the construction
     * 
     * @return void
     * 
     * @since 1.0.0
     */
    function lavaConstruct( $slug )
    {
        $this->name = $this->slug = $slug;
    }


    /**
     * lavaSkin::parent_setName( $name )
     * 
     * It is correct to call this method with setName() - lavaBase handles the rest
     * 
     * @return #chain
     * 
     * @since 1.0.0
     */
    function parent_setName( $name )
    {

        $this->name = $name;

        return $this;
    }

    /**
     * lavaSkin::parent_setAuthor( $author )
     * 
     * It is correct to call this method with setAuthor() - lavaBase handles the rest
     * 
     * @return #chain
     * 
     * @since 1.0.0
     */
    function parent_setAuthor( $author )
    {
        $this->author = $author;

        return $this;
    }

    /**
     * lavaSkin::addTemplate( $slug )
     * 
     * 
     * @return #chain
     * 
     * @since 1.0.0
     */
    function addTemplate( $slug )
    {
        return $this;
    }

    /**
     * lavaSkin::addSkinSetting( $slug )
     * 
     * 
     * @return #chain
     * 
     * @since 1.0.0
     */
    function addSkinSetting( $slug )
    {
        $slug = $this->slug . "-" . $slug;
        $theSetting = $this->_settings()->withinContext( $this )->addSetting( $slug, "skins" )->bindData( "skin", $this->slug )->bindData( "setting-visibility", "visible" )->addTag( "setting-hidden" )->addTag( "skin-setting" );

        return $theSetting;//put it in context of lavaSettings with lavaSetting as child and lavaSkin as parent
    }

    function skinPath( $append = "" )
    {
        $path = dirname( $this->_file() ) . '/skins/'. $this->slug . '/' . $append;
        return $path;
    }

    function skinUrl( $append = "" )
    {
        return plugins_url( 'skins/' . $this->slug . '/' . $append, $this->_file() );
    }

	function getSlug()
	{
		return $this->slug;
	}

	function getName()
	{
		return $this->name;
	}

	function getAuthor()
	{
		return $this->author;
	}
}
?>