<?php
/**
 * This file implements a class derived of the generic Skin class in order to provide custom code for
 * the skin in this folder.
 *
 * This file is part of the b2evolution project - {@link http://b2evolution.net/}
 *
 * @package skins
 * @subpackage bootstrap_blog
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Specific code for this skin.
 *
 * ATTENTION: if you make a new skin you have to change the class name below accordingly
 */
class mobile_bootstrap_blog_Skin extends Skin
{
	/**
	 * Skin version
	 * @var string
	 */
	var $version = '6.9.4';

	/**
	 * Do we want to use style.min.css instead of style.css ?
	 */
	var $use_min_css = 'check';  // true|false|'check' Set this to true for better optimization
	// Note: we leave this on "check" in the bootstrap_blog_skin so it's easier for beginners to just delete the .min.css file
	// But for best performance, you should set it to true.

	/**
	 * Get default name for the skin.
	 * Note: the admin can customize it.
	 */
	function get_default_name()
	{
		return 'Mobile Bootstrap Blog';
	}


	/**
	 * Get default type for the skin.
	 */
	function get_default_type()
	{
		return 'rwd';
	}


	/**
	 * What evoSkins API does has this skin been designed with?
	 *
	 * This determines where we get the fallback templates from (skins_fallback_v*)
	 * (allows to use new markup in new b2evolution versions)
	 */
	function get_api_version()
	{
		return 6;
	}


	/**
	 * Get supported collection kinds.
	 *
	 * This should be overloaded in skins.
	 *
	 * For each kind the answer could be:
	 * - 'yes' : this skin does support that collection kind (the result will be was is expected)
	 * - 'partial' : this skin is not a primary choice for this collection kind (but still produces an output that makes sense)
	 * - 'maybe' : this skin has not been tested with this collection kind
	 * - 'no' : this skin does not support that collection kind (the result would not be what is expected)
	 * There may be more possible answers in the future...
	 */
	public function get_supported_coll_kinds()
	{
		$supported_kinds = array(
				'main' => 'partial',
				'std' => 'yes',		// Blog
				'photo' => 'no',
				'forum' => 'no',
				'manual' => 'no',
				'group' => 'maybe',  // Tracker
				// Any kind that is not listed should be considered as "maybe" supported
			);

		return $supported_kinds;
	}


	/*
	 * What CSS framework does has this skin been designed with?
	 *
	 * This may impact default markup returned by Skin::get_template() for example
	 */
	function get_css_framework()
	{
		return 'bootstrap';
	}


