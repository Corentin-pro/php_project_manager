<?php

require_once('Class/Controller.php');

class ProjectTaskManagementController extends Controller
{
	public $parent_project;
	public $parent_page;
	public $page = 1;
	public $action = '';

	function render()
	{
		require_once('Manager/ProjectTaskManager.php'); // include manager here so it can print exceptions
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
					if( !empty($_GET['project_task']) )
					{
						require_once('Class/ProjectTask.php');
						$project_task = new ProjectTask($_GET['project_task'], false);
						if( $project_task->insert() )
						{
							$count = ProjectTaskManager\countByProject($this->parent_project->id);
							if( $count > (BACKEND_RESULTS_PER_PAGE * $this->page)  )
							{
								$this->page += 1;
							}
							header('Location: ?section=project_task_management&project_id=' . $this->parent_project->id . '&page=' . $this->page );
						}
						else
						{
							$this->error .= 'Adding \'<span class="highlighted">' . $project_task->name  . '</span>\' failed.';
						}
					}
					break;
				case 'edit':
					if( !empty($_GET['project_task']) )
					{
						require_once('Class/ProjectTask.php');
						$project_task = new ProjectTask($_GET['project_task'], false);
						if( $project_task->update() )
						{
							header('Location: ?section=project_task_management&project_id=' . $this->parent_project->id . '&page=' . $this->page );
						}
						else
						{
							$this->error .= 'Editing failed.';
						}
					}
					break;
				case 'delete':
					$project_task_id = (isset($_GET['project_task']['id'])) ? (int)($_GET['project_task']['id']) : 0;
					if( 0 < $project_task_id )
					{
						if( ProjectTaskManager\deleteById($project_task_id) )
						{
							$count = ProjectTaskManager\countByProject($this->parent_project->id);
							if( $count <= (BACKEND_RESULTS_PER_PAGE * ($this->page - 1))  )
							{
								$this->page -= 1;
							}
							header('Location: ?section=project_task_management&project_id=' . $this->parent_project->id . '&page=' . $this->page );
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

		$this->title = "Project task";
		Parent::printHeader();
		include 'View/Backend/ProjectTaskManagementView.html';
		Parent::printFooter();
	}

	// Generate a table showing the projects
	function printProjectTaskTable()
	{
		$count = ProjectTaskManager\countByProject($this->parent_project->id);

		$this->printPagination($this->page, $count,'&project_id='.$this->parent_project->id);
		ProjectTaskManager\printProjectTaskTable($this->parent_project->id, $this->page);
	}
}

?>
