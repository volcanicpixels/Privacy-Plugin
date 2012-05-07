<?php
/**
 * Page
 *
 * @package Lava
 * @subpackage Page
 * @author Daniel Chatfield
 *
 * @since 1.0.0
 */
class Lava_Page extends Lava_Base
{
	protected $_is_network_page = false;

	protected $_page_controller;
	protected $_page_id;
	protected $_section_id;

	protected $_page_hook;

	public $_page_styles = array();
	public $_page_scripts = array();

	function _construct( $page_controller, $page_id, $section_id ) {
		$this->_page_controller = $page_controller;
		$this->_page_id = $page_id;
		$this->_section_id = $section_id;

		$this->_set_return_object( $this->_page_controller );

		$this->_add_action( 'admin_menu', '_register_page', 3 );
	}



	function _get_section_id() {
		return $this->_section_id;
	}

	function _get_page_id() {
		return $this->_page_id;
	}

	function _get_page_url() {
		$page_id = $this->_get_page_id();
		if( $this->_is_network_page and function_exists( 'network_admin_url' ) )
			return network_admin_url( "admin.php?page={$slug}" );
		else
			return admin_url( "admin.php?page={$slug}" );
	}

	function _get_page_title() {
		return $this->_recall( '_page_title', 'Undefined Page' );
	}

	function _get_menu_title() {
		return $this->_recall( '_menu_title', $this->_get_page_title() );
	}

	function _set_menu_title( $menu_title ) {
		$this->_remember( '_menu_title', $menu_title );
		return $this->_r();
	}

	function _get_page_slug() {
		return $this->_get_section_id() . '_' . $this->_get_page_id();
	}



	function _set_page_title( $page_title ) {
		$this->_remember( '_page_title', $page_title );
		return $this->_r();
	}







	function _register_page() {

		$parent_slug = $this->_page_controller->_get_section_slug( $this->_section_id );
		$page_title = $this->_get_page_title();
		$menu_title = $this->_get_menu_title();
		$capability = 'manage_options'; # @todo add capability handling
		$menu_slug = $this->_get_page_slug();
		$function = array( $this, '_blank' );


		$page_hook = add_submenu_page(
			$parent_slug,
			$page_title,
			$menu_title,
			$capability,
			$menu_slug,
			$function
		);

	}

	function _do_page() {
		
	}










	function enqueueIncludes()
	{
		foreach( $this->_pages()->styles as $name => $notNeeded )
		{
			wp_enqueue_style( $name );
		}
		foreach( $this->_pages()->scripts as $name => $notNeeded )
		{
			wp_enqueue_script( $name );
		}
	}

	function doPage()
	{
		$this->displayHeader();
		$this->displayNotifications();
		$this->displayPage();
		$this->displayFooter();
	}

