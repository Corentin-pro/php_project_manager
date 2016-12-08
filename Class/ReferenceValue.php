<?php

require_once('Class/ManagedObject.php');
require_once('Manager/ReferenceValueManager.php');

class ReferenceValue extends ManagedObject
{
	public $code;
	public $value_int;
	public $value_float;
	public $text;
	public $parent_reference_value;

	// Mandatory fields (readonly)
	public $id;
	public $date_creation;
	public $date_modification;

	// Hidden field (only for class uses, not appearing in json)
	protected $array_descriptor = array(
		'id' => 'readonly',
		'code' => 'string',
		'value_int' => 'int',
		'value_float' => 'float',
		'text' => 'string',
		'parent_reference_value' => 'Object/ReferenceValue',
		'date_creation' => 'readonly',
		'date_modification' => 'readonly'); // Factoring the attributes for shorcuts
	protected $required_attributes = array('code'); // inelegant but handy

	function insert()
	{
		return ReferenceValueManager\insert($this);
	}

	function update()
	{
		return ReferenceValueManager\update($this);
	}
}

?>
