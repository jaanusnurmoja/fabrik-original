<?php
// Check to ensure this file is included in Joomla!
defined('_JEXEC') or die();
?>
<form method="post" action="<?php echo $this->action?>" class="advancedSeach_<?php echo $this->listid?>">
	<a id="advanced-search-add" class="addbutton" href="#">
		<?php echo FabrikHelperHTML::image('add.png', 'list', $this->tmpl);?>
		<?php echo JText::_('COM_FABRIK_ADD')?>
	</a>
	<div id="advancedSearchContainer">
	<table id="advanced-search-table" class="fabrikList">
		<tbody>
			<?php foreach ($this->rows as $row) {?>
			<tr>
				<td><span><?php echo $row['join'];?></span></td>
				<td><?php echo $row['element'] . $row['type'] . $row['grouped'];?>
				</td>
				<td><?php echo $row['condition'];?></td>
				<td class='filtervalue'><?php echo $row['filter'];?></td>
				<td>
				<ul class="fabrik_action">
				<li>
					<a class="advanced-search-remove-row" href="#">
						<?php echo FabrikHelperHTML::image('del.png', 'list', $this->tmpl);?>
					</a>
					</li>
					</ul>
				</td>
			</tr>
			<?php }?>

		</tbody>
		<thead>
			<tr class="fabrik___heading title">
				<th></th>
				<th><?php echo JText::_('COM_FABRIK_ELEMENT')?></th>
				<th><?php echo JText::_('COM_FABRIK_CONDITION')?></th>
				<th><?php echo JText::_('COM_FABRIK_VALUE')?></th>
				<th><?php echo JText::_('COM_FABRIK_DELETE')?></th>
			</tr>
			</thead>
	</table>
	</div>
	<input type="submit"
		value="<?php echo JText::_('COM_FABRIK_APPLY')?>" class="button fabrikFilter" name="applyAdvFabrikFilter"
		id="advanced-search-apply" type="button">
	<input
		id="advancedFilterTable-clearall" value="<?php echo JText::_('COM_FABRIK_CLEAR')?>" class="button"
		type="button">
			<input type="hidden" name="option" value="com_fabrik" />
	<input type="hidden" name="view" value="<?php echo JRequest::getVar('nextview', 'list')?>" />
	<input type="hidden" name="listid" value="<?php echo $this->listid?>" />
	<input type="hidden" name="task" value="filter" />
	<input type="hidden" name="advanced-search" value="1" />
<?php //echo $this->fields?>

</form>
