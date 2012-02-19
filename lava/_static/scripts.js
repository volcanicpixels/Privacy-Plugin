var lavaAnimations = true;

jQuery(document).ready(function(){
    jQuery('.js-only').removeClass('js-only');
	jQuery('.js-fallback').hide();
    jQuery('select').dropkick();

	bindSticky();
	bindButtons();

    prettifyCheckboxes();
    prettifyPasswords();
    prettifyTexts();
    prettifyTimePeriods();

    addResetSettings();
	parseSkin();

	jQuery( '.tiptip' ).tipTip({'delay':0});
    jQuery( '.tiptip-right' ).tipTip({'defaultPosition':'right','delay':0});
});


function prettifyCheckboxes()
{
    jQuery('.setting[data-type="checkbox"]').each(function(){
        var checked = jQuery(this).find('input[type="checkbox"]').addClass( "invisible" ).hasAttr( "checked" );
        jQuery(this).find('input[type="checkbox"]').change(function(){
            var checked = jQuery(this).hasAttr( "checked" );
            var checkboxUx = jQuery(this).parents( '.setting' ).find( '.checkbox-ux' );
            if( checked )
            {
                jQuery( checkboxUx ).removeClass( "unchecked" ).addClass("checked");
            }
            else
            {
                jQuery( checkboxUx ).removeClass( "checked" ).addClass("unchecked");
            }
        }).change();
        jQuery(this).find('.checkbox-ux' ).click(function(){
            if( jQuery(this).siblings('input[type="checkbox"]').hasAttr( "checked" ) )
            {
                jQuery(this).siblings('input[type="checkbox"]').removeAttr( "checked" ).change();
                jQuery(this).removeClass("checked").addClass("unchecked");
            }
            else
            {
                jQuery(this).siblings('input[type="checkbox"]').attr( "checked", "checked" ).change();
                jQuery(this).removeClass("unchecked").addClass("checked");
            }
        });
    });
}

function prettifyPasswords()
{
    jQuery('.setting[data-type="password"]').each(function(){
        jQuery(this).find( 'input[type="password"]' ).blur(function(){
            var password = jQuery(this).val();
            jQuery(this).siblings(".password-show").val(password);
            jQuery(this).parent( '.input-cntr' ).removeClass( "focus" ).click(function(){
                jQuery(this).find('input[type="password"]').focus();
            });

        }).focus(function(){
            jQuery(this).parent( '.input-cntr' ).addClass( "focus" );
        });
        jQuery(this).find( ".password-show" ).blur(function(){
            var password = jQuery(this).val();
            jQuery(this).siblings('input[type="password"]').val(password);
            jQuery(this).parent( '.input-cntr' ).removeClass( "focus" );
        }).focus(function(){
            jQuery(this).parent( '.input-cntr' ).addClass( "focus" );
        });

        jQuery(this).find( ".show-password-handle" ).click(function(){
			var currentPassword = jQuery(this).parents('.setting').find('.input-cntr').attr("data-show", "text").find( '.password-show' ).val();//hack to prevent browser from selecting text in field
			jQuery(this).parents('.setting').find( '.password-show' ).focus().val( currentPassword );
            jQuery(this).siblings(".hide-password-handle").show();
            jQuery(this).hide();
        });

        jQuery(this).find( ".hide-password-handle" ).click(function(){
			var currentPassword = jQuery(this).parents('.setting').find('.input-cntr').attr("data-show", "password").find( 'input[type="password"]' ).val();
			jQuery(this).parents('.setting').find( 'input[type="password"]' ).focus().val( currentPassword );
            jQuery(this).siblings(".show-password-handle").show();
            jQuery(this).hide();
        });
    });
}

function prettifyTexts()
{
    jQuery('.setting[data-type="text"]').each(function(){
    });
}

