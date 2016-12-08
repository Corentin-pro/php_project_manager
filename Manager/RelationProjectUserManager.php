<?php

namespace
{
	require_once('Manager/DatabaseManager.php');
}

namespace RelationProjectUserManager
{
	/**
	 * Saves a RelationProjectUser in the database
	 * @param   RelationProjectUser  $p_relation_project_user  RelationProjectUser to be saved
	 * @return  boolean                   Operation success
	 */
	function insert($p_relation_project_user)
	{
		return \DatabaseManager\insert($p_relation_project_user, '_relation_project_user');
	}

	/**
	 * Updates a RelationProjectUser in the database
	 * @param   RelationProjectUser  $relation_project_user    RelationProjectUser to be updated
	 * @param   string     $p_user_token  User token
	 * @return  boolean                   Operation success
	 */
	function update($p_relation_project_user)
	{
		return \DatabaseManager\update($p_relation_project_user, '_relation_project_user');
	}

	/**
	 * Deletes a RelationProjectUser by its id
	 * @param   integer  $p_id  RelationProjectUser id
	 * @return  boolean         Operation success
	 */
	function deleteById($p_id)
	{
		if( $statement = \DatabaseManager\prepare('DELETE FROM _relation_project_user WHERE id=:id') )
		{
			return \DatabaseManager\execute($statement , array(':id' => $p_id) );
		}
		return false;
	}

	/**
	 * Gets the total count of a project's RelationProjectUsers
	 * @param   integer  $p_project_id  Parent project id
	 * @return  integer                 RelationProjectUser count
	 */
	function countByProject($p_project_id = 0)
	{
		if( $statement = \DatabaseManager\prepare("SELECT COUNT(_relation_project_user.id) AS count
			FROM _relation_project_user
			WHERE project=:project_id") )
		{
			if( \DatabaseManager\execute($statement , array(':project_id' => $p_project_id) ) )
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
	 * Retrieves a RelationProjectUser by its id
	 * @param   integer              $p_relation_project_user_id  RelationProjectUser id to be retrieved
	 * @return  RelationProjectUser
	 */
	function getById($p_relation_project_user_id)
	{
		if( $statement = \DatabaseManager\prepare('SELECT * FROM _relation_project_user WHERE id=:id') )
		{
			if( \DatabaseManager\execute($statement , array(':id' => $p_relation_project_user_id) ) )
			{
				if( $fetched_relation_project_user = $statement->fetch() )
				{
					require_once('Class/Relation/RelationProjectUser.php');
					return new \RelationProjectUser($fetched_relation_project_user);
				}
			}
		}
		return null;
	}

	/**
	 * Retrieves RelationProjectUser for a project and a user
	 * @param   integer              $p_project_id  Project id
	 * @param   integer              $p_user_id     User id
	 * @return  RelationProjectUser
	 */
	function getByProjectAndUser($p_project_id, $p_user_id)
	{
		if( $statement = \DatabaseManager\prepare("SELECT *
			FROM _relation_project_user
			WHERE _relation_project_user.project = :project_id AND _relation_project_user.user=:user_id") )
		{
			if( \DatabaseManager\execute($statement , array( ':project_id' => $p_project_id , ':user_id' => $p_user_id ) ) )
			{
				if( $fetched_relation_project_user = $statement->fetch() )
				{
					require_once('Class/Relation/RelationProjectUser.php');
					return new \RelationProjectUser($fetched_relation_project_user);
				}
			}
		}
		return null;
	}

	/**
	 * Shows a table of a Project's UserRigths (paginated)
	 * @param  integer  $p_project_id   Project id
	 * @param  integer  $p_page_number  Number of the page to show
	 */
	function printRelationProjectUserTable($p_project_id, $p_page_number = 1)
	{
		echo "\t<table class=\"table-main\">\n",
			"\t\t<tr>",
			"<th>Name</th><th>Role</th><th style=\"width: 20em;\">Creation</th><th style=\"width: 13em;\">Action</th>",
			"</tr>\n";

		$offset = (1 < (int)$p_page_number) ? ' OFFSET ' . (($p_page_number - 1) * BACKEND_RESULTS_PER_PAGE) : '';
		if( $statement = \DatabaseManager\prepare("SELECT
			_relation_project_user.id,
			user.name,
			reference_value.text,
			reference_value.date_creation,
			_relation_project_user.project
			FROM _relation_project_user
			JOIN reference_value ON reference_value.id = _relation_project_user.user_right
			JOIN user ON user.id = _relation_project_user.user
			WHERE _relation_project_user.project = :project_id LIMIT " . BACKEND_RESULTS_PER_PAGE . $offset) )
		{
			if( \DatabaseManager\execute($statement , array( ':project_id' => $p_project_id) ) )
			{
				while( $fetched_relation_project_user = $statement->fetch() )
				{
					// Escaping user content
					$fetched_relation_project_user['name'] = htmlspecialchars($fetched_relation_project_user['name']);

					echo "<tr><td>" , $fetched_relation_project_user['name'],
						"</td><td>" , $fetched_relation_project_user['text'],
						"</td><td><span class=\"date\">" , $fetched_relation_project_user['date_creation'],
						" UTC</span></td><td>",
							"<button onclick=\"callAPIInModalWithElement('?api=relation_project_user&amp;project_id=" , $fetched_relation_project_user['project'],
									"&amp;action=details&amp;output=html&amp;id=" , $fetched_relation_project_user['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"View " , $fetched_relation_project_user['name'] , " details\"><img src=\"public/img/view.svg\" alt=\"View\"></button>",
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=relation_project_user&amp;project_id=" , $p_project_id,
									"&amp;action=form_edit&amp;output=html&amp;id=" , $fetched_relation_project_user['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Edit " , $fetched_relation_project_user['name'] , "\"><img src=\"public/img/edit.svg\" alt=\"Edit\"></button>",
							"<button onclick=\"callAPIInModalWithElement(",
								"'?api=relation_project_user&amp;project_id=" , $fetched_relation_project_user['project'],
									"&amp;action=form_delete&amp;output=html&amp;id=" , $fetched_relation_project_user['id'],
								"','modal-action','modal-action-content');\"",
								" title=\"Delete " , $fetched_relation_project_user['name'] , "\"><img src=\"public/img/delete.svg\" alt=\"Delete\"></button>",
						"</td></tr>\n";
				}
			}
		}

		echo "\t</table>\n";
	}
}

?>
