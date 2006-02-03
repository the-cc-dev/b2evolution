<?php
/**
 * This file implements the ResultSel class.
 *
 * This file is part of the b2evolution/evocms project - {@link http://b2evolution.net/}.
 * See also {@link http://sourceforge.net/projects/evocms/}.
 *
 * @copyright (c)2003-2005 by Francois PLANQUE - {@link http://fplanque.net/}.
 * Parts of this file are copyright (c)2004 by PROGIDISTRI - {@link http://progidistri.com/}.
 *
 * @license http://b2evolution.net/about/license.html GNU General Public License (GPL)
 * {@internal
 * b2evolution is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * b2evolution is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with b2evolution; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * }}
 *
 * {@internal
 * PROGIDISTRI grants Francois PLANQUE the right to license
 * PROGIDISTRI's contributions to this file and the b2evolution project
 * under any OSI approved OSS license (http://www.opensource.org/licenses/).
 * }}
 *
 * @package evocore
 *
 * {@internal Below is a list of authors who have contributed to design/coding of this file: }}
 * @author fplanque: Francois PLANQUE
 * @author fsaya: Fabrice SAYA-GASNIER / PROGIDISTRI
 *
 * @version $Id$
 */
if( !defined('EVO_MAIN_INIT') ) die( 'Please, do not access this page directly.' );

/**
 * Includes:
 */
require_once dirname(__FILE__).'/_results.class.php';


if( false )
{	/**
	 * This is ugly, sorry, but I temporarily need this until NuSphere fixes their CodeInsight :'(
	 */
	include('_main.inc.php');
}

/**
 * ResultSel class: displays Results and provides Selection capabilities
 *
 */
class ResultSel extends Results
{
	/**
	 * var Form
	 */
	var $Form;

	var $current_selection_ID;
	var $table_selections;
	var $field_selected;
	var $field_selection;


	/**
	 * Constructor
	 *
	 * @param string fieldname of item ID to select on
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param string
	 * @param integer current selection ID
	 * @param string SQL query
	 * @param NULL|string SQL query used to count the total # of rows (if NULL, we'll try to COUNT(*) by ourselves)
	 * @param string prefix to differentiate page/order params when multiple Results appear one same page
	 * @param string default ordering of columns (special syntax) if not URL specified
	 * @param integer number of lines displayed on one screen
	 */
	function ResultSel( $field_ID, $table_selections, $field_sel_ID, $field_sel_name,
											$table_objsel, $field_selected, $field_selection, $current_selection_ID,
											$sql, $count_sql = NULL, $param_prefix = '', $default_order = '', $limit = 20 )
	{
		global $current_User;

		// Call parent:
		parent::Results( $sql, $param_prefix, $default_order, $limit, $count_sql );

		if( ! $current_User->check_perm( 'selections', 'view' ) )
		{	// User is NOT allowed to view selections
			// Don't do any more then base class:
			return;
		}

		$this->current_selection_ID = $current_selection_ID;
		$this->table_selections     = $table_selections;
		$this->field_sel_ID         = $field_sel_ID;
		$this->field_sel_name       = $field_sel_name;
		$this->table_objsel         = $table_objsel;
		$this->field_selected       = $field_selected;
		$this->field_selection      = $field_selection;

		// Presets a selection checkbox:
		$this->cols[] = array(
						'th' => /* TRANS: abbr. for "Selection" */ T_('Sel'),
						'td_start' => '<td class="firstcol shrinkwrap">',
						'td' => '%selection_checkbox( #'.$field_ID.'#, \''.$param_prefix.'\' )%',
					);
	}


	/**
	 * Display list/table start preceeded by <form> opening.
	 */
	function display_list_start()
	{
		global $item_ID_array, $current_User;

		if( ! $current_User->check_perm( 'selections', 'view' ) )
		{	// User is NOT allowed to view selections
			// Don't do any more then base class:
			parent::display_list_start();
			return;
		}

		$this->Form = new Form( regenerate_url(), $this->param_prefix.'selections_checkchanges', 'post', 'none' ); // COPY!!

		$this->Form->begin_form( '' );

		if( $this->total_pages > 0 )
		{	// We have rows to display, we want the selection stuff:

			// Need it to check in the next page if the selection has to be updated
			$this->Form->hidden( $this->param_prefix.'previous_sel_ID', $this->current_selection_ID );

			// Sets the cols_check global variable to verify if checkboxes
			// have to be checked in the result set :
			cols_check( $this->current_selection_ID, $this->table_objsel, $this->field_selected, $this->field_selection );

			// item_ID_array must be emptied to avoid conflicts with previous result sets :
			// TODO: put this into object
			$item_ID_array = array();
		}

		// list/table start:
		parent::display_list_start();
	}


