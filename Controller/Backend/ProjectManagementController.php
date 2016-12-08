<?php

require_once('Class/Controller.php');

class ProjectManagementController extends Controller
{
	public $page = 1;
	public $action = '';

	function render()
	{
		require_once('Manager/ProjectManager.php'); // include manager here so it can print exceptions

		$this->page = (!empty($_GET['page'])) ? (int)$_GET['page'] : 1;
		$this->action = isset($_GET['action']) ? $_GET['action'] : "";

		switch( $this->action )
		{
			case 'add':
				if( !empty($_GET['project']) )
				{
					require_once('Class/Project.php');
					$project = new Project($_GET['project'], false);
					if( $project->insert() )
					{
						$count = ProjectManager\count();
						if( $count > (BACKEND_RESULTS_PER_PAGE * $this->page)  )
						{
							$this->page += 1;
						}
						header('Location: ?section=project_management&page=' . $this->page );
					}
					else
					{
						$this->error .= 'Adding \'<span class="highlighted">' . $project->name  . '</span>\' failed.';
					}
				}
				break;
			case 'edit':
				if( !empty($_GET['project']) )
				{
					require_once('Class/Project.php');
					$project = new Project($_GET['project'], false);
					if( $project->update() )
					{
						header('Location: ?section=project_management&page=' . $this->page );
					}
					else
					{
						$this->error .= 'Editing failed.';
					}
				}
				break;
			case 'delete':
				$project_id = (isset($_GET['project']['id'])) ? (int)($_GET['project']['id']) : 0;
				if( 0 < $project_id )
				{
					if( ProjectManager\deleteById($project_id) )
					{
						$count = ProjectManager\count();
						if( $count <= (BACKEND_RESULTS_PER_PAGE * ($this->page - 1))  )
						{
							$this->page -= 1;
						}
						header('Location: ?section=project_management&page=' . $this->page );
					}
					else
					{
						$this->error .= 'Deleting project failed.';
					}
				}
				else
				{
					$this->error .= 'Deleting project failed (no valid id given).';
				}
				break;
		}

		$this->title = "Project Management";
		Parent::printHeader();
		include 'View/Backend/ProjectManagementView.html';
		Parent::printFooter();
	}

	// Generate a table showing the projects
	function printProjectTable()
	{
		$count = ProjectManager\count();

		$this->printPagination($this->page, $count);
		ProjectManager\printProjectTable($this->page);
	}
}

?>
