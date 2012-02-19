<?php
class private_blog_callbacks extends lavaBase
{
    function lavaConstruct()
    {
		$this->translation();
    }

	function init() {
		$hookTag = "settingInnerPre";
		$this->addFilter( "{$hookTag}-tag/password-label", "addLabelHtml" );

		$hookTag = "get_header";
		$this->addWPAction( $hookTag, "doHeadActions", 2 );

		$hookTag = "displayLoginPage";
		$this->addAction( $hookTag );

		$hookTag = "_templateVars_pluginVars";
		$this->addAction( $hookTag, "pluginVars");

		$hookTag = "formInputs";
		$this->addFilter( $hookTag, "addActionField");
		$this->addFilter( $hookTag, "addRedirectField");
		$this->addFilter( $hookTag, "addPasswordField");
		$this->addFilter( $hookTag, "addSubmitField");

		$hookTag = "isLoginRequest";
		$this->addFilter( $hookTag );

		$hookTag = "isLogoutRequest";
		$this->addFilter( $hookTag );

		$hookTag = "isLoginAccepted";
		$this->addFilter( $hookTag );
		
		$hookTag = "loginAccepted";
		$this->addFilter( $hookTag );

		$hookTag = "loginRejected";
		$this->addFilter( $hookTag );

		$hookTag = "logout";
		$this->addFilter( $hookTag );

		$hookTag = "isLoggedIn";
		$this->addFilter( $hookTag );

		$rss_public = $this->_settings()->fetchSetting( "rss_feed_visible" )->getValue();

		$hookTags = array(
			"do_feed",
			"do_feed_rdf",
			"do_feed_rss",
			"do_feed_rss2",
			"do_feed_atom"
		);
		if( $rss_public != "on" ) {
			$this->addWPAction( $hookTags, "disableRssFeed", 1, true );
		}

		$this->addLogoutLink();
	}


	function adminInit() {
		$this->logoutLinkBackend();
	}

	function translation() {
		$this
			->_skins()
				->addTranslation( "logout_message", __( "You have been logged out.", $this->_slug() ) )
				->addTranslation( "incorrect_credentials", __( "The password you submitted was wrong.", $this->_slug() ) )
		;
	}

    function addLabelHtml( $html )
    {
        $pluginSlug = $this->_slug();

        $html = '<div class="js-only custom-password-label clearfix tiptip" onclick="alert(\'not implemented yet\')" title="' . __( "Click to change the name and colour of this password. These will be used in the Access Logs.", $pluginSlug ) . '"><span>undefined</span></div>' .$html;
        return $html;
    }
	/**
	 * doHeadActions function
	 *	Wrapper function that calls all hooks - isn't using getHeader() as we need priority here
	 *
	 */
    function doHeadActions()
    {
		$isEnabled = $this->_settings()->fetchSetting( "enabled" )->getValue();
		if( $isEnabled == "off" ) {
			//protection is disabled
			return;
		}
        $isLoginRequest = $this->runFilters( "isLoginRequest", false);
        if( true === $isLoginRequest )
        {
			//the user is attempting to login
            $isLoginAccepted = apply_filters( $this->_slug( "isLoginAccepted" ), false );

            if( true === $isLoginAccepted ) {
                do_action( $this->_slug( "loginAccepted" ) );
            } else {
                do_action( $this->_slug( "loginRejected" ) );
            }
        }

        $isLogoutRequest = $this->runFilters( "isLogoutRequest", false );

        if( true === $isLogoutRequest )
        {
			//the user is attempting to logout
            do_action( $this->_slug( "logout" ) );
        }

        $isLoggedIn = apply_filters( $this->_slug( "isLoggedIn" ), false );

        if( true === $isLoggedIn ) {
            //refresh logged in cookies
			$this->setCookie();
            return;
        } else {
			do_action( $this->_slug( "displayLoginPage" ) );
			exit;
        }
    }

