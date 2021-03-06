<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), 0);
$required = $form->is_field_required($field);
$element_id = 'element_id_'.uniqid();

?>

<div class="nj-form-field">
	<label>
		<div class="nj-form-label">
			<?php
				echo html::specialchars($field->get_pretty_name());
			?>
		</div>
		<input <?php
			echo ($required) ? 'required' : '';
		?> type="number" id="<?php
			echo $element_id;
		?>" pattern="^\d+$" class="nj-form-option" name="<?php
			echo html::specialchars($field->get_name());
		?>" value="<?php
			echo html::specialchars($default);
		?>" />
	</label>
</div>
