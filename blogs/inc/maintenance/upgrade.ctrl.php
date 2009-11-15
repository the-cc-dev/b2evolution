<?php
/**
 * Upgrade - This is a LINEAR controller
 *
 * This file is part of b2evolution - {@link http://b2evolution.net/}
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2009 by Francois PLANQUE - {@link http://fplanque.net/}
 * Parts of this file are copyright (c)2009 by The Evo Factory - {@link http://www.evofactory.com/}.
 *
 * Released under GNU GPL License - {@link http://b2evolution.net/about/license.html}
 *
 * {@internal Open Source relicensing agreement:
 * The Evo Factory grants Francois PLANQUE the right to license
 * The Evo Factory's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package maintenance
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author efy-maxim: Evo Factory / Maxim.
 * @author fplanque: Francois Planque.
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * @var instance of User class
 */
global $current_User;

/**
 * @vars string paths
 */
global $basepath, $upgrade_path, $install_path;

// Check minimum permission:
$current_User->check_perm( 'perm_maintenance', 'upgrade', true );

// Set options path:
$AdminUI->set_path( 'tools', 'upgrade' );

// Get action parameter from request:
param_action();

// Display <html><head>...</head> section! (Note: should be done early if actions do not redirect)
$AdminUI->disp_html_head();

// Display title, menu, messages, etc. (Note: messages MUST be displayed AFTER the actions)
$AdminUI->disp_body_top();

$AdminUI->disp_payload_begin();

/**
 * Display payload:
 */
