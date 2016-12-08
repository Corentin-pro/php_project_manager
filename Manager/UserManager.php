<?php

namespace
{
	require_once('Manager/DatabaseManager.php');
}

namespace UserManager
{
	/**
	 * Connects with an user login/password (sets the PHP session)
	 * @param   string  $p_login     User login
	 * @param   string  $p_password  User password
	 * @return  boolean              Connection success
	 */
	function connect($p_login,$p_password)
	{
		if( $statement = \DatabaseManager\prepare('SELECT id,password,admin FROM user WHERE login=:login;') )
		{
			if( \DatabaseManager\execute($statement , array(':login' => $p_login) ) )
			{
				if( $fetched_user = $statement->fetch() )
				{
					if( password_verify($p_password, $fetched_user['password']) )
					{
						// Set session to be logged
						$_SESSION['logged'] = true;
						// Set the current user for the session
						$_SESSION['user_id'] = $fetched_user['id'];
						// Set the admin right for the session
						$_SESSION['admin'] = (bool)$fetched_user['admin'];
						return true;
					}
					else
					{
						global $controller;
						// Building the error message
						$error = "Wrong password";
						// If a controller is instanciate, giving the message to it instead of just echoing
						if( null !== $controller )
						{
							$controller->error .= $error;
						}
						else
						{
							global $output;
							if( null != $output )
							{
								$output->status = 'error';
								$output->message = $error;
							}
							else
							{
								echo $error;
							}
						}
					}
				}
				else
				{
					global $controller;
					// Building the error message
					$error = "Wrong login";
					// If a controller is instanciate, giving the message to it instead of just echoing
					if( null !== $controller )
					{
						$controller->error .= $error;
					}
					else
					{
						global $output;
						if( null != $output )
						{
							$output->status = 'error';
							$output->message = $error;
						}
						else
						{
							echo $error;
						}
					}
				}
			}
		}
		return false;
	}

	/**
	 * Checks User email or login existence in the database
	 * @param   string   $p_email  User email
	 * @param   string   $p_login  User login
	 * @return  boolean            User existence in the database
	 */
	function checkEmailAndLogin($p_email,$p_login)
	{
		if( $statement = \DatabaseManager\prepare('SELECT id FROM user WHERE email=:email OR login=:login;') )
		{
			if( \DatabaseManager\execute($statement , array(':email' => $p_email, ':login' => $p_login) ) )
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
	 * Saves an User in the database
	 * @param   User     $p_user  User to be saved
	 * @return  boolean           Operation success
	 */
	function insert($p_user)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			// Check for user's email and login
			if( !checkEmailAndLogin($p_user->email, $p_user->login) )
			{
				return \DatabaseManager\insert($p_user, 'user');
			}
			else
			{
				global $controller;
				// Building the error message
				$error = "The email or login is already used please choose an other one.";
				// If a controller is instanciate, giving the message to it instead of just echoing
				if( null !== $controller )
				{
					$controller->error .= $error;
				}
				else
				{
					echo $error;
				}
			}
		}
		return false;
	}

