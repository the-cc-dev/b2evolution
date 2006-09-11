<?php
/**
 * This file implements the UI view for the Blog display properties.
 *
 * b2evolution - {@link http://b2evolution.net/}
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 * @copyright (c)2003-2006 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package admin
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var Blog
 */
global $edited_Blog;

global $tab, $blog;

$Form = new Form();

$Form->begin_form( 'fform' );

$Form->hidden_ctrl();
$Form->hidden( 'action', 'update' );
$Form->hidden( 'tab', $tab );
$Form->hidden( 'blog', $blog );

$Form->begin_fieldset( T_('Description') );
	$Form->text( 'blog_tagline', $edited_Blog->get( 'tagline' ), 50, T_('Tagline'), T_('This is diplayed under the blog name on the blog template.'), 250 );
	$Form->textarea( 'blog_longdesc', $edited_Blog->get( 'longdesc' ), 5, T_('Long Description'), T_('This is displayed on the blog template.'), 50, 'large' );
$Form->end_fieldset();

$Form->begin_fieldset( T_('Skin and style') );
	$Form->select( 'blog_default_skin', $edited_Blog->get( 'default_skin' ), 'skin_options_return', T_('Default skin') , T_('This is the default skin that will be used to display this blog.') );
	$Form->checkbox( 'blog_force_skin', 1-$edited_Blog->get( 'force_skin' ), T_('Allow skin switching'), T_('Users will be able to select another skin to view the blog (and their preferred skin will be saved in a cookie).') );
	$Form->checkbox( 'blog_allowblogcss', $edited_Blog->get( 'allowblogcss' ), T_('Allow customized blog CSS file'), T_('A CSS file in the blog media directory will override the default skin stylesheet.') );
	$Form->checkbox( 'blog_allowusercss', $edited_Blog->get( 'allowusercss' ), T_('Allow user customized CSS file for this blog'), T_('Users will be able to override the blog and skin stylesheet with their own.') );
$Form->end_fieldset();

$Form->begin_fieldset( T_('List of public blogs') );
	$Form->checkbox( 'blog_disp_bloglist', $edited_Blog->get( 'disp_bloglist' ), T_('Display public blog list'), T_('Check this if you want to display the list of all blogs on your blog page (if your skin supports this).') );
	$Form->checkbox( 'blog_in_bloglist', $edited_Blog->get( 'in_bloglist' ), T_('Include in public blog list'), T_('Check this if you want this blog to be displayed in the list of all public blogs.') );
$Form->end_fieldset();

$Form->begin_fieldset( T_('Link blog / Blogroll') );
	$BlogCache = & get_Cache( 'BlogCache' );
	$Form->select_object( 'blog_links_blog_ID', $edited_Blog->get( 'links_blog_ID' ), $BlogCache, T_('Default linkblog'), T_('Will be displayed next to this blog (if your skin supports this).'), true );
$Form->end_fieldset();

$Form->buttons( array( array( 'submit', 'submit', T_('Save !'), 'SaveButton' ),
													array( 'reset', '', T_('Reset'), 'ResetButton' ) ) );

$Form->end_form();

/*
 * $Log$
 * Revision 1.3  2006/09/11 19:35:35  fplanque
 * minor
 *
 */
?>