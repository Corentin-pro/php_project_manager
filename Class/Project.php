<?php

require_once('Class/ManagedObject.php');
require_once('Manager/ProjectManager.php');

class Project extends ManagedObject
{
	public $name;
	public $description;
	public $state='PROJECT_STATE'; // Default value (list's code), inelegant but needed
	public $progress_unit='PROGRESS_UNIT'; // Default value (list's code), inelegant but needed
	public $progress_total;
	public $progress_current;

	// Mandatory fields (readonly)
	public $id;
	public $date_creation;
	public $date_modification;

	// Hidden field (only for internal uses, not appearing in json)
	protected $array_descriptor = array(
		'id' => 'readonly',
		'name' => 'string',
		'description' => 'text',
		'state' => 'ReferenceValue',
		'progress_unit' => 'ReferenceValue',
		'progress_total' => 'float',
		'progress_current' => 'float',
		'date_creation' => 'readonly',
		'date_modification' => 'readonly'); // Factoring the attributes for shorcuts
	protected $required_attributes = array('name','state'); // inelegant but handy

	function insert()
	{
		return ProjectManager\insert($this);
	}

	function update()
	{
		return ProjectManager\update($this);
	}

	function deleteByName()
	{
		return ProjectManager\deleteByName($this->name);
	}
}

?>
