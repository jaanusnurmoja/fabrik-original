<?php
/**
 * @package Joomla
 * @subpackage Fabrik
 * @copyright Copyright (C) 2005 Pollen 8 Design Ltd. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
 */

// no direct access
defined('_JEXEC') or die('Restricted access');
jimport('joomla.filesystem.file');
if (!defined('COM_FABRIK_FRONTEND')) {
	JError::raiseError(400, JText::_('COM_FABRIK_SYSTEM_PLUGIN_NOT_ACTIVE'));
}
require_once(COM_FABRIK_FRONTEND . '/helpers/string.php');
require_once(COM_FABRIK_FRONTEND . '/helpers/parent.php');//leave in as for some reason content plguin isnt loading the fabrikworker class
/**
 * Fabrik Component HTML Helper
 *
 * @static
 * @package		Joomla
 * @subpackage	Fabrik
 * @since 1.5
 */
class FabrikHelperHTML
{

	protected static $framework = null;

	protected static $mcl = null;

	protected static $modals = array();

	protected static $tips = array();

	protected static $jsscript = false;

	protected static $ajaxCssFiles = array();

	protected static $debug = null;

	protected static $autocomplete = null;

	protected static $facebookgraphapi = null;

	protected static $helperpaths = array();

	protected static $modal = null;

	/**
	 * load up window code - should be run in ajax loaded pages as well
	 * might be an issue in that we may be re-observing some links when loading in - need to check
	 * @deprecated use windows() instead
	 * @param string element select to auto create windows for  - was default = a.modal
	 */

	public static function mocha($selector='', $params = array())
	{
		FabrikHelperHTML::windows($selector, $params);
	}

