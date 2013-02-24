<?php
class lavaSkinsPage extends lavaSettingsPage
{
    public $multisiteSupport = true;
    public $who = "skins";
    public $toolbarClasses = "toolbar-skins";

    function customPlugin() {
		?>
		<p>
			Want a custom skin or want it to integrate with your site? <a href="http://www.volcanicpixels.com/contact-us/?utm_source=plugin_settings">Contact us</a> and we'll give you a quote for a custom plugin.
		</p>
		<?php
	}
}
?>