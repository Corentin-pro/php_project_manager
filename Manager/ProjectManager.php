<?php

namespace
{
	require_once('Manager/DatabaseManager.php');
}

namespace ProjectManager
{
	/**
	 * Check user right for the Project modification
	 * @param   integer  $p_project_id  Project id
	 * @return  boolean                 Operation success
	 */
	function checkModificationPermission($p_project_id)
	{
		require_once('Manager/ReferenceValueManager.php');
		if( $user_right = \ReferenceValueManager\getByProjectAndUser($p_project_id, $_SESSION['user_id']) )
		{
			if( ("PROJECT_ROLE_OWNER" === $user_right->code) || ("PROJECT_ROLE_DEVELOPER" === $user_right->code) )
			{
				return true;
			}
		}
		\DatabaseManager\showError("<p>You don't have the permission to modify the project</p>");
		return false;
	}

	/**
	 * Check user right for the Project deletion
	 * @param   integer  $p_project_id  Project id
	 * @return  boolean                 Operation success
	 */
	function checkDeletionPermission($p_project_id)
	{
		require_once('Manager/ReferenceValueManager.php');
		if( $user_right = \ReferenceValueManager\getByProjectAndUser($p_project_id, $_SESSION['user_id']) )
		{
			if( "PROJECT_ROLE_OWNER" === $user_right->code )
			{
				return true;
			}
		}
		\DatabaseManager\showError("<p>You don't have the permission to delete the project</p>");
		return false;
	}

	/**
	 * Checks project name existence in the database
	 * @param   string   $p_name  Project name
	 * @return  boolean           Project name existence in the database
	 */
	function checkName($p_name)
	{
		if( $statement = \DatabaseManager\prepare('SELECT id FROM project WHERE name=:name;') )
		{
			if( \DatabaseManager\execute($statement , array(':name' => $p_name) ) )
			{
				if( $statement->fetch() )
				{
					return true;
				}
			}
		}
		return false;
	}

