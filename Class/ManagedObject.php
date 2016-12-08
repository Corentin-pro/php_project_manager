<?php

class ManagedObject
{
	/**
	 * Default constructor, works if you defined the 'array_descriptor' (with the class' attributes name). Making a protected array_descriptor is recommanded
	 * @param  array  $p_array     Associative array (map) containing the key=>value to initialise the object fields
	 * @param  bool   $p_escaping  Toggle string escape (for outputing), if the object is internally used not escaping is prefered to avoid html entities to be saved in the database.
	 */
	function __construct($p_array = array(), $p_escaping = true)
	{
		$attributes = array_keys($this->array_descriptor);
		foreach($attributes as $attribute)
		{
			if( isset($p_array[$attribute]) )
			{
				if( is_string($p_array[$attribute]) )
				{
					if( $p_escaping ) // if string need to escape
					{
						// Escaping is used to show data from the database that were set by user input. There shouldn't be needs to trim them as they shoud have been when they got set.
						$this->$attribute = htmlspecialchars( $p_array[$attribute] ); // escaped string
					}
					else
					{
						// Auto triming string is nice because users might not notice spaces (especially because of the mobil autocompletion)
						$this->$attribute = trim($p_array[$attribute]); // trimed string
					}
				}
				else
				{
					$this->$attribute = $p_array[$attribute]; // default initialization
				}
			}
		}
	}

	/**
	 * Simple getter for the array_descriptor (should be protected)
	 * @return  array  Key=>value corresponding the the field=>type of the object (used to avoid introspection/reflection)
	 */
	function getArrayDescriptor()
	{
		return $this->array_descriptor;
	}

	/**
	 * Returns a html version of the object (used instead of raw JSON). Expose the object's data.
	 * @param   bool    $p_javascript  Weither javascript is enable or not (in a modal or not)
	 * @return  string                 HTML output
	 */
	function details($p_javascript)
	{
		$html = '';
		// If no javascript we embed in a 'main' tag
		if( !$p_javascript )
		{
			$html .= "<main>\n";
		}

		$html .= "<h3>" . get_class($this) . " details</h3><div><table class=\"table-view\">\n";
		foreach($this->array_descriptor as $attribute => $type)
		{
			// If asking for html we don't print raw values
			// This method isn't nice for performance but it is called for one object only to show it details so it isn't worth giving to much work
			switch ($type)
			{
				case 'password':
					$this->$attribute = "*****";
					break;
				case 'ReferenceValue':
					require_once('Manager/ReferenceValueManager.php');
					if( $reference_value = \ReferenceValueManager\getById($this->$attribute) )
					{
						$this->$attribute = $reference_value->text;
					}
					break;
			}
			$html .= "<tr><td>" . $attribute . "</td><td>" . $this->$attribute . "</td></tr>\n";
		}
		$html .= "</table>\n<div class=\"modal-actions\">";

		// If no javascript we add a return button and close the 'main' tag
		if( $p_javascript )
		{
			$html .= '<button type="button" class="modal-close" onclick="removeModal(\'modal-action\')" title="Close"><img src="public/img/ok.svg" alt="Close"></button>';
		}
		else
		{
			$html .= '<a class="button modal-close" href="?' . htmlentities($_SESSION['last_page']) . '" title="Go back"><img src="public/img/back.svg" alt="Back"></a>';
		}
		$html .= "</div></form></div></main>\n";
		return $html;
	}

