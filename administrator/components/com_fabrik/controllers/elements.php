<?php
/**
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// No direct access.
defined('_JEXEC') or die;

require_once('fabcontrolleradmin.php');

/**
 * Elements list controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */

class FabrikControllerElements extends FabControllerAdmin
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */

	protected $text_prefix = 'COM_FABRIK_ELEMENTS';

	protected $view_item = 'elements';

	/**
	 * Constructor.
	 *
	 * @param	array An optional associative array of configuration settings.
	 * @see		JController
	 * @since	1.6
	 */

	public function __construct($config = array())
	{
		parent::__construct($config);
		$this->registerTask('showInListView',	'toggleInList');	// value = 0
		$this->registerTask('hideFromListView',	'toggleInList');	// value = 0
	}

	/**
	 * Proxy for getModel.
	 * @since	1.6
	 */

	public function &getModel($name = 'Element', $prefix = 'FabrikModel')
	{
		$config = array();
		$config['ignore_request'] = true;
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}

	public function toggleInList()
	{
		// Check for request forgeries
		JRequest::checkToken() or die(JText::_('JINVALID_TOKEN'));

		// Get items to publish from the request.
		$cid	= JRequest::getVar('cid', array(), '', 'array');
		$data	= array('showInListView' => 1, 'hideFromListView' => 0);
		$task 	= $this->getTask();
		$value	= JArrayHelper::getValue($data, $task, 0, 'int');

		if (empty($cid)) {
			JError::raiseWarning(500, JText::_($this->text_prefix.'_NO_ITEM_SELECTED'));
		} else {
			// Get the model.
			$model = $this->getModel();

			// Make sure the item ids are integers
			JArrayHelper::toInteger($cid);

			// Publish the items.
			if (!$model->addToListView($cid, $value)) {
				JError::raiseWarning(500, $model->getError());
			} else {
				if ($value == 1) {
					$ntext = $this->text_prefix.'_N_ITEMS_ADDED_TO_LIST_VIEW';
				} else {
					$ntext = $this->text_prefix.'_N_ITEMS_REMOVED_FROM_LIST_VIEW';
				}
				$this->setMessage(JText::plural($ntext, count($cid)));
			}
		}
		$this->setRedirect(JRoute::_('index.php?option='.$this->option.'&view='.$this->view_list, false));
	}

/**
	 * Set up page asking about what to delete
	 *
	 * @since	1.6
	 */

	function delete()
	{
		$model = $this->getModel('Elements');
		$viewType	= JFactory::getDocument()->getType();
		$view = & $this->getView($this->view_item, $viewType);
		$view->setLayout('confirmdelete');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		//used to load in the confirm form fields
		$view->setModel($this->getModel('list'));
		$view->display();
	}

	/**
	 * cancel delete element
	 * @return null
	 */

	function cancel()
	{
		$this->setRedirect('index.php?option=com_fabrik&view=elements');
	}


	public function copySelectGroup()
	{
		JRequest::checkToken() or die('Invalid Token');
		$model = $this->getModel('Elements');
		$viewType	= JFactory::getDocument()->getType();
		$view = & $this->getView($this->view_item, $viewType);
		$view->setLayout('copyselectgroup');
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		//used to load in the confirm form fields
		$view->setModel($this->getModel('list'));
		$view->display();
	}

}