	function isLoginRequest( $current ) {
		//should only alter value if there is a login as default is false so if it is something else then it is by design
		$field = $this->_slug( "action" );
		if( array_key_exists( $field , $_POST ) and $_POST[$field] == "login" )
		{
			$current = true;
		}
		return $current;
	}

	function isLogoutRequest( $current ) {
		$field = $this->_slug( "action" );
		if( array_key_exists( $field , $_GET ) and $_GET[$field] == "logout" )
		{
			$current = true;
		}
		return $current;
	}

	function logout() {
		setcookie( $this->_slug( "loggedin" ), "LOGGEDOUT", time() - 1000, COOKIEPATH, COOKIE_DOMAIN );
		$redirect = remove_query_arg( $this->_slug( "action" ) );
		$redirect = add_query_arg( "loggedout", "", $redirect );
		wp_redirect( $redirect );
		exit();
	}

	function isLoginAccepted( $current ) {
		global $maxPasswords;
		$password = $_POST[ $this->_slug( "password" ) ];
		$password = $this->runFilters( "passwordFilter", $password );
		
		$multiplePasswords = $this->_settings()->fetchSetting( "multiple_passwords" )->getValue();
			
		$limit = 1;
		if( $multiplePasswords == "on" ) {
			$limit = $maxPasswords;
		}

		for( $i = 1; $i <= $limit; $i++ ) {
			
			$passToCheck = $this->_settings()->fetchSetting( "password".$i."_value" )->getValue();
			if( !empty( $passToCheck ) and $passToCheck == $password ) {
				$current = true;
			}
		}

		return $current;
	}

	function loginAccepted() {
		$this->setCookie();
		$redirect = $_POST[ $this->_slug( "redirect" ) ];
		wp_redirect( $redirect );
	}

	function loginRejected() {
		$redirect = add_query_arg( "incorrect_credentials", "" );
		$redirect = remove_query_arg( "loggedout", $redirect );
		wp_redirect( $redirect );
	}

	function setCookie() {
		$loginNonce = wp_create_nonce( $this->_slug( "loggedin" ) );
		$expire = $this->_settings()->fetchSetting( "timeout_length" )->getValue();
		if( $expire != 0) {
			$expire = time() + $expire;
		}
		setcookie( $this->_slug( "loggedin" ), $loginNonce, $expire, COOKIEPATH, COOKIE_DOMAIN );
	}

	function isLoggedIn( $current ) {
		$cookieName = $this->_slug( "loggedin" );
		
		if( array_key_exists( $cookieName, $_COOKIE ) ) {
			$nonce = $_COOKIE[ $cookieName ];
			
			$nonceName = $this->_slug( "loggedin" );
			if( wp_verify_nonce( $nonce, $nonceName ) ) {
				$current = true;
			}
		}

		return $current;
	}




	function displayLoginPage()
	{
		echo $this->_skins()->renderTemplate( "loginpage" );
	}

	function pluginVars( $pluginVars) {
		$pluginVars['form_inputs'] = apply_filters( $this->_slug( "formInputs" ), array() );

		return $pluginVars;
	}

	function addActionField( $formInputs ) {
		$formInputs[] = array(
			"type" => "hidden",
			"name" => $this->_slug( "action" ),
			"value" => "login"
		);

		return $formInputs;
	}
	
	function addRedirectField( $formInputs ) {
		$redirect = add_query_arg( "loggedin", "" );
		$redirect = remove_query_arg( "loggedout", $redirect );
		$redirect = remove_query_arg( "incorrect_credentials", $redirect );

		$formInputs[] = array(
			"type" => "hidden",
			"name" => $this->_slug( "redirect" ),
			"value" => $redirect
		);

		return $formInputs;
	}

