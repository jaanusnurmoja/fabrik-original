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
JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');
JHtml::_('behavior.keepalive');
$fbConfig = JComponentHelper::getParams('com_fabrik');
?>


<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="adminForm" class="form-validate">
	<div class="width-100 fltlft">
		<fieldset class="adminform">
			<legend><?php echo JText::_('COM_FABRIK_DETAILS');?></legend>
			<ul class="adminformlist">
				<li>
					<?php echo $this->form->getLabel('description') . $this->form->getInput('description'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('host'). $this->form->getInput('host'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('database'). $this->form->getInput('database'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('user'). $this->form->getInput('user'); ?>
				</li>

			<?php if ($this->item->host != ""){?>
				<li>
					<label><?php echo JText::_('COM_FABRIK_ENTER_PASSWORD_OR_LEAVE_AS_IS');  ?>
				</li>
			<?php } ?>

				<li>
					<?php echo $this->form->getLabel('password'). $this->form->getInput('password'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('passwordConf'). $this->form->getInput('passwordConf'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('published'). $this->form->getInput('published'); ?>
				</li>

				<li>
					<?php echo $this->form->getLabel('id'). $this->form->getInput('id'); ?>
				</li>
			</ul>
			<div class="clr"> </div>

		</fieldset>

	</div>
	<div class="clr"></div>

	<input type="hidden" name="task" value="" />
	<?php echo JHtml::_('form.token'); ?>
</form>
