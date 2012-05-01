<?php
/**
 * Pages
 *
 * @package Lava
 * @subpackage Pages
 * @author Daniel Chatfield
 *
 * @since 1.0.0
 */
class Lava_Pages extends Lava_Base
{
	protected $_admin_sections = array();
	protected $_admin_pages = array();
	protected $_page_types = array(
		'settings' => 'Settings'
	);


	function _construct() {
		$this->_add_action( 'admin_menu', '_register_sections', 2 );
		$this->_add_action( 'admin_menu', '_register_pages' );
	}









	function _add_section(  $section_title = 'Undefined Section', $section_id = 'default' ) {
		$section = array(
			'section_id' 	=> $section_id,
			'section_title' => $section_title
		);

		$this->_set_section( $section_id, $section );

		$this->_remember( '_section', $section_id );

		return $this->_r();
	}

	function _add_page( $page_id, $page_type = '', $section_id = null ) {
		$this->_kill_child();

		if( is_null( $section_id ) ){
			if( ! $this->_is_in_memory( '_section' ) )
				$this->_add_section( $this->_get_plugin_name(), $this->_get_plugin_id() );

			$section_id = $this->_recall( '_section' );
		}

		//if this section has no default page then lets register this

		$this->_set_section_default( $section_id, $page_id, false );


		if( ! $this->_page_exists( $page_id ) ) {
			if( array_key_exists( strtolower( $page_type ) , $this->_page_types ) )
				$class_name = $this->_page_types[ strtolower( $page_type ) ] . '_Page';
			else
				$class_name = 'Page';

			$args = array(
				$this,
				$page_id,
				$section_id
			);

			$the_page = $this->_admin_pages[ $page_id ] = $this->_instantiate_class( $class_name, $args );

		}


		$this->_set_child( $this->_admin_pages[ $page_id ] );
		return $this->_r();
	}

	function _get_page( $page_id ) {
		$this->_kill_child();
		if( $this->_page_exists( $page_id ) )
			$this->_set_child( $this->_admin_pages[ $page_id ] );
		return $this->_r();
	}

	function _page_exists( $page_id ) {
		if( array_key_exists( $page_id , $this->_admin_pages) )
			return true;
		else
			return false;
	}


	function _get_pages() {
		return $this->_admin_pages;
	}

	function _get_sections() {
		return $this->_admin_sections;
	}

	function _has_section( $section_id ) {
		if( array_key_exists( $section_id , $this->_admin_sections ) )
			return true;
		else
			return false;
	}

	function _get_section( $section_id ) {
		if( $this->_has_section( $section_id ) )
			return $this->_admin_sections[ $section_id ];
		else
			return null;
	}

	function _set_section( $section_id, $section ) {
		$this->_admin_sections[ $section_id ] = $section;
		return $this->_r();
	}

	function _set_section_default( $section_id = null, $page_id = null, $should_overwrite = true ) {
		if( is_null( $section_id ) )
			$section_id = $this->_recall( '_section' );
		if( is_null( $page_id ) )
			$page_id = $this->_get_child()->_get_page_id();
		$section = $this->_get_section( $section_id );
		if( $should_overwrite or ! array_key_exists( 'section_default_page' , $section) )
			$section[ 'section_default_page' ] = $page_id;

		$this->_set_section( $section_id, $section );
		return $this->_r();
	}






	function _add_settings_page( $page_id = 'settings' ) {
		$this->_add_page( $page_id, 'settings' )
				->_set_page_title( $this->__( 'Plugin Settingsss'/*Test*/ ) )
		;

		return $this->_r();
	}








	function _register_sections() {
		$sections = $this->_get_sections();

		foreach( $sections as $section ) {
			# @todo - add filter for capabilities (get minimum capabilities required)
			extract( $section );
			add_menu_page(
				'Undefined Page',
				$section_title,
				'manage_options',
				"{$section_id}_{$section_default_page}",
				array( $this, '_blank' ) 
			);
		}
	}
















	function addPageFromTemplate( $slug, $template )
	{
		return $this->addPage( $slug );
	}


	/**
	 * addAboutPage function.
	 *
	 * @access public
	 * @return void
	 */
	function addAboutPage( $slug = "about" )
	{
		$this   ->addPage( $slug, "About" )
					->setTitle( sprintf( __( "About %s", $this->_framework() ), $this->_name() ) );
		return $this;
	}

