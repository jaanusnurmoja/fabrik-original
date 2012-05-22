<?php

/**
 * Process exif info from images
 * @package Joomla
 * @subpackage Fabrik
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();


//require the abstract plugin class
require_once(COM_FABRIK_FRONTEND.DS.'models'.DS.'plugin-form.php');

class plgFabrik_FormVbForum extends plgFabrik_Form {

	var $vb_forum_field = '';
	var $vb_path = '';
	var $vb_globals = '';

	/**
	 * process the plugin, called when form is submitted
	 *
	 * @param object $params
	 * @param object form
	 */

	function onBeforeStore(&$params, &$formModel)
	{
		global $vbulletin;
		define(VB_AREA, 'fabrik');
		define(THIS_SCRIPT, 'fabrik');

		// Initialize some variables
		$db	= FabrikWorker::getDbo();

		$data = $formModel->_formData;

		// Check for request forgeries
		JRequest::checkToken() or jexit('Invalid Token');

		$elementModel = FabrikWorker::getPluginManager()->getElementPlugin($params->get('vb_forum_field'));

		$element = $elementModel->getElement(true);
		$this->map_forum_field = $elementModel->getFullName();

		$this->vb_parent_forum = $params->get('vb_parent', '');

		$method = "POST";
		$url = JURI::base(). "forum/mkforum.php";
		$vars = array();
		$vars['forum_name'] = $data[$this->map_forum_field];
		$vars['forum_parent'] = $this->vb_parent_forum;
		$res = $this->doRequest($method, $url, $vars);
	}

	private function doRequest($method, $url, $vars)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_COOKIEJAR, 'cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEFILE, 'cookie.txt');
		if ($method == 'POST') {
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $vars);
		}
		$data = curl_exec($ch);
		curl_close($ch);
		if ($data) {
			return $data;
		} else {
			return curl_error($ch);
		}
	}

}
?>