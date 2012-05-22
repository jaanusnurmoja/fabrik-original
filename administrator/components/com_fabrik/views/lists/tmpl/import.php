<?php /*
 * @package Joomla.Administrator
 * @subpackage Fabrik
 * @since		1.6
 * @copyright Copyright (C) 2005 Rob Clayburn. All rights reserved.
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL, see LICENSE.php
*/

// no direct access
defined('_JEXEC') or die;

JHtml::_('behavior.tooltip');
JHtml::_('behavior.formvalidation');

?>

<form action="<?php JRoute::_('index.php?option=com_fabrik'); ?>" method="post" name="adminForm" id="fabrik-form" class="form-validate">

	<?php
	$cid	= JRequest::getVar('cid', array(), 'post', 'array');
	foreach ($cid as $id) { ?>
		<input type="hidden" name="cid[]" value="<?php echo $id ;?>" />
	<?php } ?>

	<fieldset class="adminform">
		<ul class="adminformlist">
		<?php for ($i=0; $i < count($this->items); $i++) {?>
  		<li><?php echo $this->items[$i]?></li>
		<?php }?>
		</ul>

		<ul>
		<?php foreach ($this->form->getFieldset('details') as $field) :?>
			<li>
				<?php echo $field->label; ?><?php echo $field->input; ?>
			</li>
			<?php endforeach; ?>
		</ul>
	</fieldset>
	<input type="hidden" name="task" value="" />
  	<?php echo JHTML::_('form.token');
	echo JHTML::_('behavior.keepalive'); ?>
</form>