switch( $action )
{
	case 'start':
	default:
		// STEP 1: Check for updates.
		$block_item_Widget = & new Widget( 'block_item' );
		$block_item_Widget->title = T_('Updates from b2evolution.net');
		$block_item_Widget->disp_template_replaced( 'block_start' );

		// Note: hopefully, the update will have been downloaded in the shutdown function of a previous page (including the login screen)
		// However if we have outdated info, we will load updates here.
		load_funcs( 'dashboard/model/_dashboard.funcs.php' );
		// Let's clear any remaining messages that should already have been displayed before...
		$Messages->clear( 'all' );
		b2evonet_get_updates( true );

		// Display info & error messages
		echo $Messages->display( NULL, NULL, false, 'all', NULL, NULL, 'action_messages' );


		/**
		 * @var AbstractSettings
		 */
		global $global_Cache;

		// Display the current version info for now. We may remove this in the future.
		$version_status_msg = $global_Cache->get( 'version_status_msg' );
		if( !empty($version_status_msg) )
		{	// We have managed to get updates (right now or in the past):
			echo '<p>'.$version_status_msg.'</p>';
			$extra_msg = $global_Cache->get( 'extra_msg' );
			if( !empty($extra_msg) )
			{
				echo '<p>'.$extra_msg.'</p>';
			}
		}

		// Extract available updates:
		$updates = $global_Cache->get( 'updates' );

		// DEBUG:
		// $updates[0]['url'] = 'http://xxx/b2evolution-1.0.0.zip'; // TODO: temporary URL

		$action = 'start';

		break;

	case 'download':
		// STEP 2: DOWNLOAD.
		$block_item_Widget = & new Widget( 'block_item' );
		$block_item_Widget->title = T_('Downloading, unzipping & installing package...');
		$block_item_Widget->disp_template_replaced( 'block_start' );

		$download_url = param( 'upd_url', 'string' );

		$upgrade_name = param( 'upd_name', 'string' );
		$upgrade_file = $upgrade_path.$upgrade_name.'.zip';

		if( $success = prepare_maintenance_dir( $upgrade_path, true ) )
		{
			// Set maximum execution time
			set_max_execution_time( 1800 ); // 30 minutes

			echo '<p>'.sprintf( T_( 'Downloading package to &laquo;<strong>%s</strong>&raquo;...' ), $upgrade_file ).'</p>';
			flush();

			if( !$success = copy( $download_url, $upgrade_file ) )
			{
				echo '<p style="color:red">'.sprintf( T_( 'Unable to download package from &laquo;%s&raquo;' ), $download_url ).'</p>';
				flush();

				// Additional check
				@unlink( $upgrade_file );
			}
		}

	case 'unzip':
		// STEP 3: UNZIP.
		if( !isset( $block_item_Widget ) )
		{
			$block_item_Widget = & new Widget( 'block_item' );
			$block_item_Widget->title = T_('Unzipping & installing package...');
			$block_item_Widget->disp_template_replaced( 'block_start' );

			$upgrade_name = param( 'upd_name', 'string' );
			$upgrade_file = $upgrade_path.$upgrade_name.'.zip';

			$success = true;
		}

		if( $success )
		{
			// Set maximum execution time
			set_max_execution_time( 1800 ); // 30 minutes

			echo '<p>'.sprintf( T_( 'Unpacking package to &laquo;<strong>%s</strong>&raquo;...' ), $upgrade_path.$upgrade_name ).'</p>';
			flush();

			// Unpack package
			if( $success = unpack_archive( $upgrade_file, $upgrade_path.$upgrade_name, true ) )
			{
				$new_version_status = check_version( $upgrade_name );
				if( !empty( $new_version_status ) )
				{
					echo '<h5 style="color:red">'.$new_version_status.'</h5>';
					break;
				}
			}
			else
			{
				// Additional check
				@rmdir_r( $upgrade_path.$upgrade_name );
			}
		}

	case 'install':
		// STEP 4: INSTALL.
		if( !isset( $block_item_Widget ) )
		{
			$block_item_Widget = & new Widget( 'block_item' );
			$block_item_Widget->title = T_('Installing package...');
			$block_item_Widget->disp_template_replaced( 'block_start' );

			$upgrade_name = param( 'upd_name', 'string' );

			$success = true;
		}

		// Enable maintenance mode
		if( $success && switch_maintenance_mode( true, T_( 'System upgrade is in progress. Please reload this page in a few minutes.' ) ) )
		{
			// Set maximum execution time
			set_max_execution_time( 1800 ); // 30 minutes

			// Verify that all destination files can be overwritten
			echo '<h4 style="color:green">'.T_( 'Verifying that all destination files can be overwritten...' ).'</h4>';
			flush();

			$read_only_list = array();
			verify_overwrite( $upgrade_path.$upgrade_name, no_trailing_slash( $basepath ), 'Verifying', false, $read_only_list );

			if( empty( $read_only_files ) )
			{	// We can do backup files and database

				// Load Backup class (PHP4) and backup all of the folders and files
				load_class( 'maintenance/model/_backup.class.php', 'Backup' );
				$Backup = & new Backup();
				$Backup->include_all();

				if( !extension_loaded( 'zip' ) )
				{
					$Backup->pack_backup_files = false;
				}

				// Start backup
				if( $success = $Backup->start_backup() )
				{	// We can upgrade files and database

					// Copying new folders and files
					echo '<h4 style="color:green">'.T_( 'Copying new folders and files...' ).'</h4>';
					flush();

					verify_overwrite( $upgrade_path.$upgrade_name, no_trailing_slash( $basepath ), 'Copying', true );

					// Upgrade database using regular upgrader script
					require_once( $install_path.'/_functions_install.php' );
					require_once( $install_path.'/_functions_evoupgrade.php' );

					echo '<h4 style="color:green">'.T_( 'Upgrading data in existing b2evolution database...' ).'</h4>';
					flush();

					global $DB;

					$DB->begin();
					if( $success = upgrade_b2evo_tables() )
					{
						$DB->commit();
					}
					else
					{
						$DB->rollback();
					}
				}
			}
			else
			{
				echo '<p style="color:red">'.T_( '<strong>The following folders and files can\'t be overwritten:</strong>' ).'</p>';
				foreach( $read_only_files as $read_only_file )
				{
					echo $read_only_file.'<br/>';
				}
				$success = false;
			}
		}

		// Disable maintenance mode
		switch_maintenance_mode( false );

		if( $success )
		{
			echo '<h5 style="color:green">'.T_( 'Upgrade completed successfully!' ).'</h5>';
		}
		else
		{
			echo '<h5 style="color:red">'.T_( 'Upgrade failed!' ).'</h5>';
		}

		break;
}

if( isset( $block_item_Widget ) )
{
	$block_item_Widget->disp_template_replaced( 'block_end' );
	$AdminUI->disp_view( 'maintenance/views/_upgrade.form.php' );
}

$AdminUI->disp_payload_end();

// Display body bottom, debug info and close </html>:
$AdminUI->disp_global_footer();


/*
 * $Log$
 * Revision 1.5  2009/11/15 19:44:02  fplanque
 * minor
 *
 * Revision 1.4  2009/10/22 10:52:57  efy-maxim
 * upgrade - messages
 *
 * Revision 1.3  2009/10/21 14:27:39  efy-maxim
 * upgrade
 *
 * Revision 1.2  2009/10/20 14:38:54  efy-maxim
 * maintenance modulde: downloading - unpacking - verifying destination files - backing up - copying new files - upgrade database using regular script (Warning: it is very unstable version! Please, don't use maintenance modulde, because it can affect your data )
 *
 * Revision 1.1  2009/10/18 20:15:51  efy-maxim
 * 1. backup, upgrade have been moved to maintenance module
 * 2. maintenance module permissions
 *
 */
?>