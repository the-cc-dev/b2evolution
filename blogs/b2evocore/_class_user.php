<?php
/**
 * This file implements Users
 *
 * b2evolution - {@link http://b2evolution.net/}
 *
 * Released under GNU GPL License - http://b2evolution.net/about/license.html
 *
 * @copyright (c)2003-2004 by Francois PLANQUE - {@link http://fplanque.net/}
 *
 * @package b2evocore
 */
require_once dirname(__FILE__). '/_class_dataobject.php';

class User extends DataObject
{
	var	$login;
	var	$pass;
	var	$firstname;
	var	$lastname;
	var	$nickname;
	var	$idmode;
	var	$email;
	var	$url;
	var	$icq;
	var	$aim;
	var	$msn;
	var	$yim;
	var	$ip;
	var	$domain;
	var	$browser;
	var	$datecreated;
	var	$level;
	var	$notify;

	var $Group;	// Pointer to group

	// Blog posts statuses permissions:
	var $blog_post_statuses = array();

	/*
	 * User::User(-)
	 *
	 * Constructor
	 */
	function User( $userdata = NULL )
	{
		global $tableusers;

		// Call parent constructor:
		parent::DataObject( $tableusers, 'user_' );

		if( $userdata == NULL )
		{
			// echo 'Creating blank user';
			$this->name = T_('New user');
			$this->login = 'login';
			$this->pass = 'pass';
			$this->firstname = '';
			$this->lastname = '';
			$this->nickname = '';
			$this->idmode = 'login';
			$this->email = '';
			$this->url = '';
			$this->icq = 0;
			$this->aim = '';
			$this->msn = '';
			$this->yim = '';
			$this->ip = '';
			$this->domain = '';
			$this->browser = '';
			$this->datecreated = date('Y-m-d H:i:s', time());	// We don't know local time here!
			$this->level = 0;
			$this->notify = 1;
			// Group for this user:
			$this->Group = Group_get_by_ID( 1 );
		}
		else
		{
			// echo 'Instanciating existing user';
			$this->ID = $userdata['ID'];
			$this->login = $userdata['user_login'];
			$this->pass = $userdata['user_pass'];
			$this->firstname = $userdata['user_firstname'];
			$this->lastname = $userdata['user_lastname'];
			$this->nickname = $userdata['user_nickname'];
			$this->idmode = $userdata['user_idmode'];
			$this->email = $userdata['user_email'];
			$this->url = $userdata['user_url'];
			$this->icq = $userdata['user_icq'];
			$this->aim = $userdata['user_aim'];
			$this->msn = $userdata['user_msn'];
			$this->yim = $userdata['user_yim'];
			$this->ip = $userdata['user_ip'];
			$this->domain = $userdata['user_domain'];
			$this->browser = $userdata['user_browser'];
			$this->datecreated = $userdata['dateYMDhour'];
			$this->level = $userdata['user_level'];
			$this->notify = $userdata['user_notify'];

			// Group for this user:
			$this->Group = Group_get_by_ID( $userdata['user_grp_ID'] );
		}
	}

	/**
	 * Get a param
	 *
	 * {@internal User::get(-)}}
	 */
	function get( $parname )
	{
		switch( $parname )
		{
			case 'preferedname':
				// Prefered name to display
				switch( $this->idmode )
				{
					case 'namefl':
						return parent::get('firstname'). ' '. parent::get('lastname');

					case 'namelf':
						return parent::get('lastname'). ' '. parent::get('firstname');

					default:
						return parent::get($this->idmode);
				}

			default:
			// All other params:
				return parent::get( $parname );
		}
	}

	/*
	 * User::set(-)
	 *
	 * Set param value
	 */
	function set( $parname, $parvalue )
	{
		switch( $parname )
		{
			// case 'icq':		// Dangerous: easy to forget it's not a string
			case 'level':
			case 'notify':
				parent::set_param( $parname, 'int', $parvalue );
			break;

			default:
				parent::set_param( $parname, 'string', $parvalue );
		}
	}

