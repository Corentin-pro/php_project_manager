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
		case 'connect':
			global $output;
			$output = new \APIResult();
			$login = isset($_POST['login']) ? $_POST['login'] : '';
			$password = isset($_POST['password']) ? $_POST['password'] : '';
			if( UserManager\connect($login, $password) )
			{
				$output->status = "ok";
				$output->message = "connected";
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

?>
