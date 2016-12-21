<?php
/**
 * This file implements the evo md5 Password Driver class.
 *
 * This file is part of the evoCore framework - {@link http://evocore.net/}
 * See also {@link https://github.com/b2evolution/b2evolution}.
 *
 * @license GNU GPL v2 - {@link http://b2evolution.net/about/gnu-gpl-license}
 *
 * @copyright (c)2003-2016 by Francois Planque - {@link http://fplanque.com/}
 *
 * @package evocore
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );


load_class( 'users/model/passwords/_passworddriver.class.php', 'PasswordDriver' );

/**
 * evoMd5PasswordDriver Class
 *
 * @package evocore
 */
class evoMd5PasswordDriver extends PasswordDriver
{
	const CODE = 'evo$md5';


	/**
	 * Hash password
	 *
	 * @param string Password
	 * @param string Salt (Not used by this password driver)
	 * @return string Hashed password
	 */
	public function hash( $password, $salt = '' )
	{
		return md5( $password );
	}
}
?>