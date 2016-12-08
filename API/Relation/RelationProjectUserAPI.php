<?php
namespace
{
	require_once('Manager/RelationProjectUserManager.php');
	require_once('Class/APIResult.php');

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	$javascript = isset($_GET['js']);
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
				$project_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
				if( 0 < $project_user_id )
				{
					$output = ProjectUserAPI\projectUserDetails($javascript, $project_user_id);
				}
				break;
			case 'form_add':
				$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
				$output = ProjectUserAPI\projectUserForm($javascript, 'add', 0, $page_parameter);
				break;
			case 'form_edit':
			case 'form_delete':
				$action = ('form_edit' === $action) ? 'edit' : 'delete';
				$project_user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
				$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
				if( 0 < $project_user_id )
				{
					$output = ProjectUserAPI\projectUserForm($javascript, $action, $project_user_id, $page_parameter);
				}
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

	if( $javascript )
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

namespace ProjectUserAPI
{
	/**
	 * Output html showing details of a ProjectUser
	 * @param  bool     $p_javascript     If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param  integer  $p_project_user_id  Id of the ProjectUser
	 * @return String
	 */
	function projectUserDetails($p_javascript, $p_project_user_id)
	{
		$api_result = new \APIResult();
		if( $project_user = \RelationProjectUserManager\getById($p_project_user_id) )
		{
			$api_result->status = 'ok';
			if( isset($_GET['output']) && ('html' === $_GET['output']) )
			{
				$api_result->message = $project_user->details($p_javascript);
			}
			else
			{
				$api_result->message = $project_user;
			}
			return $api_result;
		}
		else
		{
			$api_result->status = 'ok';
			$api_result->message = 'No project_user found with id ' . $p_project_user_id;
			return $api_result;
		}
	}

	/**
	 * Output html showing a form to add, edit or delete a ProjectUser
	 * @param   bool     $p_javascript      If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param   string   $p_action          Type of form (possible values : add / edit / delete)
	 * @param   integer  $p_project_user_id   Id of the ProjectUser
	 * @param   array    $p_page_parameter  Parameters to return to the previous page if form is submitedcanceled
	 * @return  string
	 */
	function projectUserForm($p_javascript, $p_action, $p_project_user_id, $p_page_parameter = array() )
	{
		$api_result = new \APIResult();
		switch( $p_action )
		{
			case 'edit':
			case 'delete':
				if( $project_user = \RelationProjectUserManager\getById($p_project_user_id) )
				{
					$delete_name = null;
					$api_result->status = 'ok';
					$api_result->message = $project_user->form($p_javascript, $p_action, 'project_user', $p_page_parameter, $delete_name);
					return $api_result;
				}
				else
				{
					$api_result->status = 'ok';
					$api_result->message = 'No project_user found with id ' . $p_project_user_id;
					return $api_result;
				}
				break;
			default:
				global $project_id;
				require_once('Class/Relation/RelationProjectUser.php');
				$project_user = new \RelationProjectUser( array('parent_project' => $project_id) );
				$api_result->status = 'ok';
				$api_result->message = $project_user->form($p_javascript, $p_action, 'project_user', $p_page_parameter);
				return $api_result;
		}
	}
}
?>
