<?php
/**
 * Lava_Settings
 *
 * @package Lava
 * @subpackage Settings
 * @author Daniel Chatfield
 *
 * @since 1.0.0
 */
class Lava_Settings extends Lava_Base
{
	protected $_settings = array();
	protected $_setting_prefix = 'settings';
	protected $_setting_types = array(
		'' 			=> '',
		'text' 		=> 'Text',
		'checkbox'	=> 'Checkbox'
	);



	function _construct( $setting_prefix = 'settings' )	{
		$this->_setting_prefix = $setting_prefix;
		//add the option if it doesn't exist
		add_option( $this->_namespace( $this->_setting_prefix ), array() );
	}

	function _add_setting( $setting_key, $setting_type ) {
		if( ! $this->_setting_exists( $setting_key ) ) {
			if( ! array_key_exists( strtolower( $setting_type ) , $this->_setting_types ) ) {
				$setting_type = $this->_setting_types[ $setting_type ];
			} else {
				$setting_type = '';
			}

			$class_name = "Setting_{$setting_type}";

			$args = array(
				$this->_setting_prefix,
				$setting_key
			);

			$this->_settings[ $setting_key ] = $this->_instantiate_class( $class_name, $args );
		}

		$this->_set_child( $this->_settings[ $setting_key ] );

		return $this;
	}


	function _get_setting( $setting_key )
	{
		$this->_killChild();

		if( $this->_setting_exists( $setting_key ) ) {
			$this->_setChild( $this->_settings[ $setting_key ] );
		}

		return $this;
	}

	function _setting_exists( $setting_key ) {
		if( array_key_exists( $setting_key , $this->_settings ) )
			return true;
		else
			return false;
	}


	function _get_settings() {
		return $this->_settings;
	}

	function _get_settings_by_tag( $tag ) {
		$setting_prefix = $this->_setting_prefix;
		return $this->_apply_lava_filters( "_get_settings_by_tag/{$setting_prefix}/{$tag}", array() );
	}



	function _get_settings_from_db() {
		return get_option( $this->_namespace( $this->_setting_prefix ) );
	}

	function _get_setting_from_db( $setting_key, $default = null ) {
		$settings = $this->_get_settings_from_db();

		if( array_key_exists( $setting_key, $settings ) )
			return $settings[ $setting_key ];
		else
			return $default;
	}

	function _update_settings_to_db( $settings ) {
		return update_option( $this->_namespace( $this->_setting_prefix ) );
	}

	function _update_setting_to_db( $setting_key, $setting_value ) {
		$settings = $this->_get_settings_from_db();
		$settings[ $setting_key ] = $setting_value;
		return $this->_update_settings_to_db( $settings );
	}
}
?>