	/*
	 * User::set_datecreated(-)
	 *
	 * Set date created
	 */
	function set_datecreated( $datecreated )
	{
		// Set value:
		$this->datecreated = date('Y-m-d H:i:s', $datecreated );
		// Remmeber change for later db update:
		$this->dbchange( 'dateYMDhour' , 'string', 'datecreated' );
	}

	/*
	 * User::setGroup(-)
	 *
	 * Set new Group
	 */
	function setGroup( & $Group )
	{
		$this->Group = $Group;

		$this->dbchange( 'user_grp_ID', 'int', 'Group->get(\'ID\')' );
	}

	/**
	 * Check permission for this user
	 *
	 * {@internal User::check_perm(-) }}
	 *
	 * @param string Permission name, can be one of:
	 *								- 'upload'
	 *								- 'edit_timestamp'
	 *								- 'cats_post_statuses', see {@link User::check_perm_catsusers()}
	 *								- either group permission names, see {@link Group::check_perm()}
	 *								- either blogusers permission names, see {@link User::check_perm_blogusers()}
	 * @param string Permission level
	 * @param boolean Execution will halt if this is !0 and permission is denied
	 * @param mixed Permission target (blog ID, array of cat IDs...)
	 * @return boolean 0 if permission denied
	 */
	function check_perm( $permname, $permlevel = 'any', $assert = false, $perm_target = NULL )
	{
		global $use_fileupload, $fileupload_minlevel, $fileupload_allowedusers;

		$perm = false;

		switch( $permname )
		{ // What permission do we want to check?
			case 'upload':
				// Global permission to upload files...
				$perm = (($use_fileupload) && ($this->level) >= $fileupload_minlevel)
								&& ((ereg(' '. $this->login. ' ', $fileupload_allowedusers)) || (trim($fileupload_allowedusers)==''));
				break;

			case 'edit_timestamp':
				// Global permission to edit timestamps...
				$perm = ($this->level >= 5);
				break;

			case 'cats_post_statuses':
				// Category permissions...
				$perm = $this->check_perm_catsusers( $permname, $permlevel, $perm_target );
				break;

			case 'blog_properties':
				// Blog permission to edit its properties... (depending on user AND hits group)
				// Forward request to group:
				if( $this->Group->check_perm( 'blogs', $permlevel ) )
				{	// If group says yes
					$perm = true;
					break;
				}
				// Check user perm for this blog
				$perm = $this->check_perm_blogusers( $permname, $permlevel, $perm_target );
				break;

			case 'blog_post_statuses':
			case 'blog_del_post':
			case 'blog_comments':
			case 'blog_cats':
			case 'blog_genstatic':
				// Blog permission to this or that... (depending on this user only)
				$perm = $this->check_perm_blogusers( $permname, $permlevel, $perm_target );
				break;

			default:
				// Other global permissions (see if the group can handle them)
				// Forward request to group:
				$perm = $this->Group->check_perm( $permname, $permlevel );
		}

		if( !$perm && $assert )
		{ // We can't let this go on!
			die( T_('Permission denied!'). ' ('. $permname . '/'. $permlevel . ')' );
		}

		return $perm;
	}


