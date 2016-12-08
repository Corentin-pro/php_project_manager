<?php

class Controller
{
	public $error = '';
	public $title;
	public $logout_link_hide = ' hidden';
	public $noscript_close = '';
	public $noscript_close_link = '';
	public $admin_nav;

	/**
	 * Default function to show the page header. Also initialises several session variables
	 */
	function printHeader()
	{
		$_SESSION['last_page'] = $_SERVER['QUERY_STRING'];
		if( isset($_SESSION['admin']) && $_SESSION['admin'])
		{
			$this->admin_nav = "<a class=\"button\" href=\"?section=project_management\" title=\"Go to project management\">Project Management</a>\n
				\t\t<a class=\"button\" href=\"?section=reference_value_management\" title=\"Go to reference value management\">Reference Value Management</a>\n
				\t\t<a class=\"button\" href=\"?section=user_management\" title=\"Go to user management\">User Management</a>\n";
		}
		$this->initSessionVariables();
		include 'View/Header.html';
	}

	/**
	 * Initialisation fo several session variables
	 */
	function initSessionVariables()
	{
		if( isset($_GET['close_noscript']))
		{
			$_SESSION['noscript_message_close'] = true;
		}
		if( isset($_SESSION['logged']) )
		{
			$this->logout_link_hide = '';
		}
		if( isset($_SESSION['noscript_message_close']) )
		{
			$this->noscript_close = ' hidden';
		}
		else
		{
			$this->noscript_close_link = '?' . str_replace('&','&amp;',$_SERVER['QUERY_STRING']) . '&amp;close_noscript';
		}
	}

	/**
	 * Default function to show the page footer
	 */
	function printFooter()
	{
		include 'View/Footer.html';
	}

	/**
	 * Default function to show pagination (navigation through multiples pages)
	 * @param   integer  $p_current_page      Current page number (to show as selected)
	 * @param   integer  $p_total_count       Total number of pages
	 * @param   string   $p_extra_parameters  Extra parameters to add the the page links
	 */
	function printPagination($p_current_page, $p_total_count, $p_extra_parameters = '')
	{
		global $section;

		// If pagination needed print page selection
		if( BACKEND_RESULTS_PER_PAGE < $p_total_count )
		{
			$page_count = ceil($p_total_count / BACKEND_RESULTS_PER_PAGE);
			echo "<div class=\"page-navigation\">";
			for( $page_iterator = 1 ; $page_iterator <= $page_count ; $page_iterator++)
			{
				$selected = ($page_iterator === $p_current_page) ? ' selected' : '';
				echo '<a class="button' . $selected . '" href="?section=' , $section , '&amp;page=' , $page_iterator, $p_extra_parameters , '">' , $page_iterator , "</a>";
			}
			echo "</div>\n";
		}
	}
}


?>
