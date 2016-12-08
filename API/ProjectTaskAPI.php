<?php
namespace
{
	require_once('Manager/ProjectTaskManager.php');
	require_once('Class/APIResult.php');

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	$json = isset($_GET['js']);
	$offset = isset($_GET['offset']) ? $_GET['offset'] : '';
	$project_id = isset($_GET['project_id']) ? (int)$_GET['project_id'] : 0;
	$page_parameter = array();
	if( !empty($_SESSION['last_page']) )
	{
		parse_str($_SESSION['last_page'], $page_parameter);
	}
	$output;

	// Any call here must have the project_id set
	if( 0 < $project_id)
	{
		switch($action)
		{
			case 'details':
				$project_task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
				if( 0 < $project_task_id )
				{
					$output = ProjectTaskAPI\projectTaskDetails($json, $project_task_id);
				}
				break;
			case 'form_add':
				$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
				$output = ProjectTaskAPI\projectTaskForm($json, 'add', 0, $page_parameter);
				//array( 'section' => 'project_task_management' , 'page' => $page , 'project_id' => $project_id) );
				break;
			case 'form_edit':
			case 'form_delete':
				$action = ('form_edit' === $action) ? 'edit' : 'delete';
				$project_task_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
				$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
				if( 0 < $project_task_id )
				{
					$output = ProjectTaskAPI\projectTaskForm($json, $action, $project_task_id, $page_parameter);
					//array( 'section' => 'project_task_management' , 'page' => $page , 'project_id' => $project_id) );
				}
				break;
			case 'front':
				$output = ProjectTaskManager\printProjectTasks($project_id, $offset, $json);
				break;
			default:
				$output = new \APIResult();
				$output->status = 'error';
				$output->message = 'Unkown action.';
				break;
		}
	}

	if( empty($output) ) // Default error message (if a valid project id is given an error is set in the $output variable)
	{
		$output = new \APIResult();
		$output->status = 'error';
		$output->message = 'No valid project id given.';
	}

	if( $json )
	{
		echo json_encode($output);
	}
	else
	{
		require_once('Controller/APIController.php');
		$controller = new APIController();
		$controller->message = $output->message;
	}
}

namespace ProjectTaskAPI
{
	/**
	 * Output html showing details of a ProjectTask
	 * @param  bool     $p_javascript       If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param  integer  $p_project_task_id  Id of the ProjectTask
	 * @return String
	 */
	function projectTaskDetails($p_javascript, $p_project_task_id)
	{
		$api_result = new \APIResult();
		if( $project_task = \ProjectTaskManager\getById($p_project_task_id) )
		{
			$api_result->status = 'ok';
			if( isset($_GET['output']) && ('html' === $_GET['output']) )
			{
				$api_result->message = $project_task->details($p_javascript);
			}
			else
			{
				$api_result->message = $project_task;
			}
			return $api_result;
		}
		else
		{
			$api_result->status = 'ok';
			$api_result->message = 'No project_task found with id ' . $p_project_task_id;
			return $api_result;
		}
	}

	/**
	 * Output html showing a form to add, edit or delete a ProjectTask
	 * @param   bool     $p_javascript       If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param   string   $p_action           Type of form (possible values : add / edit / delete)
	 * @param   integer  $p_project_task_id  Id of the ProjectTask
	 * @param   array    $p_page_parameter   Parameters to return to the previous page if form is submitedcanceled
	 * @return  string
	 */
	function projectTaskForm($p_javascript, $p_action, $p_project_task_id, $p_page_parameter = array() )
	{
		$api_result = new \APIResult();
		switch( $p_action )
		{
			case 'edit':
			case 'delete':
				if( $project_task = \ProjectTaskManager\getById($p_project_task_id) )
				{
					$delete_name = ('delete' === $p_action) ? 'name' : null;
					$api_result->status = 'ok';
					$api_result->message = $project_task->form($p_javascript, $p_action, 'project_task', $p_page_parameter, $delete_name);
					return $api_result;
				}
				else
				{
					$api_result->status = 'ok';
					$api_result->message = 'No project_task found with id ' . $p_project_task_id;
					return $api_result;
				}
				break;
			default:
				global $project_id;
				require_once('Class/ProjectTask.php');
				$project_task = new \ProjectTask( array('parent_project' => $project_id) );
				$api_result->status = 'ok';
				$api_result->message = $project_task->form($p_javascript, $p_action, 'project_task', $p_page_parameter);
				return $api_result;
		}
	}
}
?>