	/**
	 * Saves a Project in the database
	 * @param   Project  $p_project  Project to be saved
	 * @return  boolean              Operation success
	 */
	function insert($p_project)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			// Check for project's name
			if( !checkName($p_project->name) )
			{
				return \DatabaseManager\transaction( function() use ($p_project)
				{
					global $pdo;
					require_once('Class/Relation/RelationProjectUser.php');
					$success = true;

					// Creating the project
					$success &= \DatabaseManager\insert($p_project, 'project');
					$project_id = $pdo->lastInsertId();

					// Adding the current user as owner of the project
					require_once('Manager/ReferenceValueManager.php');
					if( $user_right = \ReferenceValueManager\getByCode("PROJECT_ROLE_OWNER") )
					{
						$relation_project_user = new \RelationProjectUser( array( 'project' => $project_id,
							'user' => $_SESSION['user_id'],
							'user_right' => $user_right->id ), false);
						$success &= \DatabaseManager\insert($relation_project_user, '_relation_project_user');
					}
					else
					{
						$success = false;
					}

					return $success;
				});
			}
			else
			{
				\DatabaseManager\showError("<p>The project name (" . $p_project->name . ") is already used please choose an other one.</p>");
			}
		}
		else
		{
			\DatabaseManager\showError("<p>You don't have the permission to create a project</p>");
		}
		return false;
	}

	/**
	 * Updates a Project in the database
	 * @param   Project  $project       Project to be updated
	 * @param   string   $p_user_token  User token
	 * @return  boolean                 Operation success
	 */
	function update($p_project)
	{
		if( checkModificationPermission($p_project->id) )
		{
			return \DatabaseManager\update($p_project, 'project');
		}
		return false;
	}

	/**
	 * Deletes a Project by its id
	 * @param   integer  $p_id  Project id
	 * @return  boolean         Operation success
	 */
	function deleteById($p_id)
	{
		if( checkDeletionPermission($p_id) )
		{
			return \DatabaseManager\transaction( function() use ($p_id)
			{
				$success = true;

				// Deleting the project permissions
				if( $statement = \DatabaseManager\prepare('DELETE FROM _relation_project_user WHERE project=:parent_project') )
				{
					$success &= \DatabaseManager\execute($statement , array(':parent_project' => $p_id) );
				}
				// Deleting the project tasks
				if( $statement = \DatabaseManager\prepare('DELETE FROM project_task WHERE parent_project=:parent_project') )
				{
					$success &= \DatabaseManager\execute($statement , array(':parent_project' => $p_id) );
				}
				// Deleting the project
				if( $statement = \DatabaseManager\prepare('DELETE FROM project WHERE id=:id') )
				{
					$success &=  \DatabaseManager\execute($statement , array(':id' => $p_id) );
				}

				return $success;
			});
		}
		return false;
	}

	/**
	 * Gets the total count of Projects in the database
	 * @return  integer  Project count
	 */
	function count()
	{
		if( $statement = \DatabaseManager\prepare('SELECT COUNT(id) AS count FROM project') )
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
	 * Retrieves a Project by its id
	 * @param   integer  $p_project_id  Project id to be retrieved
	 * @return  Project
	 */
	function getById($p_project_id)
	{
		if( $statement = \DatabaseManager\prepare('SELECT * FROM project WHERE id=:id') )
		{
			if( \DatabaseManager\execute($statement , array(':id' => $p_project_id) ) )
			{
				if( $fetched_project = $statement->fetch() )
				{
					require_once('Class/Project.php');
					return new \Project($fetched_project);
				}
			}
		}
		return null;
	}

	/**
	 * Output the option list (HTML) related to a Project
	 * @param   string   $p_default_id  Id of the Project selected by default
	 * @param   boolean  $p_required    If false adds an empty option as the first choice
	 * @return  string                  HTML output
	 */
	function listOptions($p_default_id = "0", $p_required = false)
	{
		$html = "";
		if( $statement = \DatabaseManager\prepare('SELECT id,name FROM project LIMIT 1000') )
		{
			if( \DatabaseManager\execute($statement) )
			{
				if( !$p_required )
				{
					$html .= '<option value="">&nbsp;</option>';
				}
				while( $fetched_project = $statement->fetch() )
				{
					// Escaping user content
					$fetched_project['name'] = htmlspecialchars($fetched_project['name']);

					$selected = ($p_default_id === $fetched_project['id']) ? ' selected="selected"' : '';
					$html .= '<option value="' . $fetched_project['id'] . '"' . $selected . '>' . $fetched_project['name'] . '</option>';
				}
			}
		}
		return $html;
	}

	/**
	 * Shows a table of the Projects in the database (paginated)
	 * @param  integer  $p_page_number  Number of the page to show
	 */
	function printProjectTable($p_page_number = 1)
	{
		echo "\t<table class=\"table-main\">\n",
			"\t\t<tr>",
			"<th>Name</th><th>Description</th><th>Progress</th><th>State</th><th style=\"width: 20em;\">Creation</th><th style=\"width: 14rem;\">Action</th>",
			"</tr>\n";

		$offset = (1 < (int)$p_page_number) ? ' OFFSET ' . (($p_page_number - 1) * BACKEND_RESULTS_PER_PAGE) : '';
		if( $statement = \DatabaseManager\prepare('SELECT
			project.id,
			project.name,
			project.description,
			state.text as state,
			unit.text as progress_unit,
			project.progress_total,
			project.progress_current,
			project.date_creation
				FROM project
				LEFT JOIN reference_value state ON project.state = state.id
				LEFT JOIN reference_value unit ON project.progress_unit = unit.id
				LIMIT ' . BACKEND_RESULTS_PER_PAGE . $offset) )
		{
			if( \DatabaseManager\execute($statement) )
			{
				while( $fetched_project = $statement->fetch() )
				{
					// Escaping user content
					$fetched_project['name'] = htmlspecialchars($fetched_project['name']);
					$fetched_project['description'] = htmlspecialchars($fetched_project['description']);

					echo "\t\t<tr>",
						"<td>" , $fetched_project['name'],
						"</td><td>" , $fetched_project['description'],
						"</td><td>" , $fetched_project['progress_current'] , $fetched_project['progress_unit'] , "/" ,$fetched_project['progress_total'] , $fetched_project['progress_unit'],
						"</td><td>" , $fetched_project['state'],
						'</td><td><span class="date">' , $fetched_project['date_creation'],
						" UTC</span></td><td>",
							"<button onclick=\"callAPIInModalWithElement('?api=project&amp;action=details&amp;output=html&amp;id=" , $fetched_project['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"View " , $fetched_project['name'] , " details\"><img src=\"public/img/view.svg\" alt=\"View\"></button>",
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=project&amp;action=form_edit&amp;output=html&amp;id=" , $fetched_project['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Edit " , $fetched_project['name'] , "\"><img src=\"public/img/edit.svg\" alt=\"Edit\"></button>",
							'<a class="button" href="?section=project_task_management&amp;project_id=' . $fetched_project['id'] . '" title="' , $fetched_project['name'] , ' tasks management"><img src="public/img/menu.svg" alt="Tasks"></a>',
							'<a class="button" href="?section=project_user_management&amp;project_id=' . $fetched_project['id'] . '" title="' , $fetched_project['name'] , ' users management"><img src="public/img/profile.svg" alt="Users"></a>',
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=project&amp;action=form_delete&amp;output=html&amp;id=" , $fetched_project['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Delete " , $fetched_project['name'] , "\"><img src=\"public/img/delete.svg\" alt=\"Delete\"></button>",
						"</td></tr>\n";
				}
			}
		}
		echo "\t</table>\n";
	}

	/**
	 * Show project (as cards) for the front page
	 * @param  integer  $p_offset  Result offset
	 * @param  boolean  $p_json    Output as JSON
	 */
	function frontProjects($p_offset = 0, $p_json = false)
	{
		switch (DATABASE_TYPE)
		{
			case 'mysql':
				$sql_max_operator = "GREATEST";
				break;

			default:
				$sql_max_operator = "MAX";
				break;
		}

		$sql_query = "SELECT *," . $sql_max_operator . "(project_date,project_task_date) AS date FROM
				(SELECT
					project.id,
					project.name,
					project.description,
					state.code AS state,
					state.text AS state_text,
					unit.text AS progress_unit,
					project.progress_total,
					project.progress_current,
					project.date_modification AS project_date,
					project_task.name AS task_name,
					task_unit.text AS task_progress_unit,
					project_task.progress_total AS task_progress_total,
					project_task.progress_current AS task_progress_current,
					COALESCE(project_task.date_modification,0) AS project_task_date,
					user_right.code AS user_right
						FROM project
						LEFT JOIN reference_value state ON project.state = state.id
						LEFT JOIN project_task ON project.id = project_task.parent_project
						LEFT JOIN reference_value unit ON project.progress_unit = unit.id
						LEFT JOIN reference_value task_unit ON project_task.progress_unit = task_unit.id
						INNER JOIN _relation_project_user ON _relation_project_user.project = project.id
						LEFT JOIN reference_value user_right ON _relation_project_user.user_right = user_right.id
						WHERE _relation_project_user.user=:user
						ORDER BY project_task_date DESC) AS project
				GROUP BY project.id
				ORDER BY date DESC
				LIMIT " . HOME_PAGE_PROJECT_MAX_NUMBER . " OFFSET " . (int)$p_offset;

		if( $statement = \DatabaseManager\prepare($sql_query) )
		{
			if( \DatabaseManager\execute($statement , array( ":user" => $_SESSION['user_id']) ) )
			{
				if( $p_json ) // JSON output
				{
					$output = new \APIResult();
					$output->status = 'ok';
					$output->message = $statement->fetchAll();
					return $output;
				}
				else // HTML output
				{
					$output = "";
					while( $fetched_project = $statement->fetch() )
					{
						// Setting card class by the Project state
						switch ($fetched_project['state'])
						{
							case 'PROJECT_STATE_CREATED':
								$state_class = 'created';
								break;
							case 'PROJECT_STATE_READY':
							case 'PROJECT_STATE_STARTED':
								$state_class = 'started';
								break;
							default:
								$state_class = 'finished';
								break;
						}
						// Escaping user content
						$fetched_project['name'] = htmlspecialchars($fetched_project['name']);

						$output .= "\t\t<div class=\"card " . $state_class . "\" title=\"" . $fetched_project['description'] . "\">\n".
							"\t\t\t<h3>" . $fetched_project['name'] . "</h3>\n".
							"\t\t\t<div class=\"pre-progress progress\">" . $fetched_project['progress_current'] . "/" . $fetched_project['progress_total'] . " ". $fetched_project['progress_unit'] . "</div>\n".
							"\t\t\t<h4>" . $fetched_project['state_text'] . "</h4>\n";
						if( !empty($fetched_project['task_name']) )
						{
							$output .= "\t\t\t<div class=\"progress-task\">Last updated task :<h4>" . $fetched_project['task_name'] . " (" . $fetched_project['task_progress_current'] . "/" . $fetched_project['task_progress_total'] . $fetched_project['task_progress_unit'] . ")</h4></div>\n";
						}
						$output .= "\t\t\t<div class=\"actions\">\n".
							"\t\t\t\t<a class=\"button\" href=\"?section=project_task&amp;project_id=" . $fetched_project['id'].
							'" title="Go to ' . $fetched_project['name'] . "\"><img src=\"public/img/menu.svg\" alt=\"Tasks\"></a>\n";
						if( ("PROJECT_ROLE_OWNER" === $fetched_project['user_right']) || ("PROJECT_ROLE_DEVELOPER" === $fetched_project['user_right']) )
						{
							$output .= "\t\t\t\t<button onclick=\"callAPIInModalWithElement(".
									"'?api=project&amp;action=form_edit&amp;output=html&amp;id=" . $fetched_project['id'].
									"','modal-action','modal-action-content');\"".
									" title=\"Edit " . $fetched_project['name'] . "\"><img src=\"public/img/edit.svg\" alt=\"Edit\"></button>\n";
							if("PROJECT_ROLE_OWNER" === $fetched_project['user_right'])
							{
								$output .= "\t\t\t\t<button onclick=\"callAPIInModalWithElement(".
									"'?api=project&amp;action=form_delete&amp;output=html&amp;id=" . $fetched_project['id'].
									"','modal-action','modal-action-content');\"".
									" title=\"Delete " . $fetched_project['name'] . "\"><img src=\"public/img/delete.svg\" alt=\"Delete\"></button>";
							}
						}
						$output .= "\t\t\t</div>\n".
							"\t\t\t<div class=\"detail\">(last update : <span class=\"date\">" . $fetched_project['date'] . " UTC</span>)</div>\n".
							"\t\t</div>\n";
					}
					return $output;
				}
			}
		}
	}
}

?>
