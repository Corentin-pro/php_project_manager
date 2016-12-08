<?php
namespace
{
	require_once('Manager/ProjectManager.php');
	require_once('Class/APIResult.php');

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	$json = isset($_GET['js']);
	$offset = isset($_GET['offset']) ? $_GET['offset'] : '';
	$page_parameter = array();
	if( !empty($_SESSION['last_page']) )
	{
		parse_str($_SESSION['last_page'], $page_parameter);
	}
	$output;

	switch($action)
	{
		case 'details':
			$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			if( 0 < $project_id )
			{
				$output = ProjectAPI\projectDetails($json, $project_id);
			}
			break;
		case 'form_add':
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$output = ProjectAPI\projectForm($json, 'add', 0, $page_parameter );
			break;
		case 'form_edit':
		case 'form_delete':
			$action = ('form_edit' === $action) ? 'edit' : 'delete';
			$project_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			if( 0 < $project_id )
			{
				$output = ProjectAPI\projectForm($json, $action, $project_id, $page_parameter );
			}
			break;
		case 'front':
			$output = ProjectManager\frontProjects($offset, $json);
			if( !$json )
			{
				$json_output = new \APIResult();
				$json_output->status = 'ok';
				$json_output->message = $output;
				$output = $json_output;
				$json = true;
			}
			break;
		default:
			$output = new \APIResult();
			$output->status = 'error';
			$output->message = 'Unkown action.';
			break;
	}

	if( empty($output) ) // Default error message (if a valid project id is given an error is set in the $output variable)
	{
		$output = new \APIResult();
		$output->status = 'error';
		$output->message = 'No valid id given.';
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

namespace ProjectAPI
{
	/**
	 * Output html showing details of a Project
	 * @param   bool     $p_javascript  If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param   integer  $p_project_id  Id of the Project
	 * @return  string
	 */
	function projectDetails($p_javascript, $p_project_id)
	{
		$api_result = new \APIResult();
		if( $project = \ProjectManager\getById($p_project_id) )
		{
			$api_result->status = 'ok';
			if( isset($_GET['output']) && ('html' === $_GET['output']) )
			{
				$api_result->message = $project->details($p_javascript);
			}
			else
			{
				$api_result->message = $project;
			}
			return $api_result;
		}
		else
		{
			$api_result->status = 'ok';
			$api_result->message = 'No project found with id ' . $p_project_id;
			return $api_result;
		}
	}

	/**
	 * Output html showing a form to add, edit or delete a Project
	 * @param   bool     $p_javascript      If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param   string   $p_action          Type of form (possible values : add / edit / delete)
	 * @param   integer  $p_project_id      Id of the Project
	 * @param   array    $p_page_parameter  Parameters to return to the previous page if form is canceled
	 * @return  string
	 */
	function projectForm($p_javascript, $p_action, $p_project_id, $p_page_parameter = array() )
	{
		$api_result = new \APIResult();
		switch( $p_action )
		{
			case 'edit':
			case 'delete':
				if( $project = \ProjectManager\getById($p_project_id) )
				{
					$delete_name = ('delete' === $p_action) ? 'name' : null;
					$api_result->status = 'ok';
					$api_result->message = $project->form($p_javascript, $p_action, 'project', $p_page_parameter, $delete_name);
					return $api_result;
				}
				else
				{
					$api_result->status = 'ok';
					$api_result->message = 'No project found with id ' . $p_project_id;
					return $api_result;
				}
				break;
			default:
				require_once('Class/Project.php');
				$project = new \Project(array());
				$api_result->status = 'ok';
				$api_result->message = $project->form($p_javascript, $p_action, 'project', $p_page_parameter);
				return $api_result;
		}
	}
}
?>
