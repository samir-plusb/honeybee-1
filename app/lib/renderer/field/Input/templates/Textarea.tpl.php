<div class="control-group">
<?php
    if ('yes' === $field->getOption('use_richtext', FALSE))
    {
?>
    <textarea type="text" name="<?php echo $inputName; ?>" id="<?php echo $fieldId; ?>" 
        placeholder="<?php echo $placeholder; ?>" class="ckeditor"><?php echo $fieldValue; ?></textarea>
<?php
    }
    else
    {
?>
    <label class="control-label" for="<?php echo $fieldId; ?>"><?php echo $tm->_($fieldName, $td); ?></label>
    <div class="controls">
        <textarea type="text" class="span11" name="<?php echo $inputName; ?>" id="<?php echo $fieldId; ?>" 
            placeholder="<?php echo $placeholder; ?>" class="input-xlarge"><?php echo $fieldValue; ?></textarea>
    </div>
<?php
    }
?>
</div>