	/**
	 * Check permission for this user on a set of specified categories
	 *
	 * This is not for direct use, please call {@link User::check_perm()} instead
	 *
	 * {@internal User::check_perm_catsusers(-) }}
	 *
	 * @see User::check_perm()
	 * @param string Permission name, can be one of the following:
	 *									- cat_post_statuses
	 *									- more to come later...
	 * @param string Permission level
	 * @param array Array of target cat IDs
	 * @return boolean 0 if permission denied
	 */
	function check_perm_catsusers( $permname, $permlevel, & $perm_target_cats )
	{
		// Check if permission is granted:
		switch( $permname )
		{
			case 'cats_post_statuses':
				// We'll actually pass this on to blog permissions
				// First we need to create an array of blogs, not cats
				$perm_target_blogs = array();
				foreach( $perm_target_cats as $loop_cat_ID )
				{
					$loop_cat_blog_ID = get_catblog( $loop_cat_ID );
					// echo "cat $loop_cat_ID -> blog $loop_cat_blog_ID <br/>";
					if( ! in_array( $loop_cat_blog_ID, $perm_target_blogs ) )
					{	// not already in list: add it:
						$perm_target_blogs[] = $loop_cat_blog_ID;
					}
				}
				// Now we'll check permissions for each blog:
				foreach( $perm_target_blogs as $loop_blog_ID )
				{
					if( ! $this->check_perm_blogusers( 'blog_post_statuses', $permlevel, $loop_blog_ID ) )
					{	// If at least one blog is denied:
						return false;	// permission denied
					}
				}
				return true;	// Permission granted
		}

		return false; 	// permission denied
	}


	/**
	 * Check permission for this user on a specified blog
	 *
	 * This is not for direct use, please call {@link User::check_perm()} instead
	 *
	 * {@internal User::check_perm_blogusers(-) }}
	 *
	 * @see User::check_perm()
	 * @param string Permission name, can be one of the following:
	 *									- blog_post_statuses
	 *									- blog_del_post
	 *									- blog_comments
	 *									- blog_cats
	 *									- blog_properties
	 *									- blog_genstatic
	 * @param string Permission level
	 * @param integer Permission target blog ID
	 * @return boolean 0 if permission denied
	 */
	function check_perm_blogusers( $permname, $permlevel, $perm_target_blog )
	{
		global $tableblogusers, $querycount;
		// echo "checkin for $permname >= $permlevel on blog $perm_target_blog<br />";

		if( !isset( $this->blog_post_statuses[$perm_target_blog] ) )
		{	// Allowed blog post statuses have not been loaded yet:
			if( $this->ID == 0 )
			{	// User not in DB, nothing to load!:
				return false;	// Permission denied
			}

			// Load now:
			// echo 'loading allowed statuses';
			$query = "SELECT *
								FROM $tableblogusers
								WHERE bloguser_blog_ID = $perm_target_blog
								  AND bloguser_user_ID = $this->ID";
			// echo $query, '<br />';
			$result = mysql_query($query) or mysql_oops( $query );
			$querycount++;
			$row = mysql_fetch_array($result);

			$this->blog_post_statuses[$perm_target_blog] = array();

			$bloguser_perm_post = $row['bloguser_perm_poststatuses'];
			if( empty($bloguser_perm_post ) )
				$this->blog_post_statuses[$perm_target_blog]['blog_post_statuses'] = array();
			else
				$this->blog_post_statuses[$perm_target_blog]['blog_post_statuses'] = explode( ',', $bloguser_perm_post );

			$this->blog_post_statuses[$perm_target_blog]['blog_del_post'] = $row['bloguser_perm_delpost'];
			$this->blog_post_statuses[$perm_target_blog]['blog_comments'] = $row['bloguser_perm_comments'];
			$this->blog_post_statuses[$perm_target_blog]['blog_cats'] = $row['bloguser_perm_cats'];
			$this->blog_post_statuses[$perm_target_blog]['blog_properties'] = $row['bloguser_perm_properties'];
		}

		// Check if permission is granted:
		switch( $permname )
		{
			case 'blog_genstatic':
				return ($this->level >= 2);

			case 'blog_post_statuses':
				if( $permlevel == 'any' )
				{ // Any prermission will do:
					// echo count($this->blog_post_statuses);
					return ( count($this->blog_post_statuses[$perm_target_blog]['blog_post_statuses']) > 0 );
				}

				// We want a specific permission:
				// echo 'checking :', implode( ',', $this->blog_post_statuses  ), '<br />';
				return in_array( $permlevel, $this->blog_post_statuses[$perm_target_blog]['blog_post_statuses'] );

			default:
				// echo $permname, '=', $this->blog_post_statuses[$perm_target_blog][$permname], ' ';
				return $this->blog_post_statuses[$perm_target_blog][$permname];
		}
	}