	function addPasswordField( $formInputs ) {
		$formInputs[] = array(
			"type" => "password",
			"name" => $this->_slug( "password" ),
			"id" => "password",
			"label" => __( "Password", $this->_slug() ),
			"class" => "input"
		);
		
		return $formInputs;
	}
	/*
		Adds the submit field to the login form
	*/
	
	function addSubmitField( $formInputs ) {
		$formInputs[] = array(
			"type" => "submit",
			"name" => $this->_slug( "submit" ),
			"id" => "submit",
			"value" => __( "Login", $this->_slug() )
		);
		
		return $formInputs;
	}
	
	function disableRssFeed() {
		$isLoggedIn = apply_filters( $this->_slug( "isLoggedIn" ), false );
		
		if( $isLoggedIn === true ) {
			return;
		} else {
			wp_die( __('The feed for this website is protected, please visit our <a href="'. get_bloginfo('url') .'">website</a>!') );
		}
	}

	/*
	Detects what mechanism the theme is using to display navigation and adds the relevant filters
	*/

	function addLogoutLink() {
		$menu = $this->_settings()->fetchSetting( "logout_link_menu" )->getValue();
		$this->addWPFilter( "wp_nav_menu_{$menu}_items", "pagesFilter", 10, 2 );
		$this->addWPFilter( "wp_list_pages", "pagesFilter", 10, 2 );
		$this->addWPFilter( "wp_page_menu", "pagesFilter", 10, 2 );//this is fired when the theme uses WP3 menus but the admin hasn't created one
	}

	/*
		Adds logout link to themes that use the old wp_list_pages function and the new WP3 menus
	*/
	function pagesFilter( $output, $r ) {
		if( !strpos( $output, "page-item-logout" ) )
			$output .= '<li class="page_item page-item-logout"><a href="' . add_query_arg( $this->_slug( "action" ), "logout", get_bloginfo('url') ) . '">' . __( "Logout", $this->_slug() ) . '</a></li>';
		return $output;
	}



	/*
		Handles the backend stuff to make sure only the options that can actually be changed are shown to user and makes sure that theme changes don't break it.
	*/
	function logoutLinkBackend() {
		
		$locations = get_nav_menu_locations();// get the locations set by the theme
		if( !is_array( $locations ) ) {
			$locations = array();
		}
		$counter = 0;
		$valueArray = array();
		$defaultValue = "list_pages";
		foreach( $locations as $location => $locationId ) {
			if( has_nav_menu( $location ) ) {
				$menu = wp_get_nav_menu_object( $locationId );
				$counter ++;
				if( $counter == 1 ) {
					$defaultValue = $menu->slug;//make sure a default is set
				}
				$valueArray[] = $menu->slug;
				$this->_settings()->fetchSetting( "logout_link_menu" )->addSettingOption( $menu->slug, $menu->name );
			}
		}
		if( $counter == 0 ) {
			//if there is no menu that is attached to a location then we should check to see whether there is any menus at all
			$menus = wp_get_nav_menus();
			foreach ( $menus as $menu_maybe ) {
				if ( $menu_items = wp_get_nav_menu_items($menu_maybe->term_id) ) {
					$menu = $menu_maybe;
					$this->_settings()->fetchSetting( "logout_link_menu" )->addSettingOption( $menu->slug, $menu->name )->removeTag( "options-available" );
					$valueArray[] = $menu->slug;
					$defaultValue = $menu->slug;
					break;
				}
			}
		} else if( $counter == 1 ) {
			//if there is only one then no need to give option
			$this->_settings()->fetchSetting( "logout_link_menu" )->removeTag( "options-available" );
		}
		$currentValue = $this->_settings()->fetchSetting( "logout_link_menu" )->setDefault( $defaultValue )->getValue();
		if( !in_array( $currentValue, $valueArray ) ) {
			//something has changed and the current value no longer exists as a menu so reset it to new default (probably theme change)
			$this->_settings()->fetchSetting( "logout_link_menu" )->updateValue( $defaultValue );
		}
	}
	
}
?>