<?php
namespace
{
	require_once('Manager/UserManager.php');
	require_once('Class/APIResult.php');

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	$javascript = isset($_GET['js']);
	$page_parameter = array();
	if( !empty($_SESSION['last_page']) )
	{
		parse_str($_SESSION['last_page'], $page_parameter);
	}
	$output;

	switch($action)
	{
		case 'details':
			$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			if( 0 < $user_id )
			{
				$output = UserAPI\userDetails($javascript, $user_id);
			}
			break;
		case 'form_add':
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$output = UserAPI\userForm($javascript, 'add', 0, $page_parameter );
			break;
		case 'form_edit':
		case 'form_delete':
			$action = ('form_edit' === $action) ? 'edit' : 'delete';
			$user_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			if( 0 < $user_id )
			{
				$output = UserAPI\userForm($javascript, $action, $user_id, $page_parameter );
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

namespace UserAPI
{
	/**
	 * Output html showing details of an User
	 * @param  bool     $p_javascript  If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param  integer  $p_user_id     Id of the User
	 * @return String
	 */
	function userDetails($p_javascript, $p_user_id)
	{
		$api_result = new \APIResult();
		if( $user = \UserManager\getById($p_user_id) )
		{
			$api_result->status = 'ok';
			if( isset($_GET['output']) && ('html' === $_GET['output']) )
			{
				$api_result->message = $user->details($p_javascript);
			}
			else
			{
				$api_result->message = $user;
			}
			return $api_result;
		}
		else
		{
			$api_result->status = 'error';
			$api_result->message = 'No user found with id ' . $p_user_id;
			return $api_result;
		}
	}

	/**
	* Output html showing a form to add, edit or delete an User
	 * @param   bool     $p_javascript      If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param   string   $p_action          Type of form (possible values : add / edit / delete)
	 * @param   integer  $p_user_id         Id of the User
	 * @param   array    $p_page_parameter  Parameters to return to the previous page if form is canceled
	 * @return  string
	 */
	function userForm($p_javascript, $p_action, $p_user_id, $p_page_parameter = array() )
	{
		$api_result = new \APIResult();
		switch( $p_action )
		{
			case 'edit':
			case 'delete':
				if( $user = \UserManager\getById($p_user_id) )
				{
					$delete_name = ('delete' === $p_action) ? 'login' : null;
					$api_result->status = 'ok';
					$api_result->message = $user->form($p_javascript, $p_action, 'user', $p_page_parameter, $delete_name);
					return $api_result;
				}
				else
				{
					$api_result->status = 'ok';
					$api_result->message = 'No user found with id ' . $p_user_id;
					return $api_result;
				}
				break;
			default:
				require_once('Class/User.php');
				$user = new \User(array());
				$api_result->status = 'ok';
				$api_result->message = $user->form($p_javascript, $p_action, 'user', $p_page_parameter);
				return $api_result;
		}
	}
}
?>
