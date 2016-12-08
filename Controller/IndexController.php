<?php

require_once('Class/Controller.php');

class IndexController extends Controller
{
	function render()
	{
		require_once('Manager/ProjectManager.php'); // include manager here so it can print exceptions

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
						header('Location: ./');
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
						header('Location: ./');
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
						header('Location: ./');
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
		include 'View/IndexView.html';
		Parent::printFooter();
	}

	function printFrontProjects()
	{
		echo "<div class=\"card-container\" id=\"front-project-container\">\n";
		echo ProjectManager\frontProjects();
		echo "\t</div>\n";

		if( HOME_PAGE_PROJECT_MAX_NUMBER < ProjectManager\count() )
		{
			echo "\t<div id=\"front-more\" class=\"button more\" onclick=\"loadCardsInElement('?api=project&amp;action=front&amp;','front-project-container')\">More</div>";
		}
	}
}

 ?>
