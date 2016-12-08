<?php

require_once('Class/Controller.php');

class UserManagementController extends Controller
{
	var $page = 1;
	var $action = '';

	function render()
	{
		require_once('Manager/UserManager.php'); // include manager here so it can print exceptions

		$this->page = (!empty($_GET['page'])) ? (int)$_GET['page'] : 1;
		$this->action = isset($_GET['action']) ? $_GET['action'] : "";

		switch( $this->action )
		{
			case 'add':
				if( !empty($_GET['user']) )
				{
					require_once('Class/User.php');
					$user = new User($_GET['user'], false);
					if( $user->insert() )
					{
						$count = UserManager\count();
						if( $count > (BACKEND_RESULTS_PER_PAGE * $this->page)  )
						{
							$this->page += 1;
						}
						header('Location: ?section=user_management&page=' . $this->page );
					}
					else
					{
						$this->error .= '<p>Adding \'<span class="highlighted">' . $user->login  . '</span>\' failed.</p>';
					}
				}
				break;
			case 'edit':
				if( !empty($_GET['user']) )
				{
					require_once('Class/User.php');
					$user = new User($_GET['user'], false);
					if( $user->update() )
					{
						header('Location: ?section=user_management&page=' . $this->page );
					}
					else
					{
						$this->error .= 'Editing failed.';
					}
				}
				break;
			case 'delete':
				$user_id = (isset($_GET['user']['id'])) ? (int)($_GET['user']['id']) : 0;
				if( 0 < $user_id )
				{
					if( UserManager\deleteById($user_id) )
					{
						$count = UserManager\count();
						if( $count <= (BACKEND_RESULTS_PER_PAGE * ($this->page - 1))  )
						{
							$this->page -= 1;
						}
						header('Location: ?section=user_management&page=' . $this->page );
					}
					else
					{
						$this->error .= 'Deleting failed.';
					}
				}
				else
				{
					$this->error .= 'Deleting user failed (no valid id given).';
				}
				break;
		}

		$this->title = "User Management";
		Parent::printHeader();
		include 'View/Backend/UserManagementView.html';
		Parent::printFooter();
	}

	// Generate a table showing the users
	function printUserTable()
	{
		$count = UserManager\count();

		$this->printPagination($this->page, $count);
		UserManager\printUserTable($this->page);
	}
}

?>
