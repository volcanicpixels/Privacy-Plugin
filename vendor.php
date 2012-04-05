<?php
/*
Add premium feature labels
Add premium feature warning
Add premium feature trial load
Add licensing to bar

*/
class private_blog_vendor extends lavaExtension {
	function init() {
		$this->registerLicensingSettings();
	}
	function adminInit(){
		$this->addAction( "ajaxChecks" );
		$this->addAction( "lavaNav" );
		$this->addAction( "displayUnderground" );
		$this->addAction( 'pageHiddenStuff' );
		$this->_pages()
				->addScript( $this->_slug( "vendor_js" ), "_static/vendor.js" )
				->addStyle( $this->_slug( "vendor_css" ), "_static/vendor.css" )
		;
		$this->_ajax()->addHandler( 'lavaVolcanicPixelsLicensingAjax' );
	}

	function doLicensingHooks() {
		if( md5( $this->privateKey() ) != $this->publicKey() ) {
			$this->addFilter( "settingAbsElements-tag/is-premium", "settingAbsElements" );
		} else {
			$this->addFilter( "settingClasses-tag/is-premium", "removePremiumBlock" );
		}
	}

	function removePremiumBlock( $classes ) {
		unset( $classes['tag-is-premium'] );

		return $classes;
	}

	function registerLicensingSettings() {
		$this->_settings()
			->addSetting('license_public', 'vendor')
			->addSetting('license_private', 'vendor')
		;
		$this->doLicensingHooks();
	}

	function ajaxChecks(){
		?>
		<span class="ajax-check loading type-register tiptip" title="Registering..."></span>
		<span class="ajax-check type-update hidden tiptip" title="Checking for update..."></span>
		<span class="ajax-check loading type-licensing tiptip"  title="Checking license..."></span>
		<?php
	}

	function lavaNav(){
		$code_link_text = 'Redeem key';
		if( $this->publicKey() != '' ) {
			$code_link_text = 'Change key';
		}
		?>
		<a href="#unlock" title="Click to purchase a code to unlock premium features" class="tiptip vendor-link get-premium-link">Get premium</a>
		<a href="#redeem" title="Click to redeem a previously purchased code to unlock premium features" class="tiptip vendor-link redeem-code-link"><?php echo $code_link_text ?></a>
		<?php
	}

	function settingAbsElements( $current ) {
		$current .= '
		<div class="premium-notice remove-for-trial">
			<div class="premium-notice-inner">
				<div class="premium-line">
					<div class="lava-btn start-trial tiptip" title="Click to enter trial. In this mode you can try out premium features but cannot permanently save your settings.">Enter trial mode</div>
					<div class="lava-btn get-premium-link tiptip" title="Click to purchase a license to permanently unlock premium features">Get premium</div>
				</div>
			</div>
		</div>';

		return $current;
	}

	function displayUnderground() {
		$this->getPremiumUi();
		$this->redeemPremiumUi();
	}

	function getPremiumUi() {
		?>
		<div class="underground-section underground-hidden underground-context-get-premium loading">
			<h2>Get Premium</h2>
			<div class="lava-new-message lava-message-notice" style="background: white">Licenses can be transferred between websites but excesively doing this (doing it over 10 times in a week for example) may cause the license to be blacklisted</div>

			<div class="lava-new-message lava-message-notice" style="background: white">The price will be converted into your local currency before the transaction completes</div>
			<div class="license-options clearfix">
				<div class="lava-loader loading">
			        <span class="child1"></span>
			        <span class="child2"></span>
			        <span class="child3"></span>
			        <span class="child4"></span>
			        <span class="child5"></span>
			    </div>
			</div>

			<button data-clicked-text="Please wait ..." class="lava-btn lava-btn-action  lava-btn-block lava-btn-action-red purchase-premium-button" style="display: inline; margin-top: 30px">Purchase with PayPal</button>
		</div>
		<?php
	}

	function redeemPremiumUi(){
		//currently we offer no diagnostics

	}

	function pageHiddenStuff() {
		$this->licensingFields();
	}

	function licensingFields() {
		?>
		<input type="hidden" class="vendor-input license-public" value="<?php  echo $this->_settings()->fetchSetting('license_public', 'vendor' )->getValue(); ?>"/>
		<input type="hidden" class="vendor-input license-private" value="<?php  echo $this->_settings()->fetchSetting('license_private', 'vendor' )->getValue(); ?>"/>
		<input type="hidden" class="vendor-input ajax-action" value="<?php  echo $this->_slug('licensing') ?>"/>
		<input type="hidden" class="vendor-input ajax-nonce" value="<?php  echo wp_create_nonce( $this->_slug( "licensing" ) ); ?>"/>
		<?php
	}

	function publickey() {
		return $this->_settings()->fetchSetting('license_public', 'vendor')->getValue();
	}

	function privateKey() {
		return $this->_settings()->fetchSetting('license_private', 'vendor')->getValue();
	}
}
?>