	function displayHeader()
	{
		$pluginSlug = $this->_slug();
		$pluginName = $this->_name();
		$pluginVersion = $this->_version();

		$page_hook = $_GET['page'];
		$lavaPageClass = apply_filters( "admin_page_class-{$pluginSlug}", "" );
		$lavaPageClass = apply_filters( "admin_page_class-{$page_hook}", $lavaPageClass );

		?>
		<script type="text/javascript">

		  var _gaq = _gaq || [];
		  _gaq.push(['_setAccount', 'UA-29306585-1']);
		  _gaq.push(['_setDomainName', 'example.com']);
		  _gaq.push(['_setAllowLinker', true]);
		  _gaq.push(['_trackPageview']);

		  (function() {
			var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
			ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
			var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
		  })();

		</script>
		<div class="lava-full-screen-loader">
			<div class="lava-loader loading">
				<span class="child1"></span>
				<span class="child2"></span>
				<span class="child3"></span>
				<span class="child4"></span>
				<span class="child5"></span>
			</div>
		</div>
		<div class="wrap">
			<div class="lava-header" style="margin-bottom:10px;">
				<div id="icon-options-general" class="icon32"></div>
				<h2>
					<?php echo $pluginName; ?> <span class="version"><?php echo $pluginVersion; ?></span>
					<span class="lava-ajax-checks">
						<?php $this->runActions( "ajaxChecks" ); ?>
					</span>
				</h2>

			<!--.lava-header END-->
			</div>
			<div id="lava-nav" class="lava-nav bleed-left bleed-right with-padding lava-sticky-top clearfix">
				<div class="sticky-toggle tiptip" title="Toggle whether this bar should stick to the top of the screen."></div>
				<div class="left-grad"></div>
				<ul class="nav nav-horizontal clearfix">
					<?php foreach( $this->_pages( false )->adminPages() as $page ): ?>
				   <li class="clearfix <?php echo $page->get( "slug" ); ?> <?php if( $page_hook == $page->get( "slug" ) ){ echo "active"; } ?>"><a href="<?php echo $page->getUrl(); ?>"><?php echo $page->get( "title" ); ?></a></li>
				   <?php endforeach; ?>
				</ul>
				<?php $this->runActions( "lavaNav" ); ?>
			</div>
			<noscript>
				<div class="lava-message warning">
					<span class="message"><?php _e( "You don't have JavaScript enabled. Many features will not work without JavaScript.", $this->_framework()) ?></span>
				</div>
			</noscript>
			<?php $this->runActions( "pageHiddenStuff" ); ?>

			<div class="lava-content-cntr bleed-left bleed-right with-padding">
				<div class="lava-underground texture texture-woven bleed-left bleed-right with-padding underground-hidden" style="">
				<?php
					$this->runActions( "displayUnderground" );
					$this->displayUnderground();
				?>
				</div>
				<div class="lava-overground">
					<div class="torn-paper bleed-left bleed-right bleed-abs"></div>
					<div class="lava-btn-hide-underground underground-cancel-bar lava-btn lava-btn-block" style="display:none"><?php $this->cancelText() ?></div>
					<div class="content">
		<?php
	}

	function displayUnderground()
	{
		//sub classes should overload this method or rely on js to move things around (if they have to)
	}

	function displayFooter()
	{
		?>
					<!--.content END-->
					</div>
				<!--.lava-overground END-->
				</div>
				<?php $this->displayToolbar() ?>
			<!--.lava-content-cntr END-->
			</div>
		<!--.wrap END-->
		</div>
		<?php
	}

	function displayNotifications()
	{
		$notifications = array();
		if( isset( $_GET[ 'messagesnonce' ] ) )
		{
			$storedNotifications = get_option( "lavaNotifications" );

			if( is_array( $storedNotifications ) and isset( $storedNotifications[ $_GET[ 'messagesnonce' ] ] ) )
			{
				$storedNotifications = $storedNotifications[ $_GET[ 'messagesnonce' ] ];

				if( is_array( $storedNotifications ) )
				{
					foreach( $storedNotifications as $notification )
					{
						$notifications[] = $notification;
					}
				}
			}
		}
		$page_hook = $this->pageHook;
		$notifications = apply_filters( "lava_notifications-{$page_hook}", $notifications );

		foreach( $notifications as $notification )
		{
			?>
			<div class="lava-notification lava-notification-"><?php echo $notification['message'];?></div>
			<?php
		}
	}

	function displayPage()
	{
		?>
		<div class="lava-notification lava-notification-error"><?php _e( "It looks like this page has gone walk-abouts.", $this->_framework() ) ?></div>
		<?php
	}

	function displayToolbar()
	{
		?>
		<div class="lava-toolbar lava-sticky-bottom <?php echo $this->runFilters( "toolbarClass" ) ?>">
			<div class="inner">
				<?php $this->runActions( "toolbarButtons" ) ?>
			</div>
		</div>
		<?php
	}

	function dieWith( $message = "" ) {
		echo "$message";
		die;
	}

	function cancelText()
	{
		_e( "Cancel", $this->_framework() );
	}

	function hookTags()
	{
		$hooks = array(
			" ",
			"slug/{$this->slug}",
			"multisiteSupport/{$this->multisiteSupport}"
		);
		return $hooks;
	}
}
?>