  /**
   * Display the filtering form WITHOUT starting a new form!
   *
   * We must NOT create a new form since we already have one for the selection!!
   */
	function display_filters()
	{
		if( !empty($this->filters_callback) )
		{
			echo $this->replace_vars( $this->params['filters_start'] );

			echo T_('Filters').': ';

			$func = $this->filters_callback;

			if( ! $func( $this->Form ) )
			{	// Function has not displayed the filter button yet:
				$this->Form->submit( array( 'filter_submit', T_('Filter list'), 'search' ) );
			}

			echo $this->params['filters_end'];
		}
	}


	/**
	 * Display list/table end followed by </form> closing.
	 *
	 * Typically outputs </ul> or </table>
	 */
	function display_list_end()
	{
		global $item_ID_array, $current_User;

		if( ! $current_User->check_perm( 'selections', 'view' ) )
		{	// User is NOT allowed to view selections
			// Don't do any more then base class:
			parent::display_list_end();
			return;
		}


		if( $this->total_pages > 0 )
		{	// We have rows to display, we want the selection stuff:

			echo $this->replace_vars( $this->params['functions_start'] );

			$can_edit = $current_User->check_perm( 'selections', 'edit' );

			if( $can_edit )
			{ // links to check all and uncheck all
				echo $this->Form->check_all();
			}

			// construction of the select menu :
			$selection_name = selection_select_tag( $this->param_prefix, $this->table_selections, $this->field_sel_name, $this->field_sel_ID, $this->current_selection_ID );

			if( $can_edit )
			{
				$this->Form->text( 'selection_'.$this->param_prefix.'name', $selection_name, 25, T_('Selection name') );

				// List of IDs displayed on this page (needed for deletes):
				$this->Form->hidden( 'item_ID_list', implode( $item_ID_array, ',' ) );

				// actionArray[update_selection] is experimental
				$this->Form->submit( array( 'actionArray[update_'.$this->param_prefix.'selection]', T_('Update selection'), 'SaveButton' ) );
			}

			echo $this->replace_vars( $this->params['functions_end'] );

		}

		
		// list/table end:
		parent::display_list_end();


		$this->Form->end_form();

	}


}


/**
 * Sets the cols_check global variable to verify if checkboxes have to be checked in the result set
 *
 * @param the selection ID
 * @param the name of the attachment table (which links the items and the selections
 * @param the item id field
 * @param the selection id field
 */
function cols_check( $selection_ID, $sel_table, $sel_table_item, $sel_table_selection )
{
	global $DB, $cols_check;

	if( $selection_ID !== 0 )
	{
		$sql_check = 'SELECT '.$sel_table_item.' FROM '.$sel_table.' WHERE '.$sel_table_selection.'='.$selection_ID;
		$cols_check = $DB->get_col( $sql_check );
	}
	else
	{
		$cols_check = array();
	}
}


/**
 * Display a checkbox allowing to add the item to a selection
 *
 * Only one checkbox will be displayed for each ID.
 * IDs which are already in the selection will be pre-checked.
 *
 * @deprecated should go into ResultSel class
 *
 * @param integer item ID
 * @param string item name / prefix for form values
 * @return string the correct input tag
 */
