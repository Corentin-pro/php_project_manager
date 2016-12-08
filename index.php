<?php

require_once('config.php');

ini_set( 'session.cookie_httponly', 1 );
session_start();

// Special case for calling logout
if( isset($_GET['logout']) )
{
	session_destroy();
	header('Location: ./');
}

global $controller;
global $section;

// Check if logged
if( isset($_SESSION['logged']) )
{
	// Check if API call
	if( !empty($_GET['api']) )
	{
		if( $_SESSION['admin'] ) // admin only API
		{
			switch($_GET['api'])
			{
				case 'relation_project_user':
					require_once('API/Relation/RelationProjectUserAPI.php');
					break;
				case 'user_right':
					require_once('API/UserRightAPI.php');
					break;
				case 'reference_value':
					require_once('API/ReferenceValueAPI.php');
					break;
				case 'user':
					require_once('API/UserAPI.php');
					break;
			}
		}

		// Non admin API
		switch($_GET['api'])
		{
			case 'relation_project_user':
			case 'user_right':
			case 'reference_value':
			case 'user':
				break;
			case 'project':
				require_once('API/ProjectAPI.php');
				break;
			case 'project_task':
				require_once('API/ProjectTaskAPI.php');
				break;
			case 'connect':
				require_once('API/ConnectAPI.php');
				break;
			default:
				echo json_encode( array('status' => 'error', 'message' => 'Unkown API.' ) );
				break;
		}
	}
	else // None API
	{
		// If not an API call should have a section (if not going to 'default')
		$section = (!empty($_GET['section'])) ? $_GET['section'] : null;

		if( $_SESSION['admin'] ) // admin only sections
		{
			switch($section)
			{
				case 'project_management':
					require_once('Controller/Backend/ProjectManagementController.php');
					$controller = new ProjectManagementController();
					break;
				case 'project_task_management':
					require_once('Controller/Backend/ProjectTaskManagementController.php');
					$controller = new ProjectTaskManagementController();
					break;
				case 'project_user_management':
					require_once('Controller/Backend/ProjectUserManagementController.php');
					$controller = new ProjectUserManagementController();
					break;
				case 'reference_value_management':
					require_once('Controller/Backend/ReferenceValueManagementController.php');
					$controller = new ReferenceValueManagementController();
					break;
				case 'user_management':
					require_once('Controller/Backend/UserManagementController.php');
					$controller = new UserManagementController();
					break;
			}
		}
		switch($section)
		{
			case 'login':
				require_once('Controller/LoginController.php');
				$controller = new LoginController();
				break;
			case 'project_task':
				require_once('Controller/ProjectTaskController.php');
				$controller = new ProjectTaskController();
				break;
			default:
				if( null === $controller)
				{
					require_once('Controller/IndexController.php');
					$controller = new IndexController();
				}
				break;
		}
	}
}
else // not logged
{
	if( !empty($_GET['api']) )
	{
		require_once('API/ConnectAPI.php');
	}
	else
	{
		require_once('Controller/LoginController.php');
		$controller = new LoginController();
	}
}

if( null !== $controller)
{
	$controller->render();
}

?>
