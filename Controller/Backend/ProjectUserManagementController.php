<?php

require_once('Class/Controller.php');

class ProjectUserManagementController extends Controller
{
	public $parent_project;
	public $parent_page;
	public $page = 1;
	public $action = '';

	function render()
	{
		require_once('Manager/RelationProjectUserManager.php'); // include manager here so it can print exceptions
		require_once('Manager/ProjectManager.php');

		$parent_project_id = (isset($_GET['project_id'])) ? (int)$_GET['project_id'] : 0;
		$this->parent_project = ProjectManager\getById($parent_project_id);

		$this->parent_page = (isset( $_SESSION['last_page'] )) ? '?'.$_SESSION['last_page'] : '?section=project_management';
		$this->page = (!empty($_GET['page'])) ? (int)$_GET['page'] : 1;
		$this->action = isset($_GET['action']) ? $_GET['action'] : "";

		if( null !== $this->parent_project)
		{
			switch( $this->action )
			{
				case 'add':
					if( !empty($_GET['project_user']) )
					{
						require_once('Class/Relation/RelationProjectUser.php');
						$project_user = new RelationProjectUser($_GET['project_user'], false);
						if( $project_user->insert() )
						{
							$count = RelationProjectUserManager\countByProject($this->parent_project->id);
							if( $count > (BACKEND_RESULTS_PER_PAGE * $this->page)  )
							{
								$this->page += 1;
							}
							header('Location: ?section=project_user_management&project_id=' . $this->parent_project->id . '&page=' . $this->page );
						}
						else
						{
							$this->error .= 'Adding failed.';
						}
					}
					break;
				case 'edit':
					if( !empty($_GET['project_user']) )
					{
						require_once('Class/Relation/RelationProjectUser.php');
						$project_user = new RelationProjectUser($_GET['project_user'], false);
						if( $project_user->update() )
						{
							header('Location: ?section=project_user_management&project_id=' . $this->parent_project->id . '&page=' . $this->page );
						}
						else
						{
							$this->error .= 'Editing failed.';
						}
					}
					break;
				case 'delete':
					$project_user_id = (isset($_GET['project_user']['id'])) ? (int)($_GET['project_user']['id']) : 0;
					if( 0 < $project_user_id )
					{
						if( RelationProjectUserManager\deleteById($project_user_id) )
						{
							$count = RelationProjectUserManager\countByProject($this->parent_project->id);
							if( $count <= (BACKEND_RESULTS_PER_PAGE * ($this->page - 1))  )
							{
								$this->page -= 1;
							}
							header('Location: ?section=project_user_management&project_id=' . $this->parent_project->id . '&page=' . $this->page );
						}
						else
						{
							$this->error .= 'Deleting project task failed.';
						}
					}
					else
					{
						$this->error .= 'Deleting project task failed (no valid id given).';
					}
					break;
			}
		}
		else
		{
			$this->error = "No project id specified";
			require_once('Class/Project.php');
			$this->parent_project = new Project();
		}

		$this->title = "Project users";
		Parent::printHeader();
		include 'View/Backend/ProjectUserManagementView.html';
		Parent::printFooter();
	}

	// Generate a table showing the projects
	function printProjectUserTable()
	{
		$count = RelationProjectUserManager\countByProject($this->parent_project->id);

		$this->printPagination($this->page, $count,'&project_id='.$this->parent_project->id);
		RelationProjectUserManager\printRelationProjectUserTable($this->parent_project->id, $this->page);
	}
}

?>