function selection_checkbox( $item_ID, $param_prefix )
{
	global $current_User;
	// List of checkboxes to pre-check:
	global $cols_check;
	// List of already displayed checkboxes (can be used outside to get a list of checkboxes which have been displayed)
	global $item_ID_array;

	if( in_array( $item_ID, $item_ID_array ) )
	{	// We have already displayed a checkbox for this ID
		return '&nbsp;';	// nbsp is for IE...
	}

	$item_ID_array[] = $item_ID; //construction of the ID list

	$r = '';

	if( $current_User->check_perm( 'selections', 'edit' ) )
	{	// User is allowed to edit
		$r .= '<span name="surround_check" class="checkbox_surround_init"><input type="checkbox" class="checkbox" name="'.$param_prefix.'items[]" value='.$item_ID;
		if( in_array( $item_ID, $cols_check ) )
		{	// already in selection:
			$r .= ' checked="checked" ';
		}
		$r .= ' /></span>';
	}
	else
	{	// User CANNOT edit:
		if( in_array( $item_ID, $cols_check ) )
		{	// already in selection:
			$r .= '*';
		}
	}

	return $r;
}


/**
 * Creates the select tag in the search menu and fills it with the appropriate option tags
 *
 * @param string the selection category prefix(the one used during the construction of the result object)
 * @param string the name of the table containing the selections
 * @param string the name field  in the selection table
 * @param string the id field in the selection table
 * @param integer the current selection id
 */
