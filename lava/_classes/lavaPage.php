<?php
/**
 * The lavaPage class
 * 
 * This class is the base class for all admin pages
 * 
 * @package Lava
 * @subpackage lavaPage
 * 
 * @author Daniel Chatfield
 * @copyright 2011
 * @version 1.0.0
 */
 
/**
 * lavaPage
 * 
 * @package Lava
 * @subpackage LavaPlugin
 * @author Daniel Chatfield
 * 
 * @since 1.0.0
 */
class lavaPage extends lavaBase
{
    public $multisiteSupport = false;//Whether the page should appear in the network sidebar
    public $styles = array(), $scripts = array();
    
    /**
    * lavaPage::lavaConstruct()
    * 
    * @return void
    *
    * @since 1.0.0
    */
    function lavaConstruct( $slug )
    {
        $this->setSlug( $slug, false );
        $this->setTitle( $slug );
        $this->setCapability( "manage_options" );
        $this->lavaCallReturn = $this->_pages( false );//prevents the parent losing control

        add_action( "admin_init", array($this, "_registerActions") );
        if( method_exists( $this, "registerActions" ) )
        {
            add_action( "admin_init", array($this, "registerActions") );
        }
    }

    function _registerActions()
    {
        $pageHook = $this->pageHook;
        if( is_callable( array( $this, "loadPage" ) ) )
        {
            add_action( "load-{$pageHook}", array( $this, "loadPage" ) );
        }
    }

	function get( $what )
	{
		return $this->$what;
	}

	
	function getUrl()
	{
		$slug = $this->get( "slug" );
		if( defined( 'WP_NETWORK_ADMIN' ) and WP_NETWORK_ADMIN == true )
		{
			//if we are in the network admin then make sure it is a network link
			return network_admin_url( "admin.php?page={$slug}");
		}
		return admin_url( "admin.php?page={$slug}");
	}
    
    function setCapability( $capability )
    {
        $this->capability = $capability;
        return $this->_pages( false );
    }
    
    function setSlug( $slug, $slugify = true )
    {
        $this->slug = $slug;

        if( $slugify == true )
        {
            $this->slug = $this->_slug( $slug );
        }
        return $this->_pages( false );
    }
    
    function setTitle( $title )
    {
        $this->title = $title;
        return $this->_pages( false );
    }
    
    function registerPage( $parentSlug )
    {
        $this->pageHook = add_submenu_page( 
            $parentSlug,
            $this->get( "title" ), 
            $this->get( "title" ), 
            $this->get( "capability" ),  
            $this->get( "slug" ), 
            array( $this, "doPage") 
        );
        $hook_suffix = $this->pageHook;
        add_action( "admin_print_styles-$hook_suffix", array( $this, "enqueueIncludes" ) );
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
        <div class="wrap">
            <div class="lava-header" style="margin-bottom:10px;">
                <div id="icon-options-general" class="icon32"></div>
                <h2><?php echo $pluginName; ?> <span class="version"><?php echo $pluginVersion; ?></span></h2>
                <div class="ajax-checks">
                    <!-- When no-update is implemented wrap this in an "if" or better implement a hook -->
                    <div class="js-only loader" data-name="update-available"></div>
                 <!--.ajax-checks END-->
                </div>
            <!--.lava-header END-->
            </div>
            <div id="lava-nav" class="lava-nav bleed-left bleed-right with-padding lava-sticky-top">
                <div class="sticky-toggle tiptip" title="Toggle whether this bar should stick to the top of the screen."></div>
                <div class="left-grad"></div>
                <ul class="nav nav-horizontal clearfix">
                    <?php foreach( $this->_pages( false )->adminPages() as $page ): ?>
                   <li class="clearfix <?php echo $page->get( "slug" ); ?> <?php if( $page_hook == $page->get( "slug" ) ){ echo "active"; } ?>"><a href="<?php echo $page->getUrl(); ?>"><?php echo $page->get( "title" ); ?></a></li>
                   <?php endforeach; ?>
                </ul>
            </div>
            <noscript>
                <div class="lava-message warning">
                    <span class="message"><?php _e( "You don't have JavaScript enabled. Some features will not work without JavaScript.", $this->_framework()) ?></span>
                </div>
            </noscript>
			<div class="lava-content-cntr bleed-left bleed-right with-padding">
				<div class="lava-underground texture texture-woven bleed-left bleed-right with-padding underground-hidden" style="">
				<?php
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
		//sub classes should overload this method or rely on js to move things around (if have to)
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
				<?php $this->runActions( "toolbar" ) ?>
			</div>
		</div>
		<?php
    }

	function cancelText()
	{
		_e( "Cancel", $this->_framework() );
	}

    function hookTags()
    {
        $hooks = array(
            "",
            "slug/{$this->slug}",
            "multisiteSupport/{$this->multisiteSupport}"
        );
        return $hooks;
    }
}
?>