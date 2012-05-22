<?php
/*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die;

JHtml::addIncludePath(JPATH_COMPONENT.DS.'helpers'.DS.'html');
JHTML::stylesheet('administrator/components/com_fabrik/views/fabrikadmin.css');
JHtml::_('behavior.tooltip');
JHtml::_('behavior.framework', true);
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');

FabrikHelperHTML::script('media/com_fabrik/js/mootools-ext.js');

FabrikHelperHTML::script('administrator/components/com_fabrik/views/namespace.js');
FabrikHelperHTML::script('administrator/components/com_fabrik/views/pluginmanager.js');
FabrikHelperHTML::script('administrator/components/com_fabrik/views/element/tmpl/adminelement.js');

JFactory::getDocument()->addScriptDeclaration($this->js);

JText::script('COM_FABRIK_SUBOPTS_VALUES_ERROR');
?>

<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
<?php //?>
<?php if ($this->item->parent_id != 0) {
	?>
	<div id="system-message">
	<dl>
		<dd class="notice">
		<ul>
			<li>
				<?php echo JText::_('COM_FABRIK_ELEMENT_PROPERTIES_LINKED_TO') ?>:
			</li>
			<li>
				<a href="#" id="swapToParent" class="element_<?php echo $this->parent->id ?>"><?php echo $this->parent->label ?></a>
			</li>
			<li>
				<label><input id="unlink" name="unlink" id="unlinkFromParent" type="checkbox"> <?php echo JText::_('COM_FABRIK_UNLINK') ?></label>
			</li>
		</ul>
		</dd>
	</dl>
	</div>
<?php }?>
<div id="elementFormTable">
	<div class="width-50 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FABRIK_DETAILS');?></legend>
			<input type="hidden" id="name_orig" name="name_orig" value="<?php echo $this->item->name; ?>" />
			<input type="hidden" id="plugin_orig" name="plugin_orig" value="<?php echo $this->item->plugin; ?>" />
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('css'). $this->form->getInput('css'); ?>
				</li>
				<?php foreach ($this->form->getFieldset('details') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
			<div class="clr"> </div>
		</fieldset>

		<div style="margin:10px">
			<?php echo JHtml::_('sliders.start','element-sliders-options', array('useCookie'=>1));
			echo JHtml::_('sliders.panel', JText::_('COM_FABRIK_OPTIONS'), 'options-details');
			echo "<div id=\"plugin-container\">$this->pluginFields</div>";
			echo JHtml::_('sliders.end'); ?>
		</div>
	</div>

	<div class="width-50 fltrt">
		<?php echo JHtml::_('tabs.start', 'element', array('useCookie'=>1));
			echo $this->loadTemplate('publishing');
			echo $this->loadTemplate('access');
			echo $this->loadTemplate('settings');
			echo $this->loadTemplate('validations');
			echo $this->loadTemplate('javascript');
		echo JHtml::_('tabs.end'); ?>
	</div>

	<input type="hidden" name="task" value="" />
	<input type="hidden" name="redirectto" value="" />
	<?php echo JHtml::_('form.token'); ?>
	</div>
</form>