	/*
	 * User::is_blog_member(-)
	 *
	 * Check if user is a member of specified blog
	 */
	function is_blog_member( $blog_ID )
	{
		/* echo 'blog=',$blog_ID ,'<br />';
		echo 'statuses=', $this->check_perm_blogusers( 'blog_post_statuses', 'any', $blog_ID ) ,'<br />';
		echo 'statuses=', $this->check_perm_blogusers( 'blog_del_post', 1, $blog_ID )  ,'<br />';
		echo 'statuses=', $this->check_perm_blogusers( 'blog_comments', 1, $blog_ID )  ,'<br />'; */
		return ( $this->check_perm_blogusers( 'blog_post_statuses', 'any', $blog_ID )
					|| $this->check_perm_blogusers( 'blog_del_post', 1, $blog_ID )
					|| $this->check_perm_blogusers( 'blog_comments', 1, $blog_ID )
					|| $this->check_perm_blogusers( 'blog_cats', 1, $blog_ID )
					|| $this->check_perm_blogusers( 'blog_properties', 1, $blog_ID )
					);
	}


	/**
	 * Delete user and dependencies from database
	 *
	 * Deleted dependencies:
	 * - users's posts
	 * - comments on this users' posts
	 * - user/blog permissions
	 *
	 * {@internal User::dbdelete(-) }}
	 *
	 * @todo delete comments on user's posts
	 */
	function dbdelete()
	{
		global $querycount, $tablecomments, $tableposts, $tableblogusers;

		if( $this->ID == 0 ) die( T_('Non persistant object cannot be deleted!') );

		// Delete comments
	/*	$sql="DELETE FROM $tablecomments INNER JOIN $tableposts
												ON comment_post_ID = ID
								 WHERE post_author = $this->ID";
		$result=mysql_query($sql) or mysql_oops( $sql );
		$querycount++; */

		// Delete posts
		$sql="DELETE FROM $tableposts WHERE post_author = $this->ID";
		$result=mysql_query($sql) or mysql_oops( $sql );
		$querycount++;

		// Delete userblog permission
		$sql="DELETE FROM $tableblogusers WHERE bloguser_user_ID = $this->ID";
		$result=mysql_query($sql) or mysql_oops( $sql );
		$querycount++;

		// Delete main object:
		return parent::dbdelete();
	}


	/**
	 * Template function: display user's level
	 *
	 * {@internal User::level(-) }}
	 */
	function level()
	{
		$this->disp( 'level', 'raw' );
	}


	/**
	 * Template function: display user's login
	 *
	 * {@internal User::login(-) }}
	 *
	 * @param string Output format, see {@link format_to_output()}
	 */
	function login( $format = 'htmlbody' )
	{
		$this->disp( 'login', $format );
	}


	/**
	 * Template function: display user's prefered name
	 *
	 * {@internal User::prefered_name(-) }}
	 *
	 * @param string Output format, see {@link format_to_output()}
	 */
	function prefered_name( $format = 'htmlbody' )
	{
		$this->disp( 'preferedname', $format );
	}

	/**
	 * Template function: display user's URL
	 *
	 * {@internal User::url(-) }}
	 *
	 * @param string string to display before the date (if changed)
	 * @param string string to display after the date (if changed)
	 * @param string Output format, see {@link format_to_output()}
	 */
	function url( $before = '', $after = '', $format = 'htmlbody' )
	{
		if( !empty( $this->url ) )
		{
			echo $before;
			$this->disp( 'url', $format );
			echo $after;
		}
	}

}
?>
