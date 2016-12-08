<?php

require_once('Class/ManagedObject.php');
require_once('Manager/UserManager.php');

class User extends ManagedObject
{
	public $name;
	public $email;
	public $login;
	public $password;

	// Mandatory fields (hidden)
	public $id;
	public $date_creation;
	public $date_modification;

	// Hidden field (only for internal uses, not appearing in json)
	protected $array_descriptor = array(
		'id' => 'readonly',
		'name' => 'string',
		'email' => 'email',
		'login' => 'string',
		'password' => 'password',
		'date_creation' => 'readonly',
		'date_modification' => 'readonly'); // Factoring the attributes for shorcuts
	protected $required_attributes = array('name','email','login','password'); // inelegant but handy

	function insert()
	{
		return UserManager\insert($this);
	}

	function update()
	{
		return UserManager\update($this);
	}

	function deleteByName()
	{
		return UserManager\deleteByName($this->name);
	}
}

?>