	/**
	 * Returns a html form for the object (add, edit or delete)
	 * @param   bool    $p_javascript       Weither javascript is enable or not (in a modal or not)
	 * @param   string  $p_action           Type of form (possible values : add / edit / delete)
	 * @param   string  p_object_name       Object name, used for variables name
	 * @param   array   $p_page_parameters  Parameter for the form (used for the page to show after the form is canceled or submited)
	 * @return  string                      HTML output
	 */
	function form($p_javascript, $p_action, $p_object_name, $p_page_parameters = array(), $p_delete_name = 'id')
	{
		$modal_title;
		switch( $p_action )
		{
			case 'add':
				$modal_title = 'Add';
				break;
			case 'edit':
				$modal_title = 'Edit';
				break;
			case 'delete':
				$modal_title = 'Delete';
				break;
			default:
				$modal_title = 'Action';
		}
		$close_button_action = ($p_javascript) ? "removeModal('modal-action')" : "location.href='?" . htmlentities($_SESSION['last_page']) . "'";

		$html = '';
		// If no javascript we embed in a 'main' tag
		if( !$p_javascript )
		{
			$html .= "<main>\n";
		}

		$html .= '<h3>' . $modal_title . '</h3>
			<div>
				<form method="get" autocomplete="off">
				<input type="hidden" name="action" value="' . $p_action . '" />
				<input type="hidden" name="' . $p_object_name . '[id]" value="' . $this->id . '" />';
		foreach($p_page_parameters as $page_parameter_name => $page_parameter_value)
		{
			// There shouldn't be nested arrays in $p_page_parameters (can hapen after an action error, no need to pass those parameters)
			if( !is_array($page_parameter_value) )
			{
				$html .= '<input type="hidden" name="' . $page_parameter_name . '" value="' . $page_parameter_value . '" />';
			}
		}
		if( 'delete' === $p_action )
		{
			$object_name = !empty($p_delete_name) ? '<span class="highlighted">' . $this->$p_delete_name . '</span> ' : "this entry";
			$html .= '<p>Do you want to delete ' . $object_name . '</span>?</p>
				<div class="modal-actions">
					<button type="button" class="modal-close" onclick="' . $close_button_action . '" title="Cancel"><img src="public/img/cancel.svg" alt="Cancel"></button>
					<button title="Delete project"><img src="public/img/ok.svg" alt="Yes"></button>
				</div></form>';
		}
		else // for add/edit action
		{
			$html .= '<table class="table-view">';
			foreach($this->array_descriptor as $attribute => $type)
			{
				$required = in_array($attribute, $this->required_attributes);
				// If asking for html we don't print raw values
				// This method isn't nice for performance but it is called for one object only to show it details so it isn't worth giving to much work
				switch ($type)
				{
					case 'int':
						$html .= "<tr><td>" . $attribute . '</td><td><input type="number" name="' . $p_object_name . '[' . $attribute . ']" value="'
							. $this->$attribute . '"' . ($required ? ' required="required"' : '') . " /></td></tr>\n";
						break;
					case 'float':
					case 'double':
						$html .= "<tr><td>" . $attribute . '</td><td><input type="number" step="any" name="' . $p_object_name . '[' . $attribute . ']" value="'
							. $this->$attribute . '"' . ($required ? ' required="required"' : '') . " /></td></tr>\n";
						break;
					case 'text':
						$html .= "<tr><td>" . $attribute . '</td><td><textarea name="' . $p_object_name . '[' . $attribute . ']"' . ($required ? ' required="required"' : '') . ">" . $this->$attribute . "</textarea></td></tr>\n";
						break;
					case 'email':
						$html .= "<tr><td>" . $attribute . '</td><td><input type="email" name="' . $p_object_name . '[' . $attribute . ']" value="'
							. $this->$attribute . '"' . ($required ? ' required="required"' : '') . " /></td></tr>\n";
						break;
					case 'password':
						if( 'edit' === $p_action )
						{
							$required = false;
						}
						$html .= "<tr><td>" . $attribute . '</td><td><input type="password" name="' . $p_object_name . '[' . $attribute . ']" value="'
							. '"' . ($required ? ' required="required"' : '') . " /></td></tr>\n";
						break;
					case 'ReferenceValue':
						require_once('Manager/ReferenceValueManager.php');
						// retrieve the list from its id or code : both are given in a string, either by code (default) or by id
						// The use of code by default is due to database flexibility (the code shouldn't be bound the the list id in the database)
						$reference_value = (0 < (int)$this->$attribute) ? \ReferenceValueManager\getById($this->$attribute) : \ReferenceValueManager\getByCode($this->$attribute);
						if( $reference_value)
						{
							$options = \ReferenceValueManager\listOptionsByParent($reference_value->parent_reference_value, $this->$attribute, $required);
							$html .= "<tr><td>" . $attribute . '</td><td><select name="' . $p_object_name . '[' . $attribute . ']">' . $options . "</select></td></tr>\n";
						}
						else
						{
							$html .= "<tr><td>" . $attribute . '</td><td><input type="text" name="' . $p_object_name . '[' . $attribute . ']" value="'
								. $this->$attribute . '"' . ($required ? ' required="required"' : '') . " /></td></tr>\n";
						}
						break;
					case 'fixed': // like readonly exept it has to be given during creation
						if( 'add' === $p_action )
						{
							$html .= "<tr><td>" . $attribute . '</td><td><input type="hidden" name="' . $p_object_name . '[' . $attribute . ']" value="'
								. $this->$attribute . '" />' . $this->$attribute . "</td></tr>\n";
						}
						// No break because fixed should be displayed like readonly for edition
					case 'readonly':
						if( 'edit' === $p_action )
						{
							$html .= "<tr><td>" . $attribute . "</td><td>" . $this->$attribute . "</td></tr>\n";
						}
						break;
					default:
						if( "Object/" === mb_substr($type, 0, 7) )
						{
							$type_object_name = mb_substr($type, 7);
							$list_option_function = '\\' . $type_object_name . 'Manager\\listOptions';

							require_once('Manager/' . $type_object_name . 'Manager.php');
							$options = $list_option_function( $this->$attribute , $required );
							$html .= "<tr><td>" . $attribute . '</td><td><select name="' . $p_object_name . '[' . $attribute . ']">' . $options . "</select></td></tr>\n";
						}
						else
						{
							$html .= "<tr><td>" . $attribute . '</td><td><input type="text" name="' . $p_object_name . '[' . $attribute . ']" value="'
								. $this->$attribute . '"' . ($required ? ' required="required"' : '') . " /></td></tr>\n";
							}
				}
			}
			$html .= '</table>
				<div class="modal-actions">
					<button type="button" class="modal-close" onclick="' . $close_button_action . '" title="Cancel"><img src="public/img/cancel.svg" alt="Cancel"></button>
					<button title="Edit project"><img src="public/img/ok.svg" alt="Edit"></button>
				</div></form></div>';
		}

		// If no javascript we close the 'main' tag
		if( !$p_javascript )
		{
			$html .= "</main>\n";
		}
		return $html;
	}
}


?>
