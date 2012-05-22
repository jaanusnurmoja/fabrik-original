<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// No direct access
defined('_JEXEC') or die;

jimport('joomla.application.component.controllerform');

/**
 * Package controller class.
 *
 * @package		Joomla.Administrator
 * @subpackage	com_fabrik
 * @since		1.6
 */
class FabrikControllerPackage extends JControllerForm
{
	/**
	 * @var		string	The prefix to use with controller messages.
	 * @since	1.6
	 */
	protected $text_prefix = 'COM_FABRIK_PACKAGE';


	public function export()
	{
		$cid = JRequest::getVar('cid', array(), 'post', 'array');
		$model = $this->getModel();
		$model->export($cid);
		$ntext = $this->text_prefix.'_N_ITEMS_EXPORTED';
		$this->setMessage(JText::plural($ntext, count($cid)));
		$this->setRedirect('index.php?option=com_fabrik&view=packages');
	}
	
	public function view()
	{
		$document = JFactory::getDocument();
		//$model = JModel::getInstance('Form', 'FabrikFEModel');
		$viewType	= $document->getType();
		$this->setPath('view', COM_FABRIK_FRONTEND.DS.'views');
		$viewLayout	= JRequest::getCmd('layout', 'default');
		$view = $this->getView('form', $viewType, '');
		//$view->setModel($model, true);
		$view->isMambot = $this->isMambot;
		// Set the layout
		$view->setLayout($viewLayout);

		
		//if the view is a package create and assign the table and form views
		$listView = $this->getView('list', $viewType);
		$listModel = $this->getModel('list', 'FabrikFEModel');
		$listView->setModel($listModel, true);
		$view->_tableView = $listView;
		
		$view->_formView = &$this->getView('Form', $viewType);
		$formModel = $this->getModel('Form', 'FabrikFEModel');
		$formModel->setDbo(FabrikWorker::getDbo());
		$view->_formView->setModel($formModel, true);
		
		// Push a model into the view
		$model = $this->getModel($viewName, 'FabrikFEModel');
		$model->setDbo(FabrikWorker::getDbo());
		
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		
		//todo check for cached version
		//JToolBarHelper::title(JText::_('COM_FABRIK_MANAGER_FORMS'), 'forms.png');
		$view->display();
	}
	
	public function listform()
	{
		$document = JFactory::getDocument();
		$this->Form	= $this->get('PackageListForm');
		$viewType	= $document->getType();
		$view = $this->getView('package', $viewType, '');
		// Push a model into the view
		$model = $this->getModel();
		$model->setDbo(FabrikWorker::getDbo());
		
		if (!JError::isError($model)) {
			$view->setModel($model, true);
		}
		$view->listform();
	}

}
