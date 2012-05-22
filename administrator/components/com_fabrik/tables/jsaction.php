<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

require_once(JPATH_ADMINISTRATOR.DS.'components'.DS.'com_fabrik'.DS.'tables'.DS.'fabtable.php');

/**
 * @package		Joomla
 * @subpackage	Fabrik
 */
class FabrikTableJsaction extends FabTable
{

	/*
	 *
	 */

	function __construct(&$_db)
	{
		parent::__construct('#__{package}_jsactions', 'id', $_db);
	}

}
?>