	/**
	 * addSettingsPage function.
	 *
	 * @access public
	 * @return void
	 */
	function addSettingsPage( $slug = "settings" )
	{
		$this   ->addPage( $slug, "Settings" )
					/* translators: This is the title of the settings page */
					->setTitle( __( "Plugin Settings", $this->_framework() ) )
		;

		$page = $this->fetchPage( $slug );

		$this	->_misc()
					->addPluginLink( __( 'Settings', $this->_framework() ), $page->getUrl() )
		;

		return $this;

		//add Link to plugin page


	}

	/**
	 * addSkinsPage function.
	 *
	 * @param string $slug (default: "skins") - to be appended to the plugin slug to make the url
	 * @return void
	 */
	function addSkinsPage( $slug = "skins" )
	{
		$this->_skins( false );

		$this   ->addPage( $slug, "Skins" )
					/* translators: This is the title of the settings page */
					->setTitle( __( "Skins", $this->_framework() ) )
		;

		return $this;
	}


	/**
	 * addTablePage function.
	 *
	 * @access public
	 * @param mixed $slug (default: "table") - to be appended to the plugin slug to make the url
	 * @return void
	 * @since 1.0.0
	 */
	function addTablePage( $slug = "table" )
	{
		$this   ->addPage( $slug, "Table" )
					->setTitle( __( "Table", $this->_framework() ) )
		;
		return $this;
	}





	/**
	 * defaultPage function.
	 *  Sets the currently chained page as the one to be displayed when the top-level page is clicked.
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function defaultPage()
	{
		if( isset( $this->chain[ "current" ] ) )
		{
			$this->defaultPage = $this->chain[ "current" ];
		}

		return $this;
	}

	/**
	 * registerPages function.
	 *  Registers each of the admin pages
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function registerPages()
	{
		$defaultPage = $this->defaultPage;
		//register the main page
		add_menu_page( $defaultPage->get( "title" ),  $this->_name(), $defaultPage->get( "capability" ), $defaultPage->get( "slug" ), array( $defaultPage, "doPage" ) );

		$parentSlug = $defaultPage->get( "slug" );

		//register each foreacheh
		

	}

	/**
	 * registerNetworkPages function.
	 *  Registers each of the admin pages
	 *
	 * @return void
	 * @since 1.0.0
	 */
	function registerNetworkPages()
	{
		$defaultPage = $this->defaultPage;
		//register the main page
		add_menu_page( $defaultPage->get( "title" ),  $this->_name(), $defaultPage->get( "capability" ), $defaultPage->get( "slug" ), array( $this, "blank" ) );

		$parentSlug = $defaultPage->get( "slug" );

		//register each subpage
		foreach( $this->adminPages as $page )
		{
			if( true === $page->multisiteSupport )//if they support multisite
			{
				$page->registerPage( $parentSlug );
			}
		}
	}





	function addStyle( $name, $path = "" )
	{
		$include = array(
			'path' => $path
		);

		$this->styles[ $name ] = $include;
		return $this;
	}

	function addScript( $name, $path = "", $dependencies = array() )
	{
		$include = array(
			'path' => $path,
			'dependencies' => $dependencies
		);

		$this->scripts[ $name ] = $include;
		return $this;
	}

	/**
	 * lavaPages::registerIncludes()
	 *
	 * @return void
	 */
	function registerIncludes()
	{
		foreach( $this->scripts as $name => $include )
		{
			$path		 = $include['path'];
			$dependencies = $include['dependencies'];

			if( !empty( $path ) )
			{
				if( strpos( $path, 'http' ) === false ) {
					$path = plugins_url( $path, $this->_file() );
				}
				wp_register_script( $name, $path, $dependencies );
			}
		}
		foreach( $this->styles as $name => $include )
		{
			$path = $include['path'];

			if( !empty( $path ) )
			{
				if( strpos( $path, "http" ) === false ) {
					$path = plugins_url( $path, $this->_file() );
				}
				wp_register_style( $name, $path );
			}
		}
	}




	function addCustomStyles()
	{
		$this->addStyle( $this->_slug( "Pluginstyles" ), "_static/styles.css" );
		return $this;
	}

	function addCustomScripts()
	{
		$this->addScript($this->_slug( "pluginScripts" ), "_static/scripts.js");
		return $this;
	}

}

?>