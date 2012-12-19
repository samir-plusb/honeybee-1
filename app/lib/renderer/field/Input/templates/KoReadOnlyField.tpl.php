<div class="control-group field-wrapper-<?php echo $fieldName; ?>">
    <label class="control-label" for="<?php echo $fieldId; ?>"><?php echo $tm->_($fieldName, $td);; ?></label>
    <div class="controls">
        <input readonly="readonly" type="text" name="<?php echo $inputName; ?>" data-bind="value: <?php echo $fieldName; ?>"
            id="<?php echo $fieldId; ?>" placeholder="<?php echo $placeholder; ?>" class="span11" />
    </div>
</div>