<?php
class Lava_Extension extends Lava_Base {
	function lavaConstruct() {
		$this->_misc()->_addAutoMethods( $this );
		$this->registerActions();
	}

	function registerActions() {
		//should be overloaded
	}
}
?>