function prettifyTimePeriods()
{
    jQuery('.setting[data-type="timeperiod"]').each(function(){
        jQuery(this).find('input[data-actual="true"]').addClass("invisible").change(function(){
            var newValue = jQuery( this ).val();
            newValue = Math.round( newValue / 60 ) * 60;
            jQuery( this ).val( newValue );//make sure it is a multiple of 60
            if( newValue % ( 60 * 60 * 24 * 7 ) == 0 )
            {
                jQuery( this ).parents( '.setting' ).find( '.time-period-ux' ).val( newValue / (60*60*24*7) );
                jQuery( this ).parents( '.setting' ).find( 'a[data-dk-dropdown-value="' + 60*60*24*7  + '"]' ).click();
            }
            else if( newValue % ( 60 * 60 * 24  ) == 0 )
            {
                jQuery( this ).parents( '.setting' ).find( '.time-period-ux' ).val( newValue / (60*60*24) );
                jQuery( this ).parents( '.setting' ).find( 'a[data-dk-dropdown-value="' + 60*60*24  + '"]' ).click();
            }
            else if( newValue % ( 60 * 60  ) == 0 )
            {
                jQuery( this ).parents( '.setting' ).find( '.time-period-ux' ).val( newValue / (60*60) );
                jQuery( this ).parents( '.setting' ).find( 'a[data-dk-dropdown-value="' + 60*60  + '"]' ).click();
            }
            else
            {
                jQuery( this ).parents( '.setting' ).find( '.time-period-ux' ).val( newValue / (60) );
                jQuery( this ).parents( '.setting' ).find( 'a[data-dk-dropdown-value="' + 60  + '"]' ).click();
            }
        });

        jQuery(this).find('select').change(function(){
            var quantity = jQuery(this).siblings('.input-cntr').find('.time-period-ux').val();
            var multiplier = jQuery(this).val();

            jQuery(this).siblings('input[data-actual="true"]').val( quantity * multiplier );
        });
        jQuery(this).find('.time-period-ux').change(function(){
            var quantity = jQuery(this).val();
            var multiplier = jQuery(this).parents('.setting-control').find('select').val();

            jQuery(this).parents('.setting-control').find('input[data-actual="true"]').val( quantity * multiplier );
            
        });
    });
}

function addResetSettings()
{
    jQuery( '.setting' ).each(function(){
        jQuery(this).find( '.reset-setting' ).click(function(){
            var settingParent = jQuery(this).parents( ".setting" );
            var defaultValue = jQuery(settingParent).attr("data-default-value");
            var valueChanged = changeSettingValue(settingParent, defaultValue);
            if( valueChanged )
            {
                jQuery(this).siblings('.undo-reset-setting').show();
                jQuery(this).hide();
                jQuery(settingParent).find('.show-status').each(function(){
                    var originalColor = jQuery(this).css("backgroundColor");
                    var newColor = '#FDEEAB';
                    jQuery(this)
                        .css({'background-image': 'none'})
                        .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100)
                        .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100)
                        .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100)
                        .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100, function(){
                            jQuery(this).css({'background-image': ''});
                        });
                });
            }
        });
        jQuery(this).find( '.undo-reset-setting' ).click(function(){
            var settingParent = jQuery(this).parent().parent().parent();
            var newValue = jQuery(settingParent).attr("data-default-undo");
            var valueChanged = changeSettingValue(settingParent, newValue);
            jQuery(this).siblings('.reset-setting').show();
            jQuery(this).hide();
            jQuery(settingParent).find('.show-status').each(function(){
                var originalColor = jQuery(this).css("backgroundColor");
                var originalImage = jQuery(this).css("backgroundImage");
                var newColor = '#FDEEAB';
                jQuery(this)
                    .css({'background-image': 'none'})
                    .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100)
                    .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100)
                    .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100)
                    .animate({backgroundColor: newColor}, 100).animate({backgroundColor: originalColor }, 100, function(){
                        jQuery(this).css({'background-image': ''});
                    });
            });
            
        });
    });
}

function changeSettingValue(settingSelector, settingValue)
{
    
    var settingCurrent = jQuery(settingSelector).find('*[data-actual="true"]').val();
    var settingType = jQuery(settingSelector).attr("data-type");
    var doDefault = true;
    var isChanged = false;

    if(settingType == 'checkbox')
    {
        settingCurrent = "off";
        if(jQuery(settingSelector).find('.checkbox-ux').hasClass('checked'))
        {
            settingCurrent = "on";
        }
        if( settingValue == "on" )
        {
            jQuery(settingSelector).find('input[type="checkbox"]').attr("checked", "checked").change();
        }
        else
        {
            jQuery(settingSelector).find('input[type="checkbox"]').removeAttr("checked").change();
        }
    }
    jQuery(settingSelector).attr('data-default-undo', settingCurrent);

    if( settingCurrent != settingValue)
    {
        isChanged = true;
    }
    if( doDefault )
    {
        jQuery(settingSelector).find('*[data-actual="true"]').val( settingValue ).change().blur();
    }
    return isChanged;
}


function bindButtons() {
    //the save buttons
    jQuery(".lava-btn.lava-btn-form-submit").click(function(){
        var formID = jQuery(this).attr( "data-form" );
		jQuery("#" + formID).submit();
    });
	//the underground buttons
	jQuery(".lava-btn.lava-btn-show-underground").click(function(){
        showUnderground();
    });
	jQuery(".lava-btn.lava-btn-hide-underground").click(function(){
        hideUnderground();
    });
	//the select skin button
	jQuery(".lava-btn.lava-btn-select-skin").click(function(){
        var skinSlug = jQuery(this).attr( "data-slug" );
		jQuery('#private_blog-skins-skin').val( skinSlug );
		hideUnderground();
		parseSkin();
    });
	//not implemented buttons
	jQuery(".lava-btn.not-implemented").addClass("lava-btn-disabled").addClass("tiptip-right").attr("title", "This feature hasn't been imlemented yet :(");
}

