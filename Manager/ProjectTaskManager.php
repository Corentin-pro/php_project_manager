<?php

namespace
{
	require_once('Manager/DatabaseManager.php');
}

namespace ProjectTaskManager
{
	/**
	 * Check user right for the ProjectTask modification
	 * @param   integer  $p_project_task_id  Project taks id
	 * @return  boolean                      Operation success
	 */
	function checkModificationPermission($p_project_task_id)
	{
		require_once('Manager/ReferenceValueManager.php');
		if( $user_right = \ReferenceValueManager\getByProjectTaskAndUser($p_project_task_id, $_SESSION['user_id']) )
		{
			if( ("PROJECT_ROLE_OWNER" === $user_right->code) || ("PROJECT_ROLE_DEVELOPER" === $user_right->code))
			{
				return true;
			}
		}
		\DatabaseManager\showError("<p>You don't have the permission to modify the project</p>");
		return false;
	}

	/**
	 * Check user right for the parent Project modification (used for insert)
	 * @param   integer  $p_parent_project_id  Project taks id
	 * @return  boolean                        Operation success
	 */
	function checkParentProjectModificationPermission($p_parent_project_id)
	{
		require_once('Manager/ReferenceValueManager.php');
		if( $user_right = \ReferenceValueManager\getByProjectAndUser($p_parent_project_id, $_SESSION['user_id']) )
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
	 * Saves a ProjectTask in the database
	 * @param   ProjectTaks  $p_project_task  Project to be saved
	 * @return  boolean                       Operation success
	 */
	function insert($p_project_task)
	{
		// The project doesn't have an ID yet so we use the parent project to know the user's permissions
		if( checkParentProjectModificationPermission($p_project_task->parent_project) )
		{
				return \DatabaseManager\insert($p_project_task, 'project_task');
		}
		return false;
	}

	/**
	 * Updates a ProjectTaks in the database
	 * @param   ProjectTaks  $p_project_task  Project to be updated
	 * @return  boolean                       Operation success
	 */
	function update($p_project_task)
	{
		if( checkModificationPermission($p_project_task->id) )
		{
				return \DatabaseManager\update($p_project_task, 'project_task');
		}
		return false;
	}

	/**
	 * Deletes a ProjectTaks by its id
	 * @param   integer  $p_id  ProjectTask id
	 * @return  boolean         Operation success
	 */
	function deleteById($p_id)
	{
		if( checkModificationPermission($p_id) )
		{
			if( $statement = \DatabaseManager\prepare('DELETE FROM project_task WHERE id=:id') )
			{
				return \DatabaseManager\execute($statement , array(':id' => $p_id) );
			}
		}
		return false;
	}

	/**
	 * Deletes a ProjectTask by its name
	 * @param   integer  $p_name  ProjectTask name
	 * @return  boolean           Operation success
	 */
	function deleteByName($p_name)
	{
		if( checkModificationPermission($p_project_task) )
		{
			if( $statement = \DatabaseManager\prepare('DELETE FROM project_task WHERE name=:name') )
			{
				return \DatabaseManager\execute($statement , array(':name' => $p_name) );
			}
		}
		return false;
	}

	/**
	 * Gets the total count of project's tasks in the database
	 * @param   integer  $p_parent_project_id  Parent project id
	 * @return  integer                        ProjectTask count
	 */
	function countByProject($p_parent_project_id = 0)
	{
		if( $statement = \DatabaseManager\prepare('SELECT COUNT(id) AS count FROM project_task WHERE parent_project=:parent_project') )
		{
			if( \DatabaseManager\execute($statement , array(':parent_project' => $p_parent_project_id) ) )
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
	 * Retrieves a ProjectTask from its id
	 * @param   integer      $p_project_task_id  ProjectTask id to be retrieved
	 * @return  ProjectTask
	 */
	function getById($p_project_task_id)
	{
		if( $statement = \DatabaseManager\prepare('SELECT * FROM project_task WHERE id=:id') )
		{
			if( \DatabaseManager\execute($statement , array(':id' => $p_project_task_id) ) )
			{
				if( $fetched_project_task = $statement->fetch() )
				{
					require_once('Class/ProjectTask.php');
					return new \ProjectTask($fetched_project_task);
				}
			}
		}
		return null;
	}

	/**
	 * Shows a table of the project's tasks in the database (paginated)
	 * @param   integer  $p_parent_project_id  Parent Project id
	 * @param   integer  $p_page_number        Number of the page to show
	 */
	function printProjectTaskTable($p_parent_project_id, $p_page_number = 1)
	{
		echo "\t<table class=\"table-main\">\n",
			"\t\t<tr>",
			"<th>Name</th><th>Description</th><th>Progress</th><th>State</th><th style=\"width: 20em;\">Creation</th><th style=\"width: 9rem;\">Action</th>",
			"</tr>\n";

		$offset = (1 < (int)$p_page_number) ? ' OFFSET ' . (($p_page_number - 1) * BACKEND_RESULTS_PER_PAGE) : '';
		if( $statement = \DatabaseManager\prepare('SELECT
			project_task.id,
			project_task.parent_project,
			project_task.name,
			project_task.description,
			state.text as state,
			unit.text as progress_unit,
			project_task.progress_total,
			project_task.progress_current,
			project_task.date_creation
				FROM project_task
				LEFT JOIN reference_value state ON project_task.state = state.id
				LEFT JOIN reference_value unit ON project_task.progress_unit = unit.id
				WHERE project_task.parent_project = :parent_project LIMIT ' . BACKEND_RESULTS_PER_PAGE . $offset) )
		{
			if( \DatabaseManager\execute($statement , array(":parent_project" => $p_parent_project_id) ) )
			{
				while( $fetched_project_task = $statement->fetch() )
				{
					// Escaping user content
					$fetched_project_task['name'] = htmlspecialchars($fetched_project_task['name']);
					$fetched_project_task['description'] = htmlspecialchars($fetched_project_task['description']);

					echo "\t\t<tr>",
						"<td>" , $fetched_project_task['name'],
						"</td><td>" , $fetched_project_task['description'],
						"</td><td>" , $fetched_project_task['progress_current'] , $fetched_project_task['progress_unit'] , "/" ,$fetched_project_task['progress_total'] , $fetched_project_task['progress_unit'],
						"</td><td>" , $fetched_project_task['state'],
						'</td><td><span class="date">' , $fetched_project_task['date_creation'],
						" UTC</span></td><td>",
							"<button onclick=\"callAPIInModalWithElement('?api=project_task&amp;project_id=" , $fetched_project_task['parent_project'],
									"&amp;action=details&amp;output=html&amp;id=" , $fetched_project_task['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"View " , $fetched_project_task['name'] , " details\"><img src=\"public/img/view.svg\" alt=\"View\"></button>",
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=project_task&amp;project_id=" , $fetched_project_task['parent_project'],
									"&amp;action=form_edit&amp;output=html&amp;id=" , $fetched_project_task['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Edit " , $fetched_project_task['name'] , "\"><img src=\"public/img/edit.svg\" alt=\"Edit\"></button>",
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=project_task&amp;project_id=" , $fetched_project_task['parent_project'],
									"&amp;action=form_delete&amp;output=html&amp;id=" , $fetched_project_task['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Delete " , $fetched_project_task['name'] , "\"><img src=\"public/img/delete.svg\" alt=\"Delete\"></button>",
						"</td></tr>\n";
				}
			}
		}
		echo "\t</table>\n";
	}

	/**
	 * Show project (as cards) for the front page
	 * @param  integer  $p_parent_project_id  Project id
	 * @param  integer  $p_offset             Result offset
	 * @param  boolean  $p_json               Output as JSON
	 */
	function printProjectTasks($p_parent_project_id = 0, $p_offset = 0, $p_json = false)
	{
		if( 'mysql' === DATABASE_TYPE )
		{
			// MySQL version
			$sql_query = "SELECT
				project_task.id,
				project_task.parent_project,
				project_task.name,
				project_task.description,
				state.code AS state,
				state.text AS state_text,
				unit.text AS progress_unit,
				unit.value_int AS progress_unit_value,
				project_task.progress_total,
				project_task.progress_current,
				project_task.date_modification
					FROM project_task
					LEFT JOIN reference_value state ON project_task.state = state.id
					LEFT JOIN reference_value unit ON project_task.progress_unit = unit.id
					WHERE parent_project = :parent_project
					ORDER BY FIELD(state.code, 'PROJECT_TASK_STATE_CREATED', 'PROJECT_TASK_STATE_STARTED',  'PROJECT_TASK_STATE_FINISHED'),
						project_task.date_modification DESC
					LIMIT 50 OFFSET " . (int)$p_offset;
		}
		else
		{
			// SQLITE version
			$sql_query = "SELECT
				project_task.id,
				project_task.parent_project,
				project_task.name,
				project_task.description,
				state.code AS state,
				state.text AS state_text,
				unit.text AS progress_unit,
				unit.value_int AS progress_unit_value,
				project_task.progress_total,
				project_task.progress_current,
				project_task.date_modification
					FROM project_task
					LEFT JOIN reference_value state ON project_task.state = state.id
					LEFT JOIN reference_value unit ON project_task.progress_unit = unit.id
					WHERE parent_project = :parent_project
					ORDER BY
						CASE state.code
							WHEN 'PROJECT_TASK_STATE_CREATED' THEN 0
							WHEN 'PROJECT_TASK_STATE_STARTED' THEN 1
							ELSE 2
						END ASC,
						project_task.date_modification DESC
					LIMIT 50 OFFSET " . (int)$p_offset;
		}
		if( $statement = \DatabaseManager\prepare($sql_query) )
		{
			if( \DatabaseManager\execute($statement, array( ':parent_project' => $p_parent_project_id ) ) )
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
					// Using $html to avoid echoing instantly because we need to print some totals first
					$html = "";
					$project_calculated_total_progress = 0;
					$project_calculated_current_progress = 0;
					while( $fetched_project_task = $statement->fetch() )
					{
						$project_calculated_total_progress += (float)$fetched_project_task['progress_unit_value'] * (float)$fetched_project_task['progress_total'];
						$project_calculated_current_progress += (float)$fetched_project_task['progress_unit_value'] * (float)$fetched_project_task['progress_current'];
						// Setting card class by the ProjectTask state
						switch ($fetched_project_task['state'])
						{
							case 'PROJECT_TASK_STATE_STARTED':
								$state_class = 'started';
								break;
							case 'PROJECT_TASK_STATE_FINISHED':
								$state_class = 'finished';
								break;
							default:
								$state_class = 'created';
								break;
						}
						// Escaping user content
						$fetched_project_task['name'] = htmlspecialchars($fetched_project_task['name']);

						$html .= "\t\t<div class=\"card " . $state_class . "\" title=\"" . $fetched_project_task['description'] . "\">\n".
							"\t\t\t<h3>" . $fetched_project_task['name'] . "</h3>\n".
							"\t\t\t<div class=\"pre-progress progress\">" . $fetched_project_task['progress_current'] . "/" . $fetched_project_task['progress_total'] . " ". $fetched_project_task['progress_unit'] . "</div>\n".
							"\t\t\t<h4>" . $fetched_project_task['state_text'] . "</h4>\n".
							"\t\t\t<div class=\"actions\">".
							"<button onclick=\"callAPIInModalWithElement('?api=project_task&amp;project_id=" . $p_parent_project_id.
									"&amp;action=details&amp;output=html&amp;id=" . $fetched_project_task['id'].
								"','modal-action','modal-action-content');\"".
								" title=\"View " . $fetched_project_task['name'] . " details\"><img src=\"public/img/view.svg\" alt=\"View\"></button>".
							"<button onclick=\"callAPIInModalWithElement(".
								"'?api=project_task&amp;project_id=" . $p_parent_project_id.
									"&amp;action=form_edit&amp;output=html&amp;id=" . $fetched_project_task['id'].
								"','modal-action','modal-action-content');\"".
								" title=\"Edit " . $fetched_project_task['name'] . "\"><img src=\"public/img/edit.svg\" alt=\"Edit\"></button>".
							"<button onclick=\"callAPIInModalWithElement(".
								"'?api=project_task&amp;project_id=" . $fetched_project_task['parent_project'].
									"&amp;action=form_delete&amp;output=html&amp;id=" . $fetched_project_task['id'].
								"','modal-action','modal-action-content');\"".
								" title=\"Delete " . $fetched_project_task['name'] . "\"><img src=\"public/img/delete.svg\" alt=\"Delete\"></button>".
							"</div>\n".
							"\t\t\t<div class=\"detail\">(last update : <span class=\"date\">" . $fetched_project_task['date_modification'] . " UTC</span>)</div>\n\t\t</div>\n";
					}
					if( 24 < $project_calculated_total_progress )
					{
						echo "<h3>Total progress : " , $project_calculated_total_progress , "h (" , round($project_calculated_total_progress/24,1),
							"d) , Current progress : " , $project_calculated_current_progress , "h (" , round($project_calculated_current_progress/24,1) , "d)</h3>";
					}
					else
					{
						echo "<h3>Total progress : " , $project_calculated_total_progress , "h , Current progress : " , $project_calculated_current_progress , "h</h3>";
					}
					echo "<div class=\"card-container\">\n" , $html , "\t</div>\n";
				}
			}
		}
	}
}

?>
