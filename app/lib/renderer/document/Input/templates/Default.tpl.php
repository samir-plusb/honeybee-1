<div class="container-fluid controller-edit wrapper <?php echo 'edit-' . $modulePrefix; ?>"
     data-edit-controller-options="<?php echo $controllerOptions; ?>">
<!-- 
    controller notifications (optional section for displaying alerts that are added to the controller) 
-->
    <section class="container-alerts"
         data-bind="template: { foreach: alerts, afterAdd: showAlert, beforeRemove: hideAlert }">
        <div class="alert"
             data-bind="css: {
                            'alert-success': 'success' === type,
                            'alert-error': 'error' === type
                        }">
            <strong data-bind="text: message"></strong>
            <i class="icon-remove icon-white close"
               data-bind="click: function(alert) { $parent.removeAlert(alert); }"> </i>
        </div>
    </section>
<!-- 
    the actual editing form.
-->
    <div class="row-fluid" style="margin-top: 85px">

        <form class="span12 form-horizontal" method="post" action="<?php echo $editLink; ?>"
              data-bind="submit: onFormSubmit" style="padding-bottom: 60px;">
            <fieldset>
                <legend style="display: none;"><?php echo $tm->_('edit-form-title', $td); ?></legend>
                <div class="tabbable" style="margin-top: 20px;">
                    <ul class="nav nav-tabs">
<?php
    foreach ($tabs as $tabName => $tabDef)
    {
?>
                        <li class="<?php echo (TRUE === $tabDef['is_default']) ? 'active' : ''; ?>">
                            <a data-toggle="tab" href="#tab-<?php echo $tabName; ?>">
                                <?php echo $tm->_($tabName, $td); ?>
                            </a>
                        </li>
<?php
    }
?>
                    </ul>
                </div>
                <div class="tab-content">
<?php
    foreach ($tabs as $tabName => $tabDef)
    {
?>
                    <div class="tab-pane tab-<?php echo $tabName; ?> <?php echo (TRUE === $tabDef['is_default']) ? 'active in' : ''; ?> fade" 
                        id="tab-<?php echo $tabName; ?>">
<?php
        foreach ($tabDef['rows'] as $rowGroups)
        {
?>
                        <div class="row-fluid">
<?php
            foreach ($rowGroups as $groupName => $groupDef)
            {
?>
                            <div class="span<?php echo $groupDef['width']; ?> group">
                                <div class="field-group field-group-<?php echo $groupName; ?>">
                                    <h3><?php echo $tm->_($groupName, $td); ?></h3>
<?php
                foreach ($groupDef['fields'] as $field)
                {
                    echo $field;
                }
?>
                                </div>
                            </div>
<?php
            }
?>
                        </div>
<?php 
        }
?>
                    </div>
<?php
    }
?>
                </div>
            </fieldset>
            <div class="form-actions">
                <a href="<?php echo $listLink; ?>" class="btn" style="margin-right: 20px;"><strong>Abbrechen</strong></a>
                <button type="submit" class="btn btn-primary"><strong>Speichern</strong></button>
            </div>
        </form>
    </div>
    <div class="push"></div>
</div>
