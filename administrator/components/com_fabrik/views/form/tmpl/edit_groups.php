<fieldset class="adminform">
			<ul class="adminformlist">
				<?php foreach ($this->form->getFieldset('groups') as $field) :?>
				<li>
					<?php echo $field->label; ?><?php echo $field->input; ?>
				</li>
				<?php endforeach; ?>
			</ul>
		</fieldset>