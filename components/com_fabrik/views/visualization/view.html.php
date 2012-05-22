<?php
/**
* @package Joomla
* @subpackage Fabrik
* @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class FabrikViewVisualization extends JView{

	function display($tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		$model = $this->getModel();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0))));
		$visualization = $model->getVisualization();
		$pluginParams = $model->getPluginParams();

		$pluginManager = JModel::getInstance('Pluginmanager', 'FabrikModel');
		$plugin = $pluginManager->getPlugIn($visualization->plugin, 'visualization');
		$plugin->_row = $visualization;
		if ($visualization->published == 0) {
			return JError::raiseWarning(500, JText::_('COM_FABRIK_SORRY_THIS_VISUALIZATION_IS_UNPUBLISHED'));
		}

		//plugin is basically a model


		$pluginTask = JRequest::getVar('plugintask', 'render', 'request');
		// @FIXME cant set params directly like this, but I think plugin model setParams() is not right
		$plugin->_params = $pluginParams;
		$tmpl = $plugin->getParams()->get('calendar_layout', $tmpl);
		$plugin->$pluginTask($this);
		$this->plugin = $plugin;
		$viewName = $this->getName();
		$this->addTemplatePath($this->_basePath.DS.'plugins'.DS.$this->_name.DS.$plugin->_name.DS.'tmpl'.DS.$tmpl);
		$this->addTemplatePath(JPATH_SITE.DS.'templates'.DS.$app->getTemplate().DS.'html'.DS.'com_fabrik'.DS.'visualization'.DS.$plugin->_name.DS.$tmpl);

		$ab_css_file = JPATH_SITE."/plugins/fabrik_visualization/".$plugin->_name."/tmpl/$tmpl/template.css";
		if (JFile::exists($ab_css_file))
		{
			JHTML::stylesheet('template.css', 'plugins/fabrik_visualization/'.$plugin->_name.'/tmpl/'.$tmpl.'/', true);
		}
		echo parent::display();
	}

	//just for plugin
	function setId()
	{

	}
}
?>