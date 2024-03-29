<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.controller');

/**
 * Fabrik Plugin Controller
 *
 *DEPRECIATED SEE NOTE FROM 11/07/2011
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */
class FabrikControllerPlugin extends JController
{
	/**
	 *
	 * Means that any method in Fabrik 2, e.e. 'ajax_upload' should
	 * now be changed to 'onAjax_upload'
	 * ajax action called from element
	 *
	 * 11/07/2011 - ive updated things so that any plugin ajax call uses 'view=plugin' rather than controller=plugin
	 * this means that the controller used is now plugin.php and not plugin.raw.php
	 */

	function pluginAjax()
	{
		//$formid = JRequest::getInt('formid', 0);
		//$id = JRequest::getInt('element_id', 0);
		$plugin = JRequest::getVar('plugin', '');
		$method = JRequest::getVar('method', '');
		$group = JRequest::getVar('g', 'element');

		if (!JPluginHelper::importPlugin('fabrik_'.$group, $plugin)) {
			$o = new stdClass();
			$o->err = 'unable to import plugin fabrik_'.$group.' '.$plugin;
			echo json_encode($o);
			return;
		}

		$dispatcher = JDispatcher::getInstance();
		if (substr($method, 0, 2) !== 'on') {
			$method = 'on'.JString::ucfirst($method);
		}
		$dispatcher->trigger($method);
		return;
	}

	/**
	 * custom user ajax class handling as per F1.0.x
	 * @return unknown_type
	 */
	function userAjax()
	{
		$db = FabrikWorker::getDbo();
		require_once(COM_FABRIK_FRONTEND . DS. "user_ajax.php");
		$method = JRequest::getVar('method', '');
		$userAjax = new userAjax($db);
		if (method_exists($userAjax, $method)) {
			$userAjax->$method();
		}
	}

	function doCron(&$pluginManager)
	{
		$db = FabrikWorker::getDbo();
		$cid = JRequest::getVar('element_id', array(), 'method', 'array');
		$query = $db->getQuery();
		$query->select('id, plugin')->from('#__{package}_cron');
		if (!empty($cid)) {
			$query->where(" id IN (" . implode(',', $cid).")");
		}
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		$viewModel = JModel::getInstance('view', 'FabrikFEModel');
		$c = 0;
		foreach ($rows as $row) {
			//load in the plugin
			$plugin = $pluginManager->getPlugIn($row->plugin, 'cron');
			$plugin->setId($row->id);
			$params = $plugin->getParams();

			$thisViewModel = clone($viewModel);
			$thisViewModel->setId($params->get('table'));
			$table = $viewModel->getTable();
			$total 						= $thisViewModel->getTotalRecords();
			$nav = $thisViewModel->getPagination($total, 0, $total);
			$data  = $thisViewModel->getData();
			// $$$ hugh - added table model param, in case plugin wants to do further table processing
			$c = $c + $plugin->process($data, $thisViewModel);
		}
		$query = $db->getQuery();
		$query->update('#__{package}_cron')->set('lastrun=NOW()')->where("id IN (".implode(',', $cid).")");
		$db->setQuery($query);
		$db->query();
	}

}
?>