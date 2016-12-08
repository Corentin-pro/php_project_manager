<?php

require_once('Class/Controller.php');

class APIController extends Controller
{
	public $message;

	function render()
	{
		$this->title = "Project Management";
		$this->initSessionVariables();
		include 'View/Header.html';
		echo $this->message;
		include 'View/Footer.html';
	}
}

?>
