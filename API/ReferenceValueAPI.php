<?php
namespace
{
	require_once('Manager/ReferenceValueManager.php');
	require_once('Class/APIResult.php');

	$action = isset($_GET['action']) ? $_GET['action'] : '';
	$javascript = isset($_GET['js']);
	$page_parameter = array();
	if( !empty($_SESSION['last_page']) )
	{
		parse_str($_SESSION['last_page'], $page_parameter);
	}
	$output;

	switch($action)
	{
		case 'details':
			$reference_value_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			if( 0 < $reference_value_id )
			{
				$output = ReferenceValueAPI\referenceValueDetails($javascript, $reference_value_id);
			}
			break;
		case 'form_add':
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			$output = ReferenceValueAPI\referenceValueForm($javascript, 'add', 0, $page_parameter);
			break;
		case 'form_edit':
		case 'form_delete':
			$action = ('form_edit' === $action) ? 'edit' : 'delete';
			$reference_value_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
			$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
			if( 0 < $reference_value_id )
			{
				$output = ReferenceValueAPI\referenceValueForm($javascript, $action, $reference_value_id, $page_parameter);
			}
			break;
		default:
			$output = new \APIResult();
			$output->status = 'error';
			$output->message = 'Unkown action.';
			break;
	}

	if( empty($output) ) // Default error message (if a valid project id is given an error is set in the $output variable)
	{
		$output = new \APIResult();
		$output->status = 'error';
		$output->message = 'No valid id given.';
	}

	if( $javascript )
	{
		echo json_encode($output);
	}
	else
	{
		require_once('Controller/APIController.php');
		$controller = new APIController();
		$controller->message = $output->message;
	}
}

namespace ReferenceValueAPI
{
	/**
	 * Output html showing details of an ReferenceValue
	 * @param  bool     $p_javascript  If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param  integer  $p_reference_value_id     Id of the ReferenceValue
	 * @return String
	 */
	function referenceValueDetails($p_javascript, $p_reference_value_id)
	{
		$api_result = new \APIResult();
		if( $reference_value = \ReferenceValueManager\getById($p_reference_value_id) )
		{
			$api_result->status = 'ok';
			if( isset($_GET['output']) && ('html' === $_GET['output']) )
			{
				$api_result->message = $reference_value->details($p_javascript);
			}
			else
			{
				$api_result->message = $reference_value;
			}
			return $api_result;
		}
		else
		{
			$api_result->status = 'error';
			$api_result->message = 'No reference value found with id ' . $p_reference_value_id;
			return $api_result;
		}
	}

	/**
	* Output html showing a form to add, edit or delete an ReferenceValue
	 * @param   bool     $p_javascript      If true outputs in a JSON encoded APIResult (to be parsed by the javascript layer)
	 * @param   string   $p_action          Type of form (possible values : add / edit / delete)
	 * @param   integer  $p_reference_value_id         Id of the ReferenceValue
	 * @param   array    $p_page_parameter  Parameters to return to the previous page if form is canceled
	 * @return  string
	 */
	function referenceValueForm($p_javascript, $p_action, $p_reference_value_id, $p_page_parameter = array() )
	{
		$api_result = new \APIResult();
		switch( $p_action )
		{
			case 'edit':
			case 'delete':
				if( $reference_value = \ReferenceValueManager\getById($p_reference_value_id) )
				{
					$delete_name = ('delete' === $p_action) ? 'code' : null;
					$api_result->status = 'ok';
					$api_result->message = $reference_value->form($p_javascript, $p_action, 'reference_value', $p_page_parameter, $delete_name);
					return $api_result;
				}
				else
				{
					$api_result->status = 'ok';
					$api_result->message = 'No reference value found with id ' . $p_reference_value_id;
					return $api_result;
				}
				break;
			default:
				require_once('Class/ReferenceValue.php');
				$reference_value = new \ReferenceValue(array());
				$api_result->status = 'ok';
				$api_result->message = $reference_value->form($p_javascript, $p_action, 'reference_value', $p_page_parameter);
				return $api_result;
		}
	}
}
?>
