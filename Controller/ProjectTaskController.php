<?php

require_once('Class/Controller.php');

class ProjectTaskController extends Controller
{
	public $parent_project;
	public $action = '';

	function render()
	{
		require_once('Manager/ProjectManager.php'); // include manager here so it can print exceptions
		require_once('Manager/ProjectTaskManager.php');

		$parent_project_id = (isset($_GET['project_id'])) ? (int)$_GET['project_id'] : 0;
		$this->parent_project = ProjectManager\getById($parent_project_id);
		$this->action = isset($_GET['action']) ? $_GET['action'] : "";

		if( null !== $this->parent_project )
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
							header('Location: ?section=project_task&project_id=' . $this->parent_project->id );
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
							header('Location: ?section=project_task&project_id=' . $this->parent_project->id );
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
							header('Location: ?section=project_task&project_id=' . $this->parent_project->id );
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
			$this->error = "No project found";
			require_once('Class/Project.php');
			$this->parent_project = new Project();
		}

		$this->title = "Project Tasks";
		Parent::printHeader();
		include 'View/ProjectTaskView.html';
		Parent::printFooter();
	}

	function printProjectTasks()
	{
		if( 0 < $this->parent_project->id)
		{
			\ProjectTaskManager\printProjectTasks($this->parent_project->id);
		}
	}
}

?>