	/**
	 * Get definitions for editable params
	 *
	 * @see Plugin::GetDefaultSettings()
	 * @param local params like 'for_editing' => true
	 * @return array
	 */
	function get_param_definitions( $params )
	{
		$r = array_merge( array(
				'section_layout_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('Layout Settings')
				),
					'layout' => array(
						'label' => T_('Layout'),
						'note' => '',
						'defaultvalue' => 'right_sidebar',
						'options' => array(
								'single_column'              => T_('Single Column Large'),
								'single_column_normal'       => T_('Single Column'),
								'single_column_narrow'       => T_('Single Column Narrow'),
								'single_column_extra_narrow' => T_('Single Column Extra Narrow'),
								'left_sidebar'               => T_('Left Sidebar'),
								'right_sidebar'              => T_('Right Sidebar'),
							),
						'type' => 'select',
					),
					'max_image_height' => array(
						'label' => T_('Max image height'),
						'note' => 'px',
						'defaultvalue' => '',
						'type' => 'integer',
						'allow_empty' => true,
					),
					'font_size' => array(
						'label' => T_('Font size'),
						'note' => '',
						'defaultvalue' => 'default',
						'options' => array(
								'default'        => T_('Default (14px)'),
								'standard'       => T_('Standard (16px)'),
								'medium'      	 => T_('Medium (18px)'),
								'large' 		 => T_('Large (20px)'),
								'very_large'     => T_('Very large (22px)'),
							),
						'type' => 'select',
					),
				'section_layout_end' => array(
					'layout' => 'end_fieldset',
				),

				'section_colorbox_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('Colorbox Image Zoom')
				),
					'colorbox' => array(
						'label' => T_('Colorbox Image Zoom'),
						'note' => T_('Check to enable javascript zooming on images (using the colorbox script)'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
					'colorbox_vote_post' => array(
						'label' => T_('Voting on Post Images'),
						'note' => T_('Check this to enable AJAX voting buttons in the colorbox zoom view'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
					'colorbox_vote_post_numbers' => array(
						'label' => T_('Display Votes'),
						'note' => T_('Check to display number of likes and dislikes'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
					'colorbox_vote_comment' => array(
						'label' => T_('Voting on Comment Images'),
						'note' => T_('Check this to enable AJAX voting buttons in the colorbox zoom view'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
					'colorbox_vote_comment_numbers' => array(
						'label' => T_('Display Votes'),
						'note' => T_('Check to display number of likes and dislikes'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
					'colorbox_vote_user' => array(
						'label' => T_('Voting on User Images'),
						'note' => T_('Check this to enable AJAX voting buttons in the colorbox zoom view'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
					'colorbox_vote_user_numbers' => array(
						'label' => T_('Display Votes'),
						'note' => T_('Check to display number of likes and dislikes'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
				'section_colorbox_end' => array(
					'layout' => 'end_fieldset',
				),


				'section_username_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('Username options')
				),
					'gender_colored' => array(
						'label' => T_('Display gender'),
						'note' => T_('Use colored usernames to differentiate men & women.'),
						'defaultvalue' => 0,
						'type' => 'checkbox',
					),
					'bubbletip' => array(
						'label' => T_('Username bubble tips'),
						'note' => T_('Check to enable bubble tips on usernames'),
						'defaultvalue' => 0,
						'type' => 'checkbox',
					),
					'autocomplete_usernames' => array(
						'label' => T_('Autocomplete usernames'),
						'note' => T_('Check to enable auto-completion of usernames entered after a "@" sign in the comment forms'),
						'defaultvalue' => 1,
						'type' => 'checkbox',
					),
				'section_username_end' => array(
					'layout' => 'end_fieldset',
				),


				'section_access_start' => array(
					'layout' => 'begin_fieldset',
					'label'  => T_('When access is denied or requires login...')
				),
					'access_login_containers' => array(
						'label' => T_('Display on login screen'),
						'note' => '',
						'type' => 'checklist',
						'options' => array(
							array( 'header',   sprintf( T_('"%s" container'), NT_('Header') ),    1 ),
							array( 'page_top', sprintf( T_('"%s" container'), NT_('Page Top') ),  1 ),
							array( 'menu',     sprintf( T_('"%s" container'), NT_('Menu') ),      0 ),
							array( 'sidebar',  sprintf( T_('"%s" container'), NT_('Sidebar') ),   0 ),
							array( 'sidebar2', sprintf( T_('"%s" container'), NT_('Sidebar 2') ), 0 ),
							array( 'footer',   sprintf( T_('"%s" container'), NT_('Footer') ),    1 ) ),
						),
				'section_access_end' => array(
					'layout' => 'end_fieldset',
				),

			), parent::get_param_definitions( $params ) );

		return $r;
	}


	/**
	 * Get ready for displaying the skin.
	 *
	 * This may register some CSS or JS...
	 */
	function display_init()
	{
		global $Messages, $debug;

		// Request some common features that the parent function (Skin::display_init()) knows how to provide:
		parent::display_init( array(
				'jquery',                  // Load jQuery
				'font_awesome',            // Load Font Awesome (and use its icons as a priority over the Bootstrap glyphicons)
				'bootstrap',               // Load Bootstrap (without 'bootstrap_theme_css')
				'bootstrap_evo_css',       // Load the b2evo_base styles for Bootstrap (instead of the old b2evo_base styles)
				'bootstrap_messages',      // Initialize $Messages Class to use Bootstrap styles
				'style_css',               // Load the style.css file of the current skin
				'colorbox',                // Load Colorbox (a lightweight Lightbox alternative + customizations for b2evo)
				'bootstrap_init_tooltips', // Inline JS to init Bootstrap tooltips (E.g. on comment form for allowed file extensions)
				'disp_auto',               // Automatically include additional CSS and/or JS required by certain disps (replace with 'disp_off' to disable this)
			) );

		// Skin specific initializations:

		// Limit images by max height:
		$max_image_height = intval( $this->get_setting( 'max_image_height' ) );
		if( $max_image_height > 0 )
		{
			add_css_headline( '.evo_image_block img { max-height: '.$max_image_height.'px; width: auto; }' );
		}

		// Add custom CSS:
		$custom_css = '';


		// Font size customization
		if( $font_size = $this->get_setting( 'font_size' ) )
		{
			switch( $font_size )
			{
				case 'default': // When default font size, no CSS entry
				$custom_css = '';
				break;

				case 'standard':// When standard layout
				$custom_css = '.container p, .container ul li, .container div, .container label, .container textarea, .container input { font-size: 16px !important'." }\n";
				$custom_css .= '.container input.search_field { height: 100%'." }\n";
				$custom_css .= '.container h1 { font-size: 38px'." }\n";
				$custom_css .= '.container h2 { font-size: 32px'." }\n";
				$custom_css .= '.container h3 { font-size: 26px'." }\n";
				$custom_css .= '.container h4 { font-size: 18px'." }\n";
				$custom_css .= '.container h5 { font-size: 16px'." }\n";
				$custom_css .= '.container h6 { font-size: 14px'." }\n";
				$custom_css .= '.container .small { font-size: 85% !important'." }\n";
				break;

				case 'medium': // When default font size, no CSS entry
				$custom_css = '.container p, .container ul li, .container div, .container label, .container textarea, .container input { font-size: 18px !important'." }\n";
				$custom_css .= '.container input.search_field { height: 100%'." }\n";
				$custom_css .= '.container h1 { font-size: 40px'." }\n";
				$custom_css .= '.container h2 { font-size: 34px'." }\n";
				$custom_css .= '.container h3 { font-size: 28px'." }\n";
				$custom_css .= '.container h4 { font-size: 20px'." }\n";
				$custom_css .= '.container h5 { font-size: 18px'." }\n";
				$custom_css .= '.container h6 { font-size: 16px'." }\n";
				$custom_css .= '.container .small { font-size: 85% !important'." }\n";
				break;

				case 'large': // When default font size, no CSS entry
				$custom_css = '.container p, .container ul li, .container div, .container label, .container textarea, .container input { font-size: 20px !important'." }\n";
				$custom_css .= '.container input.search_field { height: 100%'." }\n";
				$custom_css .= '.container h1 { font-size: 42px'." }\n";
				$custom_css .= '.container h2 { font-size: 36px'." }\n";
				$custom_css .= '.container h3 { font-size: 30px'." }\n";
				$custom_css .= '.container h4 { font-size: 22px'." }\n";
				$custom_css .= '.container h5 { font-size: 20px'." }\n";
				$custom_css .= '.container h6 { font-size: 18px'." }\n";
				$custom_css .= '.container .small { font-size: 85% !important'." }\n";
				break;

				case 'very_large': // When default font size, no CSS entry
				$custom_css = '.container p, .container ul li, .container div, .container label, .container textarea, .container input { font-size: 22px !important'." }\n";
				$custom_css .= '.container input.search_field { height: 100%'." }\n";
				$custom_css .= '.container h1 { font-size: 44px'." }\n";
				$custom_css .= '.container h2 { font-size: 38px'." }\n";
				$custom_css .= '.container h3 { font-size: 32px'." }\n";
				$custom_css .= '.container h4 { font-size: 24px'." }\n";
				$custom_css .= '.container h5 { font-size: 22px'." }\n";
				$custom_css .= '.container h6 { font-size: 20px'." }\n";
				$custom_css .= '.container .small { font-size: 85% !important'." }\n";
				break;
			}
		}

		if( ! empty( $custom_css ) )
		{ // Function for custom_css:
		$custom_css = '<style type="text/css">
<!--
'.$custom_css.'
-->
		</style>';
		add_headline( $custom_css );
		}
	}


	/**
	 * Check if we can display a sidebar for the current layout
	 *
	 * @param boolean TRUE to check if at least one sidebar container is visible
	 * @return boolean TRUE to display a sidebar
	 */
	function is_visible_sidebar( $check_containers = false )
	{
		$layout = $this->get_setting( 'layout' );

		if( $layout != 'left_sidebar' && $layout != 'right_sidebar' )
		{ // Sidebar is not displayed for selected skin layout
			return false;
		}

		if( $check_containers )
		{ // Check if at least one sidebar container is visible
			return ( $this->show_container_when_access_denied( 'sidebar' ) ||  $this->show_container_when_access_denied( 'sidebar2' ) );
		}
		else
		{ // We should not check the visibility of the sidebar containers for this case
			return true;
		}
	}


	/**
	 * Get value for attbiute "class" of column block
	 * depending on skin setting "Layout"
	 *
	 * @return string
	 */
	function get_column_class()
	{
		switch( $this->get_setting( 'layout' ) )
		{
			case 'single_column':
				// Single Column Large
				return 'col-md-12';

			case 'single_column_normal':
				// Single Column
				return 'col-xs-12 col-sm-12 col-md-12 col-lg-10 col-lg-offset-1';

			case 'single_column_narrow':
				// Single Column Narrow
				return 'col-xs-12 col-sm-12 col-md-10 col-md-offset-1 col-lg-8 col-lg-offset-2';

			case 'single_column_extra_narrow':
				// Single Column Extra Narrow
				return 'col-xs-12 col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-6 col-lg-offset-3';

			case 'left_sidebar':
				// Left Sidebar
				return 'col-md-9 pull-right';

			case 'right_sidebar':
				// Right Sidebar
			default:
				return 'col-md-9';
		}
	}
}

?>