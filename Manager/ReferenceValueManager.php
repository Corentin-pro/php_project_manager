<?php

namespace
{
	require_once('Manager/DatabaseManager.php');
}

namespace ReferenceValueManager
{
	/**
	 * Saves a ReferenceValue in the database
	 * @param   ReferenceValue  $p_reference_value  ReferenceValue to be saved
	 * @return  boolean                             Operation success
	 */
	function insert($p_reference_value)
	{
		return \DatabaseManager\insert($p_reference_value, 'reference_value');
	}

	/**
	 * Updates a ReferenceValue in the database
	 * @param   ReferenceValue  $p_reference_value  ReferenceValue to be updated
	 * @return  boolean                             Operation success
	 */
	function update($p_reference_value)
	{
		return \DatabaseManager\update($p_reference_value, 'reference_value');
	}

	/**
	 * Deletes a ReferenceValue by its id
	 * @param   integer  $p_id  ReferenceValue id
	 * @return  boolean         Operation success
	 */
	function deleteById($p_id)
	{
		if( $statement = \DatabaseManager\prepare('DELETE FROM reference_value WHERE id=:id') )
		{
			return \DatabaseManager\execute($statement , array(':id' => $p_id) );
		}
		return false;
	}

	/**
	 * Gets the total count of ReferenceValues in the database
	 * @return  integer  ReferenceValues count
	 */
	function count()
	{
		if( $statement = \DatabaseManager\prepare('SELECT COUNT(id) AS count FROM reference_value') )
		{
			if( \DatabaseManager\execute($statement) )
			{
				if( $count = $statement->fetch() )
				{
					return (int)$count['count'];
				}
			}
		}
		return 0;
	}

	/**
	 * Retrieves a ReferenceValue by its code
	 * @param   integer          $p_reference_value_id  ReferenceValue id to be retrieved
	 * @return  ReferenceValue
	 */
	function getById($p_reference_value_id)
	{
		if( $statement = \DatabaseManager\prepare('SELECT * FROM reference_value WHERE id=:id') )
		{
			if( \DatabaseManager\execute($statement , array(':id' => $p_reference_value_id) ) )
			{
				if( $fetched_reference_value = $statement->fetch() )
				{
					require_once('Class/ReferenceValue.php');
					return new \ReferenceValue($fetched_reference_value);
				}
			}
		}
		return null;
	}

	/**
	 * Retrieves a ReferenceValue by its code
	 * @param   string          $p_reference_value_code  ReferenceValue code to be retrieved
	 * @return  ReferenceValue
	 */
	function getByCode($p_reference_value_code)
	{
		if( $statement = \DatabaseManager\prepare('SELECT * FROM reference_value WHERE code=:code') )
		{
			if( \DatabaseManager\execute($statement , array(':code' => $p_reference_value_code) ) )
			{
				if( $fetched_reference_value = $statement->fetch() )
				{
					require_once('Class/ReferenceValue.php');
					return new \ReferenceValue($fetched_reference_value);
				}
			}
		}
		return null;
	}

