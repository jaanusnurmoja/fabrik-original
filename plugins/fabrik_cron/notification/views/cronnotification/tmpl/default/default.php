<?php if (count($this->rows) == 0) {
echo JText::_('YOU_ARE_NOT_SUBSCRIBED_TO_ANY_NOTIFICATIONS');
}else{?>
<form action="index.php" method="post" name="adminForm">
		<table class="adminlist">
			<thead>
			<tr>
				<th width="1%"> <input type="checkbox" name="toggle" value="" onclick="checkAll(<?php echo count($this->rows);?>);" /> </th>
				<th width="49%">
					<?php echo JText::_('NOTIFICATION')?>
				</th>
				<th width ="50%">
					<?php echo JText::_('NOTIFICATION_REASON')?>
				</th>
			</tr>
</thead>
<tbody>
<?php
$k = 0;
for ( $i = 0, $n = count($this->rows); $i < $n; $i ++ ) {
				$row = & $this->rows[$i];
	?>
	<tr class="<?php echo "row$k"; ?>">
		<td>
			<?php echo JHTML::_('grid.checkedout',   $row, $i);?>
		</td>
		<td><a href="<?php echo $row->url?>"><?php echo $row->title?></a></td>
		<td><?php echo $row->reason?></td>
	</tr>

	<?php
	$k = 1 - $k;
}
?>
</tbody>
</table>
<div class="readon-wrap1"><div class="readon1-l"></div><a class="readon-main"><span class="readon1-m"><span class="readon1-r">
<input type="submit" value="<?php echo JText::_('DELETE')?>" class="button"/></span></span></a></div>

<input type="hidden" name="option" value="com_fabrik" />
<input type="hidden" name="view" value="cron" />
<input type="hidden" name="controller" value="cron.cronnotification" />
<input type="hidden" name="task" value="delete" />
<input type="hidden" name="boxchecked" value="0" />
<?php echo JHTML::_('form.token'); ?>
</form>
<?php }?>