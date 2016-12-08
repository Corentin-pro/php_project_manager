<?php

require_once('Class/Controller.php');

class LoginController extends Controller
{

	function render()
	{
		if( !empty($_POST['login']) && !empty($_POST['password']) )
		{
			require_once('Manager/UserManager.php'); // include manager here so it can print exceptions

			// Check creditentials
			if( UserManager\connect($_POST['login'], $_POST['password']) )
			{
				header('Location: ./');
			}
		}

		$this->title = "Login";
		Parent::printHeader();
		include 'View/LoginView.html';
		Parent::printFooter();
	}

}

?>
