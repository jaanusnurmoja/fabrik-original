<?php
/**
 * @version
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

if (!defined('COM_FABRIK_FRONTEND')) {
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}

jimport('joomla.filesystem.file');
jimport('joomla.application.component.model');
jimport('joomla.application.component.helper');
JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models', 'FabrikFEModel');
JModel::addIncludePath(COM_FABRIK_FRONTEND.DS.'models');
JTable::addIncludePath(COM_FABRIK_BASE.DS.'administrator'.DS.'components'.DS.'com_fabrik'.DS.'tables');

require_once(COM_FABRIK_FRONTEND.DS.'controller.php');
require_once(COM_FABRIK_FRONTEND.DS.'controllers'.DS.'list.php');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'list'.DS.'view.html.php');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'package'.DS.'view.html.php');
require_once(COM_FABRIK_FRONTEND.DS.'controllers'.DS.'package.php');
require_once(COM_FABRIK_FRONTEND.DS.'views'.DS.'form'.DS.'view.html.php');

//load front end language file as well
$lang = JFactory::getLanguage();
$lang->load('com_fabrik', JPATH_BASE.DS.'components'.DS.'com_fabrik');

$app = JFactory::getApplication();
$document = JFactory::getDocument();

FabrikHelperHTML::framework();
//$$$rob looks like including the view does something to the layout variable
$origLayout = JRequest::getVar('layout');

$listId = (int)$params->get('list_id', 1);
$useajax = (int)$params->get('useajax', 0);
$random	= (int)$params->get('radomizerecords', 0);
$limit = (int)$params->get('limit', 0);
$showTitle = $params->get('show-title', '');
$layout	= $params->get('fabriklayout', '');
JRequest::setVar('layout', $layout);

$moduleclass_sfx = $params->get('moduleclass_sfx', '');

$listId	= intval($params->get('list_id', 0));
if ($listId === 0) {
	JError::raiseError(500, 'no list specified');
}

$listels = json_decode($params->get('list_elements'));
if (isset($listels->show_in_list)) {
	JRequest::setVar('fabrik_show_in_list', $listels->show_in_list);
}

$viewName = 'list';
$viewType	= $document->getType();
$controller = new FabrikControllerList();

// Set the default view name from the Request
$view = clone($controller->getView($viewName, $viewType));

// Push a model into the view
$model = $controller->getModel($viewName, 'FabrikFEModel');
$model->setId($listId);
$model->setRenderContext($module->id);

if ($limit !== 0) {
	$app->setUserState('com_fabrik.list'.$model->getRenderContext().'.limitlength', $limit);
	JRequest::setVar('limit'.$listId, $limit);
}

if ($useajax !== '') {
	$model->set('ajax', $useajax);
}

if ($params->get('ajax_links') !== '') {
	$listParams = $model->getParams();
	$listParams->set('list_ajax_links', $params->get('ajax_links'));
}

if ($params->get('show_nav', '') !== '') {
	$listParams->set('show-table-nav', $params->get('show_nav'));
}

if ($showTitle !== '') {
	$listParams->set('show-title', $showTitle);
}

$ordering = JArrayHelper::fromObject(json_decode($params->get('ordering')));
$orderBy = (array)$ordering['order_by'];
$orderDir = (array)$ordering['order_dir'];
if (!empty($orderBy)) {
	$model->getTable()->order_by = json_encode($orderBy);
	$model->getTable()->order_dir = json_encode($orderDir);
}

//set up prefilters - will overwrite ones defined in the list!

$prefilters = JArrayHelper::fromObject(json_decode($params->get('prefilters')));
$conditions = (array)$prefilters['filter-conditions'];
if (!empty($conditions)) {
	$listParams->set('filter-fields', $prefilters['filter-fields']);
	$listParams->set('filter-conditions', $prefilters['filter-conditions']);
	$listParams->set('filter-value', $prefilters['filter-value']);
	$listParams->set('filter-access', $prefilters['filter-access']);
	$listParams->set('filter-eval', $prefilters['filter-eval']);
}

$model->randomRecords = $random;
if (!JError::isError($model)) {
	$view->setModel($model, true);
}
$view->isMambot = true;
// Display the view
$view->assign('error', $controller->getError());
echo $view->display();

JRequest::setVar('layout', $origLayout);
?>