<?php
/**
 * Plugin element to render fields
 * @package fabrikar
 * @author Rob Clayburn
 * @copyright (C) Rob Clayburn
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 */

// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();

class flashRender{

	var $output = '';

	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 * @param object all row's data
	 */

	function renderListData(&$model, &$params, $file, $oAllRowsData)
	{
		$this->render($model, $params, $file);
	}

	/**
	 * @param object element model
	 * @param object element params
	 * @param string row data for this element
	 */

	function render(&$model, &$params, $file)
	{

		$fbConfig = JComponentHelper::getParams('com_fabrik');
		ini_set('display_errors', true);
		require_once(COM_FABRIK_FRONTEND.DS.'libs'.DS.'getid3'.DS.'getid3'.DS.'getid3.php');
		require_once(COM_FABRIK_FRONTEND.DS.'libs'.DS.'getid3'.DS.'getid3'.DS.'getid3.lib.php');

		getid3_lib::IncludeDependency(COM_FABRIK_FRONTEND.DS.'libs'.DS.'getid3'.DS.'getid3'.DS.'extension.cache.mysql.php', __FILE__, true);
		$config = JFactory::getConfig();
		$host =  $config->getValue('host');
		$database = $config->getValue('db');
		$username = $config->getValue('user');
		$password = $config->getValue('password');
		$getID3 = new getID3_cached_mysql($host, $database, $username, $password);
		// Analyze file and store returned data in $ThisFileInfo
		$relPath = str_replace("\\", "/", JPATH_SITE  . $file);
		$thisFileInfo = $getID3->analyze($relPath);

		//var_dump($relPath, $thisFileInfo);
		$w = $params->get('fu_main_max_width', 0);
		$h = $params->get('fu_main_max_height', 0);
		if ($thisFileInfo && array_key_exists('swf', $thisFileInfo)) {
			if ($thisFileInfo['swf']['header']['frame_width'] < $w
			|| $thisFileInfo['swf']['header']['frame_height'] < $h) {
				$w = $thisFileInfo['swf']['header']['frame_width'];
				$h = $thisFileInfo['swf']['header']['frame_height'];
			}
		}
		if ($w <= 0 || $h <= 0) {
			$w = 800;
			$h = 600;
		}
		// $$$ hugh - if they've enabled thumbnails, for Flash content we'll take that to mean they don't
		// want to play the content inline in the table, and use mediabox (if available) to open it instead.
		if (!$model->_inDetailedView && $fbConfig->get('use_mediabox', true) && $params->get('make_thumbnail', false)) {
			$element = $model->getElement();

			// @TODO - work out how to do thumbnails
			// $$$ hugh - thought about using thumbed-down embedded Flash as
			// thumbnail, but really need to avoid having to download every flash in
			// the table on page load!  But just doesn't seem to be a way of getting
			// a thumbnail from a Flash.  For now, just use a default "Here Be Flash"
			// icon for the thumb.  Might be nice to add 'icon to use' option for Upload
			// element, so if it isn't an image type, you can point at another element on the form,
			// (either and Upload or an Image) and use that as the thumb for this content.
			/*
				$thumb_w = $params->get('thumb_max_width');
				$thumb_h = $params->get('thumb_max_height');
				$file = str_replace("\\", "/", COM_FABRIK_LIVESITE  . $file);
				$this->output = "<object width=\"$w\" height=\"$h\">
				<param name=\"movie\" value=\"$file\">
				<embed src=\"$file\" width=\"$thumb_w\" height=\"$thumb_h\">
				</embed>
				</object>";
				*/
			$thumb_dir = $params->get('thumb_dir');
			if (!empty( $thumb_dir)) {
				$file = str_replace("\\", "/", $file);
				$pathinfo = pathinfo( $file);
				// $$$ hugh - apparently filename ocnstant only added in PHP 5.2
				if (!isset($pathinfo['filename'])) {
					$pathinfo['filename'] = explode('.',$pathinfo['basename']);
					$pathinfo['filename'] = $pathinfo['filename'][0];
				}
				$thumb_path = COM_FABRIK_BASE.$thumb_dir.DS.$pathinfo['filename'].'.png';
				if (JFile::exists($thumb_path)) {
					$thumb_file = COM_FABRIK_LIVESITE . '/' . $thumb_dir . '/' . $pathinfo['filename'] . '.png';
				}
				else {
					$thumb_file = COM_FABRIK_LIVESITE . "media/com_fabrik/images/flash.jpg";
				}
			}
			else {
				$thumb_file = COM_FABRIK_LIVESITE . "media/com_fabrik/images/flash.jpg";
			}
			$file = str_replace("\\", "/", COM_FABRIK_LIVESITE  . $file);
			$this->output .=	"<a href='$file' rel='lightbox[flash $w $h]'><img src='$thumb_file' alt='Full Size'></a>";
		}
		else if ($model->_inDetailedView) {
			$file = str_replace("\\", "/", COM_FABRIK_LIVESITE  . $file);
			$this->output = "<object width=\"$w\" height=\"$h\">
				<param name=\"movie\" value=\"$file\">
				<embed src=\"$file\" width=\"$w\" height=\"$h\">
				</embed>
				</object>";
		}
		else {
			$file = str_replace("\\", "/", COM_FABRIK_LIVESITE  . $file);
			$this->output = "<object width=\"$w\" height=\"$h\">
				<param name=\"movie\" value=\"$file\">
				<embed src=\"$file\" width=\"$w\" height=\"$h\">
				</embed>
				</object>";
		}
		//}
	}
}
?>