function bindSticky()
{
	jQuery('#wpbody').resize( function() {
		restartStickyBottom();
		restartStickyTop();
	});
	jQuery('#wpbody').resize();
	jQuery(window).scroll( function() {
		refreshStickyBottom();
		refreshStickyTop();
	});
	setTimeout( "restartStickyBottom()", 1000);
	setTimeout( "restartStickyTop()", 1000);
}

function restartStickyTop()
{
	var leftPosition = jQuery('#adminmenuback').outerWidth();//work out how far from the left it should be when absolutely positioned;
	var topPosition = jQuery('#wpbody').offset();//work out how far from top it should be positioned so it doesn't cover the admin bar
	topPosition = topPosition.top;

	jQuery('.lava-sticky-top').each(function(){
		var offset = jQuery(this).removeClass('sticky').offset();
		jQuery(this).attr( 'data-sticky-offset', offset.top - topPosition );
		jQuery(this).attr( 'data-sticky-leftposition', leftPosition );
		jQuery(this).attr( 'data-sticky-topposition', topPosition );
	});

	refreshStickyTop();
}

function refreshStickyTop()
{
	jQuery('.lava-sticky-top').each(function(){
		var offset = jQuery(this).attr('data-sticky-offset');//distance between object and top of document
		var targetOffset = jQuery(document).scrollTop();
		var leftPosition = jQuery(this).attr('data-sticky-leftposition');
		var topPosition = jQuery(this).attr('data-sticky-topposition');
		offset = parseInt(offset);
		targetOffset = parseInt(targetOffset);

		if( offset < targetOffset ) {
			jQuery(this).addClass('sticky').css({'left':leftPosition + 'px', 'top':topPosition + 'px'});
		} else if( offset > targetOffset ) {
			jQuery(this).removeClass('sticky').css({'left':'0px','top':'0px'});
		}
	});
}

function restartStickyBottom()
{
	var leftPosition = jQuery('#adminmenuback').outerWidth();
	jQuery('.lava-sticky-bottom').each(function(){
		var offset = jQuery(this).removeClass('sticky').offset();
		var targetOffset = jQuery('body').height() - jQuery(this).outerHeight() + 5;

		jQuery(this).attr( 'data-sticky-offset', offset.top );
		jQuery(this).attr( 'data-sticky-target', targetOffset );
		jQuery(this).attr( 'data-sticky-leftposition', leftPosition );
	});

	refreshStickyBottom();
}

function refreshStickyBottom()
{
	jQuery('.lava-sticky-bottom').each(function(){
		var offset = jQuery(this).attr('data-sticky-offset');
		var targetOffset = jQuery(document).scrollTop() + parseInt(jQuery(this).attr('data-sticky-target'));
		var leftMargin = jQuery(this).attr('data-sticky-leftposition');
		offset = parseInt(offset);
		targetOffset = parseInt(targetOffset);

		if( offset > targetOffset ) {
			jQuery(this).addClass('sticky').css({'left':leftMargin + 'px'});
		} else if( offset < targetOffset ) {
			jQuery(this).removeClass('sticky').css({'left':'0px'});
		}
	});
}


function showUnderground() {
	var animationDuration = 500;
	jQuery('.lava-underground').slideDown(animationDuration).removeClass('underground-hidden').addClass('underground-visible');
	jQuery('.lava-overground .underground-cancel-bar').slideDown().animate({'opacity':1},animationDuration, function(){
		jQuery('.lava-overground').addClass('lava-sticky-bottom');
		restartStickyBottom();
	});
	jQuery('.lava-overground .content').fadeOut(animationDuration);
    jQuery('.lava-content-cntr').addClass( "no-toolbar" );
}

function hideUnderground() {
	var animationDuration = 500;
	jQuery('.lava-overground').removeClass('lava-sticky-bottom').removeClass('sticky').css({'left':'0px'});
	jQuery('.lava-overground .content').fadeIn(animationDuration);
	jQuery('.lava-underground').slideUp(animationDuration).addClass('underground-hidden').removeClass('underground-visible');
	jQuery('.lava-overground .underground-cancel-bar').slideUp().animate({'opacity':0},animationDuration);
    jQuery('.lava-content-cntr').removeClass( "no-toolbar" );
}

function parseSkin() {
	jQuery( ".skin-selector .skin" ).removeClass( "active" );
	var currentTheme = jQuery('#private_blog-skins-skin').val();
	jQuery('.skin[data-slug="' + currentTheme + '"]').addClass('active');
	var imgSrc = jQuery('.skin[data-slug="' + currentTheme + '"] img').attr('src');
	jQuery('#setting-cntr_private_blog-skins-skin .skin-thumb img').attr({'src': imgSrc});

    //show skin options

	jQuery('.setting.tag-skin-setting').addClass( 'tag-setting-hidden' );
	jQuery('.setting[data-skin="' + currentTheme + '"]').removeClass( 'tag-setting-hidden' );

}