<div class="control-group field-wrapper-<?php echo $fieldName; ?>">
    <label class="control-label" for="<?php echo $fieldId; ?>"><?php echo $tm->_($fieldName, $td);; ?></label>
<?php
    if ($hasWidget)
    {
?>
    <section class="controls honeybee-widget <?php echo $widgetType; ?>" 
        data-<?php echo str_replace('widget-', '', $widgetType); ?>-options="<?php echo $widgetOptions; ?>" ></section>
<?php
    }
    else
    {
?>
    <div class="controls">
        <input type="text" name="<?php echo $inputName; ?>" value="<?php echo $fieldValue; ?>"
            id="<?php echo $fieldId; ?>" placeholder="<?php echo $placeholder; ?>" class="span11" />
    </div>
<?php
    }
?>
</div>