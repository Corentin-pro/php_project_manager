<?php

require_once('Class/Controller.php');

class ReferenceValueManagementController extends Controller
{
	var $page = 1;
	var $action = '';

	function render()
	{
		require_once('Manager/ReferenceValueManager.php'); // include manager here so it can print exceptions

		$this->page = (!empty($_GET['page'])) ? (int)$_GET['page'] : 1;
		$this->action = isset($_GET['action']) ? $_GET['action'] : "";

		switch( $this->action )
		{
			case 'add':
				if( !empty($_GET['reference_value']) )
				{
					require_once('Class/ReferenceValue.php');
					$reference_value = new ReferenceValue($_GET['reference_value'], false);
					if( $reference_value->insert() )
					{
						$count = ReferenceValueManager\count();
						if( $count > (BACKEND_RESULTS_PER_PAGE * $this->page)  )
						{
							$this->page += 1;
						}
						header('Location: ?section=reference_value_management&page=' . $this->page );
					}
					else
					{
						$this->error .= '<p>Adding \'<span class="highlighted">' . $reference_value->login  . '</span>\' failed.</p>';
					}
				}
				break;
			case 'edit':
				if( !empty($_GET['reference_value']) )
				{
					require_once('Class/ReferenceValue.php');
					$reference_value = new ReferenceValue($_GET['reference_value'], false);
					if( $reference_value->update() )
					{
						header('Location: ?section=reference_value_management&page=' . $this->page );
					}
					else
					{
						$this->error .= 'Editing failed.';
					}
				}
				break;
			case 'delete':
				$reference_value_id = (isset($_GET['reference_value']['id'])) ? (int)($_GET['reference_value']['id']) : 0;
				if( 0 < $reference_value_id )
				{
					if( ReferenceValueManager\deleteById($reference_value_id) )
					{
						$count = ReferenceValueManager\count();
						if( $count <= (BACKEND_RESULTS_PER_PAGE * ($this->page - 1))  )
						{
							$this->page -= 1;
						}
						header('Location: ?section=reference_value_management&page=' . $this->page );
					}
					else
					{
						$this->error .= 'Deleting \'<span class="highlighted">' . $this->reference_value_delete_name  . '</span>\' failed.';
					}
				}
				else
				{
					$this->error .= 'Deleting reference value failed (no valid id given).';
				}
				break;
		}

		$this->title = "ReferenceValue Management";
		Parent::printHeader();
		include 'View/Backend/ReferenceValueManagementView.html';
		Parent::printFooter();
	}

	// Generate a table showing the reference_values
	function printReferenceValueTable()
	{
		$count = ReferenceValueManager\count();

		$this->printPagination($this->page, $count);
		ReferenceValueManager\printReferenceValueTable($this->page);
	}
}

?>