function selection_select_tag(
															$category_prefix,
															$selections_table,
															$selections_table_name,
															$selections_table_ID,
															$selection_ID
															)
{
	global $DB, $selection_name, $current_User;

	$r = T_('Selection');
	$r .= ' <select name="selection_'.$category_prefix.'ID"
					onchange="selform = get_form( this );
						selform.elements[\''.$category_prefix.'previous_sel_ID\'].value=-1;
						selform.submit()" >'."\n";
	// in the onchange attribute, option_db is set to -1 to avoid updating the database

	if( $current_User->check_perm( 'selections', 'edit' ) )
	{	// User is allowed to edit
		$r .= '<option value="0">'.T_('New selection')."</option>\n";
	}
	else
	{	// User CANNOT edit:
		$r .= '<option value="0">'.T_('None')."</option>\n";
	}

	$sql = 'SELECT * FROM '.$selections_table.' ORDER BY '.$selections_table_name;
	$rows = $DB->get_results( $sql );
	if( !empty( $rows ) )
	{
		$selection_name = '';
		foreach( $rows as $row )
		{ // construction of the option tags
			if( $row->$selections_table_ID == $selection_ID )
			{ // option selected by default
				$selected = ' selected="selected" ';
				$selection_name = $row->$selections_table_name;
			}
			else
			{
				$selected = '';
			}
			$r .= '<option value="'.$row->$selections_table_ID.'" '.$selected.' >'.$row->$selections_table_name."</option>\n";
		}
	}
	$r .= "</select>\n\n";

	echo $r;

	return $selection_name;
}


/**
 * Handle selection action
 * 
 * Determine if we need to perform an action to the current selection and do it in this case. 
 *  
 * @param integer the current selection id
 * @param string prefix (cont_, etab_, firm_, ..)
 * @param string selection prefix (cocs_, etes_, fifs_, ..)
 */
function handle_selection_actions( $selection_ID, $prefix, $prefix_sel )
{
	$previous_sel_ID = param( $prefix.'previous_sel_ID', 'integer', 0, true );// previous selection ID, need it to check for updating sel
	
	if( $selection_ID == $previous_sel_ID ) // the databse has to be updated
	{	// The selected ID is the same than the edited one in the previous page 	
		if( $selection_ID == 0 )
		{ // A new selection must be created
			$action = 'create';
		}
		elseif( $selection_ID >0 )
		{ // An existing selection is being updated
			$action = 'update';
		}
		// Get the selection name
		$selection_name = param( 'selection_'.$prefix.'name', 'string', '', true );

		// Do the slection action
		selection_action( $action, $selection_ID, $selection_name, $prefix, $prefix_sel );
	}
}

		
/**
 * Manages the various database changes to make on selections
 *
 * @param string the action currently effectuated
 * @param integer the current selection id
 * @param string the current selection name
 * @param string prefix (cont_, etab_, firm_, ..)
 * @param string selection prefix (cocs_, etes_, fifs_, ..)
 */
function selection_action( $action, $selection_ID, $selection_name, $prefix, $prefix_sel )
{ // the form has been submitted to act on the database and not only to change the display

	global $DB, $Messages, $confirm, $item_ID_list, $current_User;
	
	$items = param( $prefix.'items', 'array', array(), false );	// do NOT memorize // ?????????????
	param( 'item_ID_list', 'string', '', false );
	
	$current_User->check_perm( 'selections', 'edit', true );


	// Set global vars, selection_.prefix.ID, selection_.prefix_name 
	$selection_prefix_ID = 'selection_'.$prefix.'ID';
	$selection_prefix_name = 'selection_'.$prefix.'name';
	global $$selection_prefix_ID, $$selection_prefix_name;
	
	// Moche ms bon...
	$selections_table = 'T_'.substr($prefix,0,strlen($prefix)-1).'selections';
	$sel_table = 'T_'.$prefix.substr($prefix,0,1).'sel';	
	$selections_table_name = substr($prefix,0,1).'sel_name';
	$selections_table_id = substr($prefix,0,1).'sel_ID';
	$sel_table_selection = $prefix_sel.$selections_table_id;
	$sel_table_item = $prefix_sel.$prefix.'ID';


	switch( $action )
	{

		// creation of a new selection
		case 'create':

			if( empty($selection_name) )
			{	// No name provided:
				$Messages->add( T_('Cannot create a selection with an empty name'), 'error' );
				// Abord creation!
				break;
			}

			$sql_selections = "INSERT INTO $selections_table ( $selections_table_name )
															VALUES( ".$DB->quote($selection_name)." ) ";		 // construction of the query
			$DB->query( $sql_selections ); // insertion of a new selection in the database

			$selection_ID = mysql_insert_id(); // id generated by the last sql query

			if( !empty( $items ) )
			{ // nothing must be inserted if no items are selected
				$sql_sel = 'INSERT INTO '.$sel_table.'( '.$sel_table_item.', '.$sel_table_selection .')  VALUES ';
				$sel_array = array();

				$i = 0;
				foreach( $items as $item )
				{ // construction of the sql query depending on selected values in the result table
					$sel_array[$i++] = ' ('.$item.','.$selection_ID.' ) ';
				}
				$sql_sel .= implode( $sel_array, ',' );
				$DB->query( $sql_sel ); // insertion of the relation between selections and items in the database
			}

			// Set $selection_.$prefix.ID var
			$sel_prefix_ID = 'selection_'.$prefix.'ID';
			$$sel_prefix_ID = $selection_ID;
			// Set $selection_.$prefix.name var
			$sel_prefix_name = 'selection_'.$prefix.'name';
			$$sel_prefix_name = $selection_name;

			$Messages->add( T_('Selection created.'), 'success' );

			break;


		case 'edit':
		case 'update':
			// update of an existing selection
			$DB->begin();

 			if( empty($selection_name) )
			{	// No name provided:
				$Messages->add( T_('Please provide a selection name.'), 'error' );
			}
			else
			{	// Update name:
				$sql_selections = "UPDATE $selections_table
															SET $selections_table_name = ".$DB->quote($selection_name)."
														WHERE $selections_table_id = $selection_ID"; // construction of the update query
				$DB->query( $sql_selections );
			}

			if( preg_match( '#[0-9,]+#', $item_ID_list ) )
			{ // check the format of the item list to avoid sql injection
				$sql_delete = 'DELETE FROM '.$sel_table.' WHERE '.$sel_table_selection.' = '.$selection_ID
				            .' AND '.$sel_table_item.' IN ('.$item_ID_list.')'; // deletion of the former db entries
				$DB->query( $sql_delete );

				$Messages->add( T_('Obsolete selection entries deleted.'), 'success' );
			}

			if( !empty( $items ) )
			{ // there have been some items selected in the result table: they must be inserted into the database
				$sql_sel = 'INSERT INTO '.$sel_table.'( '.$sel_table_item.', '.$sel_table_selection .')  VALUES ';
				$sel_array = array();

				foreach( $items as $item )
				{ // construction of the sql query depending on selected values in the result table
					$sel_array[] = ' ( '.$item.', '.$selection_ID.' ) ';
				}
				$sql_sel .= implode( $sel_array, ',' );
				$DB->query( $sql_sel ); // insertion of the relation between selections and items in the database

				$Messages->add( T_('New selections entries inserted.'), 'success' );
			}

			$DB->commit();
			break;


		case 'copy':
			// creation of a new selection with the same name
			$sql_selections = 'INSERT INTO '.$selections_table.'('.$selections_table_name.
												') VALUES( "'.$selection_name.'" )';
			$DB->query( $sql_selections );
			$Messages->add( T_('Selection copied.'), 'success' );

			$new_selection_ID = mysql_insert_id();// gets the new selection id

			// creation of the links between the new selection and the selected items
			$sql_sel = 'INSERT INTO '.$sel_table.'( '.$sel_table_item.', '.$sel_table_selection.' ) '
								 .'SELECT '.$sel_table_item.', '.$new_selection_ID.' FROM '.$sel_table.' WHERE '
												.$sel_table_selection.'='.$selection_ID;
			$DB->query( $sql_sel );
			$Messages->add( T_('Selection links copied.'), 'success' );

			$selection_ID = $new_selection_ID;

			break;


		case 'delete':
			// deletion of the selection
			if( !$confirm )
			{ // ask for confirmation before deleting
				?>
				<div class="panelinfo">
					<h3><?php printf( T_('Delete selection &laquo;%s&raquo;?'), $selection_name )?></h3>

					<p><?php echo T_('Warning').': '.T_('Cascading deletes!') ?></p>

					<p><?php echo T_('THIS CANNOT BE UNDONE!') ?></p>

					<p>

				<?php
					$Form = & new Form( regenerate_url(), 'form_confirm', 'post', '' );

					$action = '';

					$Form->begin_form( 'inline' );
					$Form->hidden( 'action', 'delete' );
					$Form->hidden( 'selection_ID', $selection_ID );
					$Form->hidden( 'selection_name', $selection_name );
					$Form->hidden( 'confirm', 1 );
					$Form->button( array( 'submit', '', T_('I am sure!'), 'DeleteButton' ) );
					$Form->end_form();
					
					unset( $Form );

					$Form = & new Form( regenerate_url(), 'form_cancel', 'post', '' );
					
					$Form->begin_form( 'inline' );
					$Form->button( array( 'submit', '', T_('CANCEL'), 'CancelButton' ) );
					$Form->end_form();
				?>

				</p>

				</div>
				<?php
			}
			else
			{ // the deletion has been confirmed
				$sql_sel = 'DELETE FROM '.$sel_table.' WHERE '.$sel_table_selection.'='.$selection_ID;
				$DB->query( $sql_sel );// deletion of the links between the selection and the selected items
				$Messages->add( T_('Selection attachments deleted.'), 'success' );

				$sql_selections = 'DELETE FROM '.$selections_table.' WHERE '.$selections_table_id.'='.$selection_ID;
				$DB->query( $sql_selections );// deletion of the selection
				$Messages->add( T_('Selection deleted.'), 'success' );
			}

			$selection_ID = -1;

			break;

		default:
			break;
	}

	return $selection_ID;

}


/*
 * $Log$
 * Revision 1.11  2006/02/03 21:58:05  fplanque
 * Too many merges, too little time. I can hardly keep up. I'll try to check/debug/fine tune next week...
 *
 * Revision 1.10  2005/12/30 20:13:40  fplanque
 * UI changes mostly (need to double check sync)
 *
 * Revision 1.9  2005/12/12 19:21:23  fplanque
 * big merge; lots of small mods; hope I didn't make to many mistakes :]
 *
 * Revision 1.8  2005/11/23 23:58:34  blueyed
 * Sorry, encoding messed up (latin1 again).
 *
 * Revision 1.7  2005/11/23 22:48:50  blueyed
 * minor (translation strings)
 *
 * Revision 1.6  2005/11/18 21:01:21  fplanque
 * no message
 *
 * Revision 1.5  2005/10/12 18:24:37  fplanque
 * bugfixes
 *
 * Revision 1.4  2005/09/06 17:13:55  fplanque
 * stop processing early if referer spam has been detected
 *
 * Revision 1.3  2005/06/20 17:40:23  fplanque
 * minor
 *
 * Revision 1.2  2005/06/03 15:12:33  fplanque
 * error/info message cleanup
 *
 * Revision 1.1  2005/06/02 18:50:53  fplanque
 * no message
 *
 */
?>