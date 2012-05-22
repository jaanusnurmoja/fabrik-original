<?php

/**
 * @package		Joomla
 * @subpackage	Fabik
 * @copyright	Copyright (C) 2005 - 2008 Pollen 8 Design Ltd. All rights reserved.
 * @license		GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class FabrikViewList extends JView{

	function display()
	{
		$document = JFactory::getDocument();
		$model		=& $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');

		$model->setId(JRequest::getVar('listid', $usersConfig->get('listid')));
		$model->render();
		$table = $model->getTable();

		$document->title = $table->label;
		$document->description = $table->introduction;
		$document->copyright = '';
		$document->listid = $table->id;

		$document->items = $model->getData();

	}
}
?>

