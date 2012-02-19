<?php
class lavaSkinsPage extends lavaSettingsPage
{
    public $multisiteSupport = true;
    public $who = "skins";
    public $toolbarClasses = "toolbar-skins";

    function displayUnderground()
    {
        $skins = $this->_skins()->fetchSkins();
        ?>
        <div class="skin-selector clearfix">
            <?php foreach( $skins as $skin ): ?>
            <div class="skin" data-slug="<?php echo $skin->getSlug() ?>">
				<div class="ribbons">
					<?php $this->runActions( "skinRibbons" ) ?>
				</div>
				<div class="content">
					<img alt="Skin Thumbnail" src="<?php echo $skin->skinUrl( "thumbnail.png" ) ?>" />
					<div class="actions">
						<button class="lava-btn-select-skin lava-btn lava-btn-margin-bottom lava-btn-action lava-btn-action-blue lava-btn-action-large" data-slug="<?php echo $skin->getSlug() ?>"><?php _e( "Use this skin", $this->_framework() ) ?></button>
						<button class="lava-btn lava-btn-margin-bottom lava-btn-action lava-btn-action-white not-implemented" data-slug="<?php echo $skin->getSlug() ?>"><?php _e( "Edit this skin", $this->_framework() ) ?></button>
						<button class="lava-btn lava-btn-margin-bottom lava-btn-action lava-btn-action-white not-implemented" data-slug="<?php echo $skin->getSlug() ?>"><?php _e( "Make copy of this skin", $this->_framework() ) ?></button>
                    </div>
					<div class="name"><?php echo $skin->getName() ?> <span class="author">by <?php echo $skin->getAuthor() ?></span></div>
				</div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php
    }

	function cancelText()
	{
		_e( "Use current skin and return to settings", $this->_framework() );
	}
}
?>