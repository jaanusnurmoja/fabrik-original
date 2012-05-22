<?php

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

jimport('joomla.application.component.view');

class fabrikViewGooglemap extends JView
{

	function display($tmpl = 'default')
	{
		FabrikHelperHTML::framework();
		FabrikHelperHTML::slimbox();
		$document = JFactory::getDocument();
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$model = $this->getModel();
		$model->setId(JRequest::getVar('id', $usersConfig->get('visualizationid', JRequest::getInt('visualizationid', 0) )));
		$this->row = $model->getVisualization();
		$model->setListIds();
		$js = $model->getJs();
		$this->txt = $model->getText();
		$params = $model->getParams();
		$this->assign('params', $params);
		$tmpl = $params->get('fb_gm_layout', $tmpl);
		$tmplpath = JPATH_ROOT . '/plugins/fabrik_visualization/googlemap/views/googlemap/tmpl/' . $tmpl;
		FabrikHelperHTML::script('media/com_fabrik/js/list.js');

		$uri = JURI::getInstance();
		if ($params->get('fb_gm_center') == 'userslocation') {
			$document->addScript($uri->getScheme() . '://code.google.com/apis/gears/gears_init.js');
			FabrikHelperHTML::script('components/com_fabrik/libs/geo-location/geo.js');
		}

		$src = $this->get('PluginJsClasses');
		FabrikHelperHTML::script($src);
		$tableplugins = "head.ready(function() {\n"
		.$this->get('PluginJsObjects')
		."\n});";
		FabrikHelperHTML::addScriptDeclaration($tableplugins);
		global $ispda;
		if ($ispda == 1)
		{ //pdabot
		  $template = 'static';
		  $this->assign('staticmap', $this->get('StaticMap'));
		}
		else
		{
			$src = $uri->getScheme() . '://maps.google.com/maps/api/js?sensor=' . $params->get('fb_gm_sensor', 'false');
			$document->addScript($src);
			FabrikHelperHTML::script('plugins/fabrik_visualization/googlemap/googlemap.js');
			if ((int) $this->params->get('fb_gm_clustering', '0') == 1)
			{
				FabrikHelperHTML::script('components/com_fabrik/libs/googlemaps/markerclusterer/src/markerclusterer.js');
				//FabrikHelperHTML::script('http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/src/markerclusterer_compiled.js');
				//FabrikHelperHTML::script('components/com_fabrik/libs/googlemaps/markermanager.js');
			}
			else
			{
				//doesnt work in v3
				//FabrikHelperHTML::script('components/com_fabrik/libs/googlemaps/markermanager.js');
			}

			FabrikHelperHTML::addScriptDeclaration($js);
			$template = null;
		}
		FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/googlemap/views/googlemap/tmpl/' . $tmpl . '/template.css');
		//check and add a general fabrik custom css file overrides template css and generic table css
		FabrikHelperHTML::stylesheetFromPath('media/com_fabrik/css/custom.css');
		//check and add a specific viz template css file overrides template css generic table css and generic custom css
		FabrikHelperHTML::stylesheetFromPath('plugins/fabrik_visualization/googlemap/views/googlemap/tmpl/' . $tmpl . '/custom.css');
		$this->assignRef('filters', $this->get('Filters'));
		$this->assign('showFilters', JRequest::getInt('showfilters', $params->get('show_filters')) === 1 ?  1 : 0);
		$this->assign('filterFormURL', $this->get('FilterFormURL'));
		$this->assign('sidebarPosition', $params->get('fb_gm_use_overlays_sidebar'));
		if ($this->get('ShowSideBar'))
		{
			$this->assign('showSidebar', 1);
			$this->assign('overlayUrls', (array) $params->get('fb_gm_overlay_urls'));
			$this->assign('overlayLabels', (array) $params->get('fb_gm_overlay_labels'));
		}
		else
		{
			$this->assign('showSidebar', 0);
		}
		$this->_setPath('template', $tmplpath);
		$this->assign('containerId', $this->get('ContainerId'));
		$this->assignRef('grouptemplates', $this->get('GroupTemplates'));
		echo parent::display($template);
	}

}
?>