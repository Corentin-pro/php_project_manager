<?php

require_once('Class/ManagedObject.php');
require_once('Manager/RelationProjectUserManager.php');

class RelationProjectUser extends ManagedObject
{
	public $project;
	public $user;
	public $user_right="PROJECT_ROLE";

	// Mandatory fields (readonly)
	public $id;
	public $date_creation;
	public $date_modification;

	// Hidden field (only for internal uses, not appearing in json)
	protected $array_descriptor = array(
		'id' => 'readonly',
		'project' => 'Object/Project',
		'user' => 'Object/User',
		'user_right' => 'ReferenceValue',
		'date_creation' => 'readonly',
		'date_modification' => 'readonly'); // Factoring the attributes for shorcuts
	protected $required_attributes = array('project','user','user_right'); // inelegant but handy

	function insert()
	{
		return RelationProjectUserManager\insert($this);
	}

	function update()
	{
		return RelationProjectUserManager\update($this);
	}

	function deleteByName()
	{
		return RelationProjectUserManager\deleteByName($this->name);
	}
}

?>