	/**
	 * Updates an User in the database
	 * @param   User     $p_user  User to be updated
	 * @return  boolean           Operation success
	 */
	function update($p_user)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			return \DatabaseManager\update($p_user, 'user');
		}
	}

	/**
	 * Deletes an User by its id
	 * @param   integer  $p_id  User id
	 * @return  boolean         Operation success
	 */
	function deleteById($p_id)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			return \DatabaseManager\transaction( function() use ($p_id)
			{
				$success = true;

				// Deleting the user permissions
				if( $statement = \DatabaseManager\prepare('DELETE FROM _relation_project_user WHERE user=:user') )
				{
					$success &= \DatabaseManager\execute($statement , array(':user' => $p_id) );
				}
				// Deleting the user
				if( $statement = \DatabaseManager\prepare('DELETE FROM user WHERE id=:id') )
				{
					$success &= \DatabaseManager\execute($statement , array(':id' => $p_id) );
				}

				return $success;
			});
		}
		return false;
	}

	/**
	 * Deletes an User by its login
	 * @param   integer  $p_login  User login
	 * @return  boolean            Operation success
	 */
	function deleteByLogin($p_login)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			if( $statement = \DatabaseManager\prepare('DELETE FROM user WHERE login=:login') )
			{
				return \DatabaseManager\execute($statement , array(':login' => $p_login) );
			}
		}
		return false;
	}

	/**
	 * Gets the total count of Users in the database
	 * @return  integer  User count
	 */
	function count()
	{
		if( $statement = \DatabaseManager\prepare('SELECT COUNT(id) AS count FROM user') )
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
	 * Retrieves an User by its id
	 * @param   integer  $p_user_id  User id to be retrieved
	 * @return  User
	 */
	function getById($p_user_id)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			if( $statement = \DatabaseManager\prepare('SELECT * FROM user WHERE id=:id') )
			{
				if( \DatabaseManager\execute($statement , array(':id' => $p_user_id) ) )
				{
					if( $fetched_user = $statement->fetch() )
					{
						require_once('Class/User.php');
						return new \User($fetched_user);
					}
				}
			}
		}
		return null;
	}

	/**
	 * Output the option list (HTML) related to a User
	 * @param   string   $p_default_id  Id of the User selected by default
	 * @param   boolean  $p_required    If false adds an empty option as the first choice
	 * @return  string                  HTML output
	 */
	function listOptions($p_default_id = "0", $p_required = false)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			$html = "";
			if( $statement = \DatabaseManager\prepare('SELECT id,name FROM user LIMIT 1000') )
			{
				if( \DatabaseManager\execute($statement) )
				{
					if( !$p_required )
					{
						$html .= '<option value="">&nbsp;</option>';
					}
					while( $fetched_user = $statement->fetch() )
					{
						$selected = ($p_default_id === $fetched_user['id']) ? ' selected="selected"' : '';
						$html .= '<option value="' . $fetched_user['id'] . '"' . $selected . '>' . $fetched_user['name'] . '</option>';
					}
				}
			}
			return $html;
		}
	}

	/**
	 * Shows a table of the Users in the database (paginated)
	 * @param  integer  $p_page_number  Number of the page to show
	 */
	function printUserTable($p_page_number = 1)
	{
		// Check if admin
		if( isset($_SESSION['admin']) && $_SESSION['admin'] )
		{
			echo "\t<table class=\"table-main\">\n",
				"\t\t<tr>",
				"<th>Name</th><th>Email</th><th>Login</th><th style=\"width: 20em;\">Creation</th><th style=\"width: 9rem;\">Action</th>",
				"</tr>\n";

			$offset = (1 < (int)$p_page_number) ? ' OFFSET ' . (($p_page_number - 1) * BACKEND_RESULTS_PER_PAGE) : '';
			if( $statement = \DatabaseManager\prepare('SELECT id,name,email,login,date_creation FROM user LIMIT ' . BACKEND_RESULTS_PER_PAGE . $offset) )
			{
				if( \DatabaseManager\execute($statement) )
				{
					while( $fetched_user = $statement->fetch() )
					{
						// Escaping user content
						$fetched_user['name'] = htmlspecialchars($fetched_user['name']);
						$fetched_user['email'] = htmlspecialchars($fetched_user['email']);
						$fetched_user['login'] = htmlspecialchars($fetched_user['login']);

						echo "\t\t<tr>",
							"<td>" , $fetched_user['name'],
							"</td><td>" , $fetched_user['email'],
							"</td><td>" , $fetched_user['login'],
							'</td><td><span class="date">' , $fetched_user['date_creation'],
							" UTC</span></td><td>",
							"<button onclick=\"callAPIInModalWithElement('?api=user&amp;action=details&amp;output=html&amp;id=" , $fetched_user['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"View " , $fetched_user['login'] , " details\"><img src=\"public/img/view.svg\" alt=\"View\"></button>",
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=user&amp;action=form_edit&amp;output=html&amp;id=" , $fetched_user['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Edit " , $fetched_user['login'] , "\"><img src=\"public/img/edit.svg\" alt=\"Edit\"></button>",
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=user&amp;action=form_delete&amp;output=html&amp;id=" , $fetched_user['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Delete " , $fetched_user['login'] , "\"><img src=\"public/img/delete.svg\" alt=\"Delete\"></button>",
							"</td></tr>\n";
					}
				}
			}
			echo "\t</table>\n";
		}
	}
}

?>