	public static function windows($selector='', $params = array())
	{
		$script = '';

		$document = JFactory::getDocument();

		$sig = md5(serialize(array($selector,$params)));
		if (isset(self::$modals[$sig]) && (self::$modals[$sig]))
		{
			return;
		}

		$script .= "head.ready(function() {";
		if ($selector == '')
		{
			return;
		}

		// Setup options object
		$opt['ajaxOptions']	= (isset($params['ajaxOptions']) && (is_array($params['ajaxOptions']))) ? $params['ajaxOptions'] : null;
		$opt['size']		= (isset($params['size']) && (is_array($params['size']))) ? $params['size'] : null;
		$opt['onOpen']		= (isset($params['onOpen'])) ? $params['onOpen'] : null;
		$opt['onClose']		= (isset($params['onClose'])) ? $params['onClose'] : null;
		$opt['onUpdate']	= (isset($params['onUpdate'])) ? $params['onUpdate'] : null;
		$opt['onResize']	= (isset($params['onResize'])) ? $params['onResize'] : null;
		$opt['onMove']		= (isset($params['onMove'])) ? $params['onMove'] : null;
		$opt['onShow']		= (isset($params['onShow'])) ? $params['onShow'] : null;
		$opt['onHide']		= (isset($params['onHide'])) ? $params['onHide'] : null;

		$options = json_encode($opt);
		// Attach modal behavior to document
		//set default values which can be overwritten in <a>'s rel attribute

		$opts = new stdClass();
		$opts->id = 'fabwin';
		$opts->title = JText::_('COM_FABRIK_ADVANCED_SEARCH');
		$opts->loadMethod = 'xhr';
		$opts->minimizable = false;
		$opts->collapsible = true;
		$opts->width = 500;
		$opts->height = 150;
		$opts = json_encode($opts);

		$script .= <<<EOD

  $$('$selector').each(function(el, i) {
    el.addEvent('click', function(e) {
    	var opts = $opts;
    	e.stop();
      opts2 = JSON.decode(el.get('rel'));
      opts = Object.merge(opts, opts2 || {});
      opts.contentURL = el.href;
      if (opts.id === 'fabwin') {
      	opts.id += i;
      }
      opts.onContentLoaded = function() {
  			Fabrik.Windows[opts.id].fitToContent()
			};
      Fabrik.getWindow(opts);
    });
  });
});
EOD;

		FabrikHelperHTML::addScriptDeclaration($script);
		self::$modals[$sig] = true;
		return;
	}

	/**
	 * show form to allow users to email form to a friend
	 * @param object form
	 */

	function emailForm($formModel, $template='')
	{
		$document = JFactory::getDocument();
		$form = $formModel->getForm();
		$document->setTitle($form->label);
		$document->addStyleSheet("templates/'. $template .'/css/template_css.css");
		//$url = JRoute::_('index.php?option=com_fabrik&view=emailform&tmpl=component');
		?>
<form method="post" action="index.php" name="frontendForm">
	<table>
		<tr>
			<td><label for="email"><?php echo JText::_('COM_FABRIK_YOUR_FRIENDS_EMAIL' ) ?>:</label>
			</td>
			<td><input type="text" size="25" name="email" id="email" /></td>
		</tr>
		<tr>
			<td><label for="yourname"><?php echo JText::_('COM_FABRIK_YOUR_NAME'); ?>:</label>
			</td>
			<td><input type="text" size="25" name="yourname" id="yourname" /></td>
		</tr>
		<tr>
			<td><label for="youremail"><?php echo JText::_('COM_FABRIK_YOUR_EMAIL'); ?>:</label>
			</td>
			<td><input type="text" size="25" name="youremail" id="youremail" /></td>
		</tr>
		<tr>
			<td><label for="subject"><?php echo JText::_('COM_FABRIK_MESSAGE_SUBJECT'); ?>:</label>
			</td>
			<td><input type="text" size="40" maxlength="40" name="subject"
				id="subject" /></td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" name="submit" class="button"
				value="<?php echo JText::_('COM_FABRIK_SEND_EMAIL'); ?>" />
				&nbsp;&nbsp; <input type="button" name="cancel"
				value="<?php echo JText::_('COM_FABRIK_CANCEL'); ?>" class="button"
				onclick="window.close();" /></td>
		</tr>
	</table>
	<input name="referrer"
		value="<?php echo JRequest::getVar('referrer' )?>" type="hidden" /> <input
		type="hidden" name="option" value="com_fabrik" /> <input type="hidden"
		name="view" value="emailform" /> <input type="hidden" name="tmpl"
		value="component" />

	 <?php echo JHTML::_('form.token'); ?></form>
		<?php
	}

	/**
	 * once email has been sent to a frind show this message
	 */

	function emailSent($to, $ok)
	{
		$config = JFactory::getConfig();
		$document = JFactory::getDocument();
		$document->setTitle($config->get('sitename'));

		if ($ok)
		{?>
<span class="contentheading"><?php echo JText::_('COM_FABRIK_THIS_ITEM_HAS_BEEN_SENT_TO')." $to";?>
</span>
<?php }?>
<br />
<br />
<br />
<a href='javascript:window.close();'> <span class="small"><?php echo JText::_('COM_FABRIK_CLOSE_WINDOW');?>
</span> </a>


<?php
	}

	/**
	 * writes a print icon
	 * @param object form
	 * @param object parameters
	 * @param int row id
	 * @return string print html icon/link
	 */

	function printIcon($formModel, $params, $rowid = '')
	{
		$app = JFactory::getApplication();
		$config	= JFactory::getConfig();
		$form = $formModel->getForm();
		$table = $formModel->getTable();
		if ($params->get('print'))
		{
			$status = "status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=400,height=350,directories=no,location=no";
			$url = COM_FABRIK_LIVESITE."index.php?option=com_fabrik&tmpl=component&view=details&formid=". $form->id . "&listid=" . $table->id . "&rowid=" . $rowid.'&iframe=1&print=1';
			// $$$ hugh - @TODO - FIXME - if they were using rowid=-1, we don't need this, as rowid has already been transmogrified
			// to the correct (PK based) rowid.  but how to tell if original rowid was -1???
			if (JRequest::getVar('usekey') !== null)
			{
				$url .= "&usekey=" . JRequest::getVar('usekey');
			}
			$link = JRoute::_($url);
			$link = str_replace('&', '&amp;', $link); // $$$ rob for some reason JRoute wasnt doing this ???
			if ($params->get('icons', true))
			{
				$image = JHtml::_('image','system/printButton.png', JText::_('COM_FABRIK_PRINT'), NULL, true);
			}
			else
			{
				$image = '&nbsp;' . JText::_('JGLOBAL_PRINT');
			}
			if ($params->get('popup', 1))
			{
				$ahref = '<a class=\"printlink\" href="javascript:void(0)" onclick="javascript:window.print(); return false" title="' . JText::_('COM_FABRIK_PRINT') . '">';
			}
			else
			{
				$ahref = "<a href=\"#\" class=\"printlink\" onclick=\"window.open('$link','win2','$status;');return false;\"  title=\"" .  JText::_('COM_FABRIK_PRINT') . "\">";
			}
			$return = $ahref .
			$image .
			"</a>";
			return $return;
		}
	}

	/**
	 * Writes Email icon
	 * @param object form
	 * @param object parameters
	 * @return string email icon/link html
	 */

	function emailIcon($formModel, $params)
	{
		$app = JFactory::getApplication();
		$config	= JFactory::getConfig();
		$popup = $params->get('popup', 1);
		if ($params->get('email') && !$popup)
		{
			$status = "status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=400,height=250,directories=no,location=no";
			if ($app->isAdmin())
			{
				$url = 'index.php?option=com_fabrik&task=emailform.display&tmpl=component&formid=' . $formModel->get('id') . '&rowid=' . $formModel->rowId;
			 }
			 else
			 {
				$url = 'index.php?option=com_fabrik&view=emailform&tmpl=component&formid=' . $formModel->get('id') . '&rowid=' . $formModel->rowId;
			}

			if (JRequest::getVar('usekey') !== null)
			{
				$url .= "&usekey=" . JRequest::getVar('usekey');
			}
			$url .= '&referrer='.urlencode(JFactory::getURI()->toString());

			//$link = JRoute::_($url); //cant jroute as reffer values overrides url task/view!
			$link = $url;

			if ($params->get('icons', true))
			{
				$image = JHtml::_('image','system/emailButton.png', JText::_('JGLOBAL_EMAIL'), NULL, true);
			}
			else
			{
				$image = '&nbsp;'. JText::_('JGLOBAL_EMAIL');
			}
			return "<a href=\"#\" onclick=\"window.open('$link','win2','$status;');return false;\"  title=\"" .  JText::_('JGLOBAL_EMAIL') . "\">
			$image
			</a>\n";
		}
	}

	/**
	 * get a list of condition options - used in advanced search
	 * @param string table ref
	 * @param string selected value
	 * @return string html select list
	 */

	function conditonList($listid, $sel = '')
	{
		$conditions = array();
		$conditions[] = JHTML::_('select.option', 'AND', JText::_('COM_FABRIK_AND'));
		$conditions[] = JHTML::_('select.option', 'OR', JText::_('COM_FABRIK_OR'));
		return JHTML::_('select.genericlist', $conditions, 'fabrik___filter[list_' . $listid . '][join][]', 'class="inputbox" size="1" ', 'value', 'text', $sel);
	}


	function tableList($sel = '')
	{
		$db = FabrikWorker::getDbo(true);
		$query = $db->getQuery(true);
		$query->select('id, label')->from('#__{package}_lists')->where('published = 1');
		$db->setQuery($query);
		$rows = $db->loadObjectList();
		if ($db->getErrorNum())
		{
			JError::raiseError(500, $db->getErrorMsg());
		}
		return JHTML::_('select.genericlist', $rows, 'fabrik__swaptable', 'class="inputbox" size="1" ', 'id', 'label', $sel);
	}

	/**
	 * load the css and js files once only (using calendar-eightsix)
	 * @deprecated - behavior.calendar is loaded in framework();
	 */

	function loadCalendar()
	{
	}

	/**
	 * @deprecated
	 * Internal method to translate the JavaScript Calendar
	 *
	 * @return	string	JavaScript that translates the object
	 * @since	1.5
	 */
	function _calendartranslation()
	{
	}

	/**
	 * fabrik script to load in a style sheet
	 * takes into account if you are viewing the page in raw format
	 * if so sends js code back to webpage to inject css file into document head
	 * If not raw format then apply standard J stylesheet
	 * @param $filename
	 * @param $path
	 * @param $attribs
	 * @return null
	 */

	public static function stylesheet($file, $attribs = array())
	{
		if ((JRequest::getVar('format') == 'raw' || JRequest::getVar('tmpl') == 'component') && JRequest::getVar('print') != 1)
		{
			$attribs = json_encode(JArrayHelper::toObject($attribs));
			// $$$rob TEST!!!! - this may mess up stuff
			//send an inline script back which will inject the css file into the doc head
			// note your ajax call must have 'evalScripts':true set in its properties
			if (!in_array($file, self::$ajaxCssFiles))
			{
				// $$$ rob added COM_FABRIK_LIVESITE to make full path name other wise style sheets gave 404 error
				// when loading from site with sef urls.

				echo "<script type=\"text/javascript\">var v = new Asset.css('{$file}', {});</script>\n";
				self::$ajaxCssFiles[] = $file;
			}
		}
		else
		{
			// $$$ rob 27/04/2011 changed from JHTML::styleSheet as that doesn't work loading
			// php style sheets with querystrings in them
			$document = JFactory::getDocument();
			$document->addStylesheet($file);
		}
	}

	/**
	 * check for a custom css file and include it if it exists
	 * @param string $path NOT including JPATH_SITE (so relative too root dir
	 * @return false
	 */

	public static function stylesheetFromPath($path)
	{
		if (JFile::exists(JPATH_SITE . '/' . $path))
		{
			FabrikHelperHTML::stylesheet($path);
		}
	}

	/**
	 * Generates an HTML radio list
	 * @param array An array of objects
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @param string The name of the object variable for the option value
	 * @param string The name of the object variable for the option text
	 * @param int number of options to show per row @since 2.0.5
	 * @returns string HTML for the select list
	 */

	function radioList(&$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $options_per_row = 0)
	{
		return FabrikHelperHTML::aList('radio', $arr, $tag_name, $tag_attribs, $selected, $key, $text, $options_per_row);
	}

	/**
	 * Generates an HTML radio OR checkbox list
	 * @param string type - radio or checkbox
	 * @param array An array of objects
	 * @param string The value of the HTML name attribute
	 * @param string Additional HTML attributes for the <select> tag
	 * @param mixed The key that is selected
	 * @param string The name of the object variable for the option value
	 * @param string The name of the object variable for the option text
	 * @param int number of options to show per row @since 2.0.5
	 * @param bool is the list editable or not @since 2.1.1
	 * @returns string HTML for the select list
	 */

	public function aList($type, &$arr, $tag_name, $tag_attribs, $selected=null, $key='value', $text='text', $options_per_row = 0, $editable=true)
	{
		reset($arr);
		if ($options_per_row > 0)
		{
			$percentageWidth = floor(floatval(100) / $options_per_row) - 2;
			$div = "<div class=\"fabrik_subelement\" style=\"float:left;width:" . $percentageWidth . "%\">\n";
		}
		else
		{
			$div = '<div class="fabrik_subelement">';
		}
		$html = "";
		if ($editable)
		{
			$selectText = $type == 'checkbox' ? " checked=\"checked\"" : " selected=\"selected\"";
		}
		else
		{
			$selectText = '';
		}
		for ($i = 0, $n = count($arr); $i < $n; $i++)
		{
			$k = $arr[$i]->$key;
			$t = $arr[$i]->$text;
			$id = isset($arr[$i]->id) ? @$arr[$i]->id : null;

			$extra = '';
			$extra .= $id ? " id=\"" . $arr[$i]->id . "\"" : '';
			$found = false;
			if (is_array($selected))
			{
				foreach ($selected as $obj)
				{
					if (is_object($obj))
					{
						$k2 = $obj->$key;
						if ($k == $k2)
						{
							$found = true;
							$extra .= $selected;
							break;
						}
					}
					else
					{
						if ($k == $obj)
						{
							//checkbox from db join
							$extra .= $selectText;
							$found = true;
							break;
						}
					}
				}
			}
			else
			{
				$extra .= $k == $selected ? " checked=\"checked\"" : '';
			}
			$html .= $div;

			if ($editable)
			{
				$html .= '<label>';
				$html .= '<input type="' . $type . '" value="' . $k . '" name="' . $tag_name . '" class="fabrikinput" ' . $extra. '/>';
			}
			if ($editable || $found)
			{
				$html .= '<span>' . $t . '</span>';
			}
			if ($editable)
			{
				$html .= '</label>';
			}
			$html .= '</div>';
		}
		$html .= "\n";
		return $html;
	}

	/**
	 *
	 */

	function PdfIcon($model, $params, $rowId = 0, $attribs = array())
	{
		$app = JFactory::getApplication();
		$url = '';
		$text	= '';
		// $$$ rob changed from looks at the view as if rendering the table as a module when rendering a form
		// view was form, but $Model is a table
		$modelClass = get_class($model);
		$task = JRequest::getVar('task');
		if ($task == 'form' || $modelClass == 'FabrikModelForm')
		{
			$form = $model->getForm();
			$table = $model->getTable();
			$user = JFactory::getUser();
			$url = COM_FABRIK_LIVESITE."index.php?option=com_fabrik&amp;view=details&amp;format=pdf&amp;formid=". $form->id . "&amp;listid=" . $table->id . "&amp;rowid=" . $rowId;
		}
		else
		{
			$table = $model->getTable();
			$url = COM_FABRIK_LIVESITE."index.php?option=com_fabrik&amp;view=list&amp;format=pdf&amp;listid=" . $table->id;
		}
		if (JRequest::getVar('usekey') !== null)
		{
			$url .= "&amp;usekey=" . JRequest::getVar('usekey');
		}
		$status = 'status=no,toolbar=no,scrollbars=yes,titlebar=no,menubar=no,resizable=yes,width=640,height=480,directories=no,location=no';

		// checks template image directory for image, if non found default are loaded
		if ($app->isAdmin())
		{
			$text = "<img src=\"" . COM_FABRIK_LIVESITE . "images/pdf_button.png\" alt=\"" . JText::_('PDF') . "\" />\n";
		}
		else
		{
			$text = JHTML::_('image.site', 'pdf_button.png', '/images/', NULL, NULL, JText::_('PDF'));
		}
		$attribs['title'] = JText::_('PDF');
		$attribs['onclick'] = "window.open(this.href,'win2','".$status."'); return false;";
		$attribs['rel'] = 'nofollow';
		$url = JRoute::_($url);
		$output = JHTML::_('link', $url, $text, $attribs) . "\n";
		return $output;
	}

	/**
	 * Keep session alive, for example, while editing or creating an article.
	 */

	function keepalive()
	{
		//test since 2.0b3 dont do anything if loading from Fabrik win
		if (FabrikHelperHTML::inAjaxLoadedPage())
		{
			return;
		}
		JHtml::_('behavior.keepalive');
	}

	public function mcl()
	{
		if (!self::$mcl)
		{
			$src = array('media/com_fabrik/js/lib/mcl/CANVAS.js', 'media/com_fabrik/js/lib/mcl/CanvasItem.js',
						'media/com_fabrik/js/lib/mcl/Cmorph.js', 'media/com_fabrik/js/lib/mcl/Layer.js',
						'media/com_fabrik/js/lib/mcl/LayerHash.js', 'media/com_fabrik/js/lib/mcl/Thread.js',
						'media/com_fabrik/js/lib/canvas-extra.js'
			);
			FabrikHelperHTML::script($src);
			self::$mcl = true;
		}
	}

	/**
	 * load Fabrik's framework (js and base css file)
	 */

	public static function framework()
	{
		if (!self::$framework)
		{
			$src = array();
			if (FabrikHelperHTML::inAjaxLoadedPage())
			{
				// 17/10/2011 (firefox) retesting loading this in ajax page as without it Class is not available? so form class doesnt load
				JHtml::_('behavior.framework', true);
				//$$$ rob 06/02/2012 recall ant so that Color.detach is available (needed for opening a window from within a window)
				JHtml::_('script', 'media/com_fabrik/js/lib/art.js');
				JHtml::_('script', 'media/com_fabrik/js/lib/Event.mock.js');
			}

			if (!FabrikHelperHTML::inAjaxLoadedPage())
			{

				//required so that any ajax loaded form can make use of it later on (otherwise stops js from working)
				//only load in main/first window - otherwise reloading it causes js errors related to calendar translations
				JHTML::_('behavior.calendar');

				//loading framework, if in ajax loaded page:
				// * makes document.body not found for gmap element when
				// * removes previously added window.events (17/10/2011 we're now using Fabrik.events - so this may no longer be an issue)
				JHtml::_('behavior.framework', true);

				JDEBUG ? JHtml::_('script', 'media/com_fabrik/js/lib/head/head.js'): JHtml::_('script', 'media/com_fabrik/js/lib/head/head.min.js');

				$navigator = JBrowser::getInstance();
				if ($navigator->getBrowser() == 'msie') {
					$src[] = 'media/com_fabrik/js/lib/flexiejs/flexie.js';
				}
				$src[] = 'media/com_fabrik/js/mootools-ext.js';
				$src[] = 'media/com_fabrik/js/lib/art.js';
				$src[] = 'media/com_fabrik/js/icons.js';
				$src[] = 'media/com_fabrik/js/icongen.js';
				$src[] = 'media/com_fabrik/js/fabrik.js';
				//$src[] = 'media/com_fabrik/js/lib/tips/floatingtips.js';
				$src[] = 'media/com_fabrik/js/tips.js';
				$src[] = 'media/com_fabrik/js/window.js';
				$src[] = 'media/com_fabrik/js/lib/Event.mock.js';

				FabrikHelperHTML::styleSheet(COM_FABRIK_LIVESITE.'media/com_fabrik/css/fabrik.css');
				// $$$ hugh - setting liveSite needs to use addScriptDecleration, so it loads earlier, otherwise
				// in some browsers it's not available when other things (like map biz) are loading
				FabrikHelperHTML::addScriptDeclaration("head.ready(function() { Fabrik.liveSite = '".COM_FABRIK_LIVESITE."';});");
				
				$script = array();
				$script[] = "Fabrik.fireEvent('fabrik.framework.loaded');";
				FabrikHelperHTML::script($src, implode("\n", $script));
				
				$tipOpts = FabrikHelperHTML::tipOpts();
				FabrikHelperHTML::addScriptDeclaration("head.ready(function () {
	Fabrik.tips = new FloatingTips('.fabrikTip', " . json_encode($tipOpts) . ");
	Fabrik.addEvent('fabrik.list.updaterows', function () {
		//reattach new tips after list redraw,
		Fabrik.tips.attach('.fabrikTip');
	});
	Fabrik.addEvent('fabrik.plugin.inlineedit.editing', function () {
		Fabrik.tips.hideAll();
	});
});
				");
			}
			self::$framework = true;
		}
	}

	/**
	 * @deprecated use ::framework instead
	 */

	function mootools()
	{
		FabrikHelperHTML::framework();
	}

	public static function tipOpts()
	{
		$usersConfig = JComponentHelper::getParams('com_fabrik');
		$opts = new stdClass();
		$opts->tipfx = 'Fx.Transitions.' . $usersConfig->get('tipfx', 'Linear');
		if ($usersConfig->get('tipfx', 'Linear') !== 'Linear')
		{
			$opts->tipfx .= '.' . $usersConfig->get('tipfx_ease', 'easeIn');
		}
		$opts->duration = $usersConfig->get('tipfx_duration', '500');
		$opts->distance = (int) $usersConfig->get('tipfx_distance', '20');
		$opts->fadein = (bool) $usersConfig->get('tipfx_fadein', false);
		return $opts;
	}
	
	/**
	 * @param string js $script
	 * @return null
	 */

	public static function addScriptDeclaration($script)
	{
		if (JRequest::getCmd('format') == 'raw')
		{
			echo "<script type=\"text/javascript\">" . $script . "</script>";
		}
		else
		{
			JFactory::getDocument()->addScriptDeclaration($script);
		}
	}

	public function addStyleDeclaration($style)
	{
		if (JRequest::getCmd('format') == 'raw')
		{
			echo "<style type=\"text/css\">" . $style . "</script>";
		}
		else
		{
			JFactory::getDocument()->addStyleDeclaration($style);
		}
	}

	/**
	 * sometimes you want to load a page in an iframe and want to use tmpl=component - in this case
	 * append iframe=1 to the url to ensure that we dont try to add the scripts via FBAsset()
	 */

	public static function inAjaxLoadedPage()
	{
		//are we in fabrik or a content view, if not return false (things like com_config need to load in mootools)
		$option = JRequest::getCmd('option');
		if ($option !== 'com_fabrik' && $option !== 'com_content')
		{
			return false;
		}
		if (class_exists('JSite'))
		{
			$menus	= JSite::getMenu();
			$menu	= $menus->getActive();
			//popup menu item so not in ajax loaded page even if tmpl=component
			// $$$ hugh - nope, browserNav of '1' is not a popup, just a new tab, see ...
			// http://fabrikar.com/forums/showthread.php?p=111771#post111771
			// if (is_object($menu) && ($menu->browserNav == 2 || $menu->browserNav == 1)) {
			if (is_object($menu) && ($menu->browserNav == 2))
			{
				return false;
			}
		}
		return JRequest::getVar('format') == 'raw' || (JRequest::getVar('tmpl') == 'component' && JRequest::getInt('iframe') != 1);
	}

	/**
	 * wrapper for JHTML::Script()
	 * @param mixed, string or array of files to load
	 * @param string optional js to run if format=raw (as we first load the $file via Asset.Javascript()
	 */

	public static function script($file, $onLoad = '')
	{
		if (empty($file))
		{
			return;
		}

		$config = JFactory::getConfig();
		$debug = $config->get('debug');
		//$uncompressed	= $debug ? '-uncompressed' : '';
		$ext = $debug || JRequest::getInt('fabrikdebug', 0) === 1 ? '.js' : '-min.js';

		$file = (array)$file;
		$src = array();
		foreach ($file as $f)
		{
			if (!(stristr($f, 'http://') || stristr($f, 'https://')))
			{
				if (!JFile::exists(COM_FABRIK_BASE . '/' . $f))
				{
					continue;
				}
			}
			if (stristr($f, 'http://') || stristr($f, 'https://'))
			{
				$f = $f;
			}
			else
			{
				$compressedFile = str_replace('.js', $ext, $f);
				if (JFile::exists($compressedFile))
				{
					$f = $compressedFile;
				}
				$f = COM_FABRIK_LIVESITE.$f;
			}
			if (JRequest::getCmd('format') == 'raw')
			{
				$opts = trim($onLoad) !== '' ? '\'onLoad\':function(){'.$onLoad.'}' : '';
				echo '<script type="text/javascript">Asset.javascript(\''.$f.'\', {'.$opts.'});</script>';
			}
			else
			{
				$src[] = "'" . $f . "'";
			}
		}
		if ($onLoad !== '' && JRequest::getCmd('format') != 'raw' && !empty($src))
		{
			//$onLoad = "head.ready(function() {\n " . $onLoad . "\n});\n";
			//FabrikHelperHTML::addScriptDeclaration($onLoad);
			if (FabrikHelperHTML::inAjaxLoadedPage())
			{
				$onLoad = "(function() {\n " . $onLoad . "\n //end load func \n})";
			}
			else
			{
				$onLoad = "(function() { head.ready(function() {\n" . $onLoad . "\n})\n})";
			}
			$src[] = $onLoad;
		}
		if (!empty($src))
		{
			JFactory::getDocument()->addScriptDeclaration('head.js('.implode(",\n", array_unique($src)).');'."\n");
		}
	}

	function slimbox()
	{
		if (!self::$modal)
		{
			$fbConfig = JComponentHelper::getParams('com_fabrik');
			if ($fbConfig->get('include_lightbox_js', 1) == 0)
			{
				return;
			}
			if ($fbConfig->get('use_mediabox', false))
			{
				$folder = 'components/com_fabrik/libs/mediabox/';
				JHTML::stylesheet($folder . '/css/mediabox.css');
				FabrikHelperHTML::script($folder.'mediabox.js');
			}
			else
			{
				JHTML::stylesheet('components/com_fabrik/libs/slimbox1.64/css/slimbox.css');
				FabrikHelperHTML::script('components/com_fabrik/libs/slimbox1.64/js/slimbox.js');
			}
			self::$modal = true;
		}
	}

	/**
	 * @param $selector string class name of tips
	 * @param $params array paramters
	 * @param $selectorPrefix limit the tips selection to those contained within an id
	 * @return unknown_type
	 */

	function tips($selector='.hasTip', $params = array(), $selectorPrefix = 'document')
	{

		$sig = md5(serialize(array($selector,$params)));
		if (isset(self::$tips[$sig]) && (self::$tips[$sig]))
		{
			return;
		}

		// Setup options object
		$opt['maxTitleChars']	= (isset($params['maxTitleChars']) && ($params['maxTitleChars'])) ? (int)$params['maxTitleChars'] : 50;
		$opt['offsets']			= (isset($params['offsets'])) ? (int)$params['offsets'] : null;
		$opt['showDelay']		= (isset($params['showDelay'])) ? (int)$params['showDelay'] : null;
		$opt['hideDelay']		= (isset($params['hideDelay'])) ? (int)$params['hideDelay'] : null;
		$opt['className']		= (isset($params['className'])) ? $params['className'] : null;
		$opt['fixed']			= (isset($params['fixed']) && ($params['fixed'])) ? '\\true' : '\\false';
		$opt['onShow']			= (isset($params['onShow'])) ? '\\'.$params['onShow'] : null;
		$opt['onHide']			= (isset($params['onHide'])) ? '\\'.$params['onHide'] : null;

		$options = json_encode($opt);

		// Attach tooltips to document
		//force the zindex to 9999 so that it appears above the popup window.
		//$event = (JRequest::getVar('tmpl') == 'component') ? 'load' : 'domready';
		$tooltipInit = 'head.ready(function() {if(typeOf('.$selectorPrefix.') !== \'null\' && '.$selectorPrefix.'.getElements(\''.$selector.'\').length !== 0) {window.JTooltips = new Tips('.$selectorPrefix.'.getElements(\''.$selector.'\'), '.$options.');$$(".tool-tip").setStyle("z-index", 999999);}});';
		//FabrikHelperHTML::addScriptDeclaration($tooltipInit);

		self::$tips[$sig] = true;
		return;
	}

	/**
	 * add a debug out put section
	 * @param mixed string/object $content
	 * @param string $title
	 */

	function debug($content, $title = 'output:')
	{
		$config = JComponentHelper::getParams('com_fabrik');
		if ($config->get('use_fabrikdebug') == 0)
		{
			return;
		}
		if (JRequest::getBool('fabrikdebug', 0, 'request') != 1)
		{
			return;
		}
		if (JRequest::getVar('format') == 'raw')
		{
			return;
		}
		echo "<div class=\"fabrikDebugOutputTitle\">$title</div>";
		echo "<div class=\"fabrikDebugOutput fabrikDebugHidden\">";
		if (is_object($content) || is_array($content))
		{
			echo "<pre>" . htmlspecialchars(print_r($content, true)) . "</pre>";
		}
		else
		{
			echo htmlspecialchars($content);
		}
		echo "</div>";

		if (!isset(self::$debug))
		{
			self::$debug = true;
			$document = JFactory::getDocument();
			$style = ".fabrikDebugOutputTitle{padding:5px;background:#efefef;color:#333;border:1px solid #999;cursor:pointer}";
			$style .= ".fabrikDebugOutput{padding:5px;background:#efefef;color:#999;}";
			$style .= ".fabrikDebugOutput pre{padding:5px;background:#efefef;color:#999;}";
			$style .= ".fabrikDebugHidden{display:none}";
			FabrikHelperHTML::addStyleDeclaration($style);
			$script = "head.ready(function() {
			$$('.fabrikDebugOutputTitle').each(function(title) {
				title.addEvent('click', function(e) {
					title.getNext().toggleClass('fabrikDebugHidden');
				});
			});
			})";
			FabrikHelperHTML::addScriptDeclaration($script);
		}
	}

	/**
	 * create html for ajax folder browser (used by fileupload and image elements)
	 * @param array folders
	 * @param string start path
	 * @return string html snippet
	 */

	function folderAjaxSelect($folders, $path = '')
	{
		$str = array();
		$str[] = '<a href="#" class="toggle" title="' . JText::_('COM_FABRIK_BROWSE_FOLDERS') . '">';
		//$str[] = FabrikHelperHTML::image('control_play.png', 'form', @$this->tmpl, array('alt' => JText::_('COM_FABRIK_BROWSE_FOLDERS')));
		$str[] = FabrikHelperHTML::image('orderneutral.png', 'form', @$this->tmpl, array('alt' => JText::_('COM_FABRIK_BROWSE_FOLDERS')));
		$str[] = '</a>';
		$str[] = '<div class="folderselect-container">';
		$str[] = '<span class="breadcrumbs"><a href="#">'.JText::_('HOME').'</a><span> / </span>';
		$i = 1;
		$path = explode("/", $path);
		foreach ($path as $p)
		{
			$str[] = '<a href="#" class="crumb'. $i .'">' . $p . '</a><span> / </span>';
			$i ++;
		}
		$str[] = '</span>';
		$str[] = '<ul class="folderselect">';
		settype($folders, 'array');
		foreach ($folders as $folder)
		{
			if (trim($folder) != '')
			{
				$str[] = '<li class="fileupload_folder"><a href="#">'.$folder.'</a></li>';
			}
		}
		//for html validation
		if (empty($folder))
		{
			$str[] =  '<li></li>';
		}
		$str[] = '</ul></div>';
		return implode("\n", $str);
	}

	/**
	 * Add autocomplete JS code to head
	 * @param string $htmlid of element to turn into autocomplete
	 * @param int $elementid
	 * @param string $plugin
	 * @param array $opts (currently only takes 'onSelection')
	 */

	public function autoComplete($htmlid, $elementid, $plugin = 'field', $opts = array())
	{
		FabrikHelperHTML::autoCompleteScript();
		$json = FabrikHelperHTML::autoCompletOptions($htmlid, $elementid, $plugin, $opts);
		$str = json_encode($json);
		FabrikHelperHTML::addScriptDeclaration(
		"head.ready(function() { new FbAutocomplete('$htmlid', $str); });"
		);
	}

	/**
	 * Gets auto complete js options (needed separate from autoComplete as db js class needs these values for repeat group duplication)
	 * @param string $htmlid of element to turn into autocomplete
	 * @param int $elementid
	 * @param string $plugin
	 * @param array $opts (currently only takes 'onSelection')
	 * @return array autocomplete options (needed for elements so when duplicated we can create a new FabAutocomplete object
	 */

	public function autoCompletOptions($htmlid, $elementid, $plugin = 'field', $opts = array())
	{
		$json = new stdClass();
		$json->url = COM_FABRIK_LIVESITE . 'index.php?option=com_fabrik&format=raw&view=plugin&task=pluginAjax&g=element&element_id=' . $elementid . '&plugin=' . $plugin . '&method=autocomplete_options';
		$c = JArrayHelper::getValue($opts, 'onSelection');
		if ($c != '')
		{
			$json->onSelections = $c;
		}
		$json->container = JArrayHelper::getValue($opts, 'container', 'fabrikElementContainer');
		return $json;
	}

	/**
	 *Load the autocomplete script once
	 */

	public function autoCompleteScript() {
		if (!isset(self::$autocomplete))
		{
			self::$autocomplete = true;
			FabrikHelperHTML::script('media/com_fabrik/js/autocomplete.js');
		}
	}

	public function facebookGraphAPI($appid, $locale = 'en_US', $meta = array())
	{
		if (!isset(self::$facebookgraphapi))
		{
			self::$facebookgraphapi = true;
			return "<div id=\"fb-root\"></div>
<script>
  window.fbAsyncInit = function() {
    FB.init({appId: '$appid', status: true, cookie: true,
             xfbml: true});
  };
  (function() {
    var e = document.createElement('script'); e.async = true;
    e.src = document.location.protocol +
      '//connect.facebook.net/$locale/all.js';
    document.getElementById('fb-root').appendChild(e);
  }());
</script>";
		}
		$document = JFactory::getDocument();
		$data = array('custom'=>array());
		$typeFound = false;
		foreach ($meta as $k => $v)
		{
			$v = strip_tags($v);
			//og:type required
			if ($k == 'og:type') {
				$typeFound = true;
				if ($v == '')
				{
					$v = 'article';
				}
			}
			$data['custom'][] = "<meta property=\"$k\" content=\"$v\"/>";

		}
		if (!$typeFound)
		{
			$data['custom'][] = "<meta property=\"og:type\" content=\"article\"/>";
		}
		$document->setHeadData($data);
	}

	/**
	 * add path for image() function
	 * @since 3.0
	 * @param string $path to add to list of folders to search
	 * @param string $type of path set to load (currently only image is used)
	 * @param string $view are we looking at loading form or list images?
	 * @param bool $highPriority should the added $path take precedence over previously added paths (default true)
	 */

	public function addPath($path = '', $type = 'image', $view = 'form', $highPriority = true)
	{
		if (!array_key_exists($type, self::$helperpaths))
		{
			self::$helperpaths[$type] = array();
			$app = JFactory::getApplication();
			$template = $app->getTemplate();
			switch ($type)
			{
				case 'image':
					if ($app->isAdmin())
					{
						self::$helperpaths[$type][] = JPATH_SITE . '/' . "administrator/templates/$template/images/";
					}
					self::$helperpaths[$type][] = COM_FABRIK_BASE."templates/$template/html/com_fabrik/$view/%s/images/";
					self::$helperpaths[$type][] = COM_FABRIK_BASE."templates/$template/html/com_fabrik/$view/images/";
					self::$helperpaths[$type][] = COM_FABRIK_BASE."templates/$template/html/com_fabrik/images/";
					self::$helperpaths[$type][] = COM_FABRIK_FRONTEND."/views/$view/tmpl/%s/images/";
					self::$helperpaths[$type][] = COM_FABRIK_BASE."media/com_fabrik/images/";
					self::$helperpaths[$type][] = COM_FABRIK_BASE."images/";
					self::$helperpaths[$type][] = COM_FABRIK_BASE."images/stories/";
					break;
			}
		}
		if (!array_key_exists($path, self::$helperpaths[$type]) && $path !== '')
		{
			$highPriority ? array_unshift(self::$helperpaths[$type], $path) : self::$helperpaths[$type][] =  $path;
		}
		return self::$helperpaths[$type];
	}

	/**
	 * Search various folder locations for an image
	 * @param string file name
	 * @param string type e.g. form/list/element
	 * @param string template folder name
	 * @return string full path name if found, original filename if not found
	 */

	public function getImagePath($file, $type = 'form', $tmpl = '')
	{
		$file = ltrim($file, DS);
		$paths = FabrikHelperHTML::addPath('', 'image', $type, true);
		$src = '';
		foreach ($paths as $path)
		{
			$path = sprintf($path, $tmpl);
			$src = $path . $file;
			if (JFile::exists($src))
			{
				return $src;
			}
		}
		return '';
	}

	/**
	 * Search various folder locations for a template image
	 * @since 3.0
	 * @param string file name
	 * @param string type e.g. form/list/element
	 * @param string template folder name
	 * @param array assoc list of properties or string (if you just want to set the image alt tag)
	 * @param bool src only (default false)
	 */

	public function image($file, $type = 'form', $tmpl = '', $properties = array(), $srcOnly = false)
	{
		if (is_string($properties))
		{
			$properties = array('alt' => $properties);
		}

		$src = FabrikHelperHTML::getImagePath($file, $type, $tmpl);
		$src = str_replace(COM_FABRIK_BASE, COM_FABRIK_LIVESITE, $src);
		$src = str_replace("\\", "/", $src);

		if ($srcOnly)
		{
			return $src;
		}
		$bits = array();
		foreach ($properties as $key => $val)
		{
			$bits[$key] = $val;
		}
		$p = '';
		foreach ($bits as $key => $val)
		{
			$val = str_replace('"', "'", $val);
			$p .= $key . '="' . $val . '" ';
		}
		return $src == '' ? '' : '<img src="' . $src . '" '. $p. '/>';
	}

	/**
	 * make a grid of items
	 * @param array $values
	 * @param array $labels
	 * @param array $selected
	 * @param string $name
	 * @param string $type *checkbox/radio etc
	 * @param bool $elementBeforeLabel
	 * @param int $optionsPerRow
	 */

	public function grid($values, $labels, $selected, $name, $type="checkbox", $elementBeforeLabel = true, $optionsPerRow = 4)
	{
		$items = array();
		for ($i = 0; $i < count($values); $i ++)
		{
			$item = array();
			$thisname = $type === 'checkbox' ? FabrikString::rtrimword($name, '[]') . "[$i]" : $name;
			$label = '<span>'.$labels[$i].'</span>';
			$value = htmlspecialchars($values[$i], ENT_QUOTES); //for values like '1"'
			$chx = '<input type="'.$type.'" class="fabrikinput '.$type.'" name="'.$thisname.'" value="'.$value.'" ';
			$chx .= in_array($values[$i], $selected) ? ' checked="checked" />' : ' />';
			$item[] = '<label class="fabrikgrid_'.$value.'">';
			$item[] = $elementBeforeLabel  == '1' ? $chx.$label : $label.$chx;
			$item[] = '</label>';
			$items[] = implode("\n", $item);
		}
		$grid = array();

		$optionsPerRow = empty($optionsPerRow) ? 4 : $optionsPerRow;
		$w = floor(100 / $optionsPerRow);
		$widthConstraint = '';
		$grid[] = '<ul>';
		foreach ($items as $i => $s)
		{
			$clear = ($i % $optionsPerRow == 0) ? 'clear:left;' : '';
			$grid[] = '<li style="'.$clear.'float:left;width:'.$w.'%;padding:0;margin:0;">'.$s.'</li>';
		}
		$grid[] = '</ul>';
		return $grid;
	}

}
?>