	/**
	 * Retrieves ReferenceValue (meant be the user right) for a project and a user (from the _relation_project_user table)
	 * @param   integer         $p_project_id  Project id
	 * @param   integer         $p_user_id     User id
	 * @return  ReferenceValue
	 */
	function getByProjectAndUser($p_project_id, $p_user_id)
	{
		if( $statement = \DatabaseManager\prepare("SELECT reference_value.*
			FROM reference_value
			JOIN _relation_project_user ON _relation_project_user.user_right=reference_value.id
			WHERE _relation_project_user.project = :project AND _relation_project_user.user=:user") )
		{
			if( \DatabaseManager\execute($statement , array( ':project' => $p_project_id , ':user' => $p_user_id ) ) )
			{
				if( $fetched_reference_value = $statement->fetch() )
				{
					require_once('Class/ReferenceValue.php');
					return new \ReferenceValue($fetched_reference_value);
				}
			}
		}
		return null;
	}

	/**
	 * Retrieves ReferenceValue (meant be the user right) for a project and a user (from the _relation_project_user table)
	 * @param   integer         $p_project_task_id  ProjectTask id
	 * @param   integer         $p_user_id          User id
	 * @return  ReferenceValue
	 */
	function getByProjectTaskAndUser($p_project_task_id, $p_user_id)
	{
		if( $statement = \DatabaseManager\prepare("SELECT reference_value.*
			FROM reference_value
			JOIN _relation_project_user ON _relation_project_user.user_right=reference_value.id
			JOIN project_task ON _relation_project_user.project=project_task.parent_project
			WHERE project_task.id = :project_task AND _relation_project_user.user=:user") )
		{
			if( \DatabaseManager\execute($statement , array( ':project_task' => $p_project_task_id , ':user' => $p_user_id ) ) )
			{
				if( $fetched_reference_value = $statement->fetch() )
				{
					require_once('Class/ReferenceValue.php');
					return new \ReferenceValue($fetched_reference_value);
				}
			}
		}
		return null;
	}

	/**
	 * Output the option list (HTML) for a ReferenceValue
	 * @param   integer  $p_default_id  Id of the ReferenceValue selected by default
	 * @param   boolean  $p_required    If false adds an empty option as the first choice
	 * @return  string                  HTML output
	 */
	function listOptions($p_default_id = 0, $p_required = false)
	{
		$html = "";
		if( $statement = \DatabaseManager\prepare('SELECT id,code FROM reference_value') )
		{
			if( \DatabaseManager\execute($statement) )
			{
				if( !$p_required )
				{
					$html .= '<option value="">&nbsp;</option>';
				}
				while( $fetched_reference_value = $statement->fetch() )
				{
					$selected = ($p_default_id === $fetched_reference_value['id']) ? ' selected="selected"' : '';
					$html .= '<option value="' . $fetched_reference_value['id'] . '"' . $selected . '>' . $fetched_reference_value['code'] . '</option>';
				}
			}
		}
		return $html;
	}

	/**
	 * Output the option list (HTML) related to a ReferenceValue (children)
	 * @param   integer  $p_parent_id   Parent ReferenceValue id
	 * @param   string   $p_default_id  Id of the ReferenceValue selected by default
	 * @param   boolean  $p_required    If false adds an empty option as the first choice
	 * @return  string                  HTML output
	 */
	function listOptionsByParent($p_parent_id, $p_default_id = "0", $p_required = false)
	{
		$html = "";
		if( $statement = \DatabaseManager\prepare('SELECT id,text FROM reference_value WHERE parent_reference_value=:parent_id AND id!=parent_reference_value') )
		{
			if( \DatabaseManager\execute($statement , array(':parent_id' => $p_parent_id) ) )
			{
				if( !$p_required )
				{
					$html .= '<option value="">&nbsp;</option>';
				}
				while( $fetched_reference_value = $statement->fetch() )
				{
					$selected = ($p_default_id === $fetched_reference_value['id']) ? ' selected="selected"' : '';
					$html .= '<option value="' . $fetched_reference_value['id'] . '"' . $selected . '>' . $fetched_reference_value['text'] . '</option>';
				}
			}
		}
		return $html;
	}

	/**
	 * Shows a table of the ReferenceValues in the database (paginated)
	 * @param  integer  $p_page_number  Number of the page to show
	 */
	function printReferenceValueTable($p_page_number = 1)
	{
		echo "\t<table class=\"table-main\">\n",
			"\t\t<tr>",
			"<th style=\"width: 5em;\">Code</th><th>Int</th><th>Float</th><th style=\"width: 20em;\">Text</th><th>Parent</th><th style=\"width: 20em;\">Creation</th><th style=\"width: 9rem;\">Action</th>",
			"</tr>\n";

		$offset = (1 < (int)$p_page_number) ? ' OFFSET ' . (($p_page_number - 1) * BACKEND_RESULTS_PER_PAGE) : '';
		if( $statement = \DatabaseManager\prepare("SELECT
			reference_value.id,
			reference_value.code,
			reference_value.value_int,
			reference_value.value_float,
			reference_value.text,
			parent_reference_value.code AS parent,
			reference_value.date_creation
			FROM reference_value
			JOIN reference_value parent_reference_value ON reference_value.parent_reference_value = parent_reference_value.id
			LIMIT " . BACKEND_RESULTS_PER_PAGE . $offset) )
		{
			if( \DatabaseManager\execute($statement) )
			{
				while( $fetched_reference_value = $statement->fetch() )
				{
					// Escaping reference_value content
					$fetched_reference_value['code'] = htmlspecialchars($fetched_reference_value['code']);
					$fetched_reference_value['text'] = htmlspecialchars($fetched_reference_value['text']);
					$fetched_reference_value['parent'] = htmlspecialchars($fetched_reference_value['parent']);

					echo "\t\t<tr>",
						"<td>" , $fetched_reference_value['code'],
						"</td><td>" , $fetched_reference_value['value_int'],
						"</td><td>" , $fetched_reference_value['value_float'],
						"</td><td>" , $fetched_reference_value['text'],
						"</td><td>" , $fetched_reference_value['parent'],
						'</td><td><span class="date">' , $fetched_reference_value['date_creation'],
						" UTC</span></td><td>",
						"<button onclick=\"callAPIInModalWithElement('?api=reference_value&amp;action=details&amp;output=html&amp;id=" , $fetched_reference_value['id'],
							"','modal-action','modal-action-content');\"",
							" title=\"View " , $fetched_reference_value['login'] , " details\"><img src=\"public/img/view.svg\" alt=\"View\"></button>",
						"<button onclick=\"callAPIInModalWithElement(",
							"'?api=reference_value&amp;action=form_edit&amp;output=html&amp;id=" , $fetched_reference_value['id'],
							"','modal-action','modal-action-content');\"",
							" title=\"Edit " , $fetched_reference_value['login'] , "\"><img src=\"public/img/edit.svg\" alt=\"Edit\"></button>",
						"<button onclick=\"callAPIInModalWithElement(",
							"'?api=reference_value&amp;action=form_delete&amp;output=html&amp;id=" , $fetched_reference_value['id'],
							"','modal-action','modal-action-content');\"",
							" title=\"Delete " , $fetched_reference_value['login'] , "\"><img src=\"public/img/delete.svg\" alt=\"Delete\"></button>",
						"</td></tr>\n";
				}
			}
		}
		echo "\t</table>\n";
	}
}

?>
