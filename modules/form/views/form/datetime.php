<?php defined('SYSPATH') OR die('No direct access allowed.');
/* @var $form Form_Model */
/* @var $field Form_Field_Text_Model */

$default = $form->get_value($field->get_name(), "");
$element_id = 'element_id_'.uniqid();

?>

<div class="nj-form-field">
	<label>
		<div class="nj-form-label">
			<?php echo html::specialchars($field->get_pretty_name()); ?>
		</div>
		<span class="icon-16 x16-schedule"></span> <input class="flatpicker" type="text" data-min-date="today" data-enabled-time="true" data-time_24hr="true" id="<?php echo $element_id; ?>" name="<?php echo html::specialchars($field->get_name()); ?>" value="<?php echo html::specialchars($default); ?>">
	</label>
</div>
