<?php
    $controllerOptions = $t['controller_options'];
    $itemData = $t['item_data'];
    $ticketData = $t['ticket_data'];
    $coreItem = $itemData['coreItem'];
    $ticketId = isset($ticketData['identifier']) ? $ticketData['identifier'] : '';
    $location = $coreItem['location'];
    $isHidden = (isset($itemData['attributes']['isHidden']) && 1 == $itemData['attributes']['isHidden']);
    $locationWidgetOpts = $t['location_widget_opts'];
    $escape = function($val) { return htmlspecialchars($val); };
?>
<div class="container-fluid controller-edit container-core-item"
     data-edit-controller-options='<?php echo $controllerOptions; ?>'>
<!-- controller notifications (optional section for displaying alerts that are added to the controller) -->
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
    <form action="<?php echo $ro->gen(null); ?>" class="form-horizontal" method="post"
          data-bind="submit: onFormSubmit">
        <div class="row-fluid">
<!-- ###############################################################################################
Navigation:
    Holds the various views available for the editing shofi places.
############################################################################################### -->
            <section class="span3 well navigation" style="padding:8px 0">
                <ul class="nav nav-list">
                    <li class="nav-header">Daten bearbeiten</li>
                    <li class="active"><a href="<?php echo $ro->gen(null, array('_page' => 'CoreItem')); ?>"><i class="icon-edit"></i> Stammdaten</a></li>
                    <li><a href="<?php echo $ro->gen(null, array('_page' => 'DetailItem')); ?>"><i class="icon-edit"></i> Redaktions-Daten</a></li>
                    <li><a href="<?php echo $ro->gen(null, array('_page' => 'SalesItem')); ?>"><i class="icon-edit icon-white"></i> Vertriebsdaten</a></li>
                    <li class="nav-header">Goodies</li>
                    <li onclick="return nope()"><a href="<?php $ro->gen(null); ?>"><i class="icon-picture"></i> Medien</a></li>
<?php 
    if (isset($t['contentmachine_link']))
    {
?>
                    <li class="clipboard-widget" data-clipboard-widget-options="<?php echo $t['clipboard_widget_opts']; ?>">
                        <a href="#clipboard" class="clipboard-widget-trigger" title="<?php echo $t['contentmachine_link']; ?>"><i class="icon-copy"></i> CM-Url nach Zwischenablage kopieren</a>
                    </li>
<?php
    }
?>
                </ul>
            </section>
            <script type="text/javascript">
                function nope(){
                    alert("Diese Funktion ist noch nicht implementiert");
                    return false;
                }
            </script>
<!-- ###############################################################################################
Editing:
    Holds form for editing the current shofi place's coreItem. (All form values are nested here.)
############################################################################################### -->
            <section class="span6 editing well">
                <input type="hidden" name="ticket" class="ticket-identifier" value="<?php echo $escape($ticketId); ?>" />
<!-- coreItem meta data -->
                <div class="page-header" style="margin-top:0">
                    <h2>Grundeinstellungen</h2>
                </div>
                <div class="control-group">
                    <label class="control-label">R4-Artikel-Id</label>
                    <div class="controls">
                        <input type="input" name="attributes[r4id]" value="<?php echo $escape(isset($itemData['attributes']['r4id']) ? $itemData['attributes']['r4id'] : ''); ?>" />
                        <p class="help-block">Die ID dieses Eintrags in R4</p>
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Eintrag verstecken?</label>
                    <div class="controls">
                        <input type="hidden" name="attributes[isHidden]" value="0" />
                        <input type="checkbox" name="attributes[isHidden]" value="1" <?php echo $isHidden ? 'checked="checked"' : ''; ?>/>
                        <p class="help-block">Du kannst diesen Eintrag verstecken,<br /> so dass er nicht auf unseren Webseiten angezeigt wird.</p>
                    </div>
                </div>
<!-- coreItem common data -->
                <div class="page-header" style="margin-top:0">
                    <h2>Angaben zum Unternehmen</h2>
                </div>
                <div class="control-group">
                    <label class="control-label">Name</label>
                    <div class="controls">
                        <input type="input" name="coreItem[name]" value="<?php echo $escape($coreItem['name']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Unternehmen</label>
                    <div class="controls">
                        <input type="input" name="coreItem[company]" value="<?php echo $escape($coreItem['company']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Titel</label>
                    <div class="controls">
                        <input type="input" name="coreItem[title]" value="<?php echo $escape($coreItem['title']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label" for="Nachname">Nachname</label>
                    <div class="controls">
                        <input type="input" name="coreItem[lastName]" value="<?php echo $escape($coreItem['lastName']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Vorname</label>
                    <div class="controls">
                        <input type="input" name="coreItem[firstName]" value="<?php echo $escape($coreItem['firstName']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Telefonnummer (mobil)</label>
                    <div class="controls">
                        <input type="input" name="coreItem[mobile]" value="<?php echo $escape($coreItem['mobile']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Telefonnummer</label>
                    <div class="controls">
                        <input type="input" name="coreItem[phone]" value="<?php echo $escape($coreItem['phone']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Fax</label>
                    <div class="controls">
                        <input type="input" name="coreItem[fax]" value="<?php echo $escape($coreItem['fax']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">Webadresse</label>
                    <div class="controls">
                        <input type="input" name="coreItem[website]" value="<?php echo $escape($coreItem['website']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label class="control-label">E-Mail</label>
                    <div class="controls">
                        <input type="input" name="coreItem[email]" value="<?php echo $escape($coreItem['email']); ?>" />
                    </div>
                </div>
<!-- coreItem.location data -->
                <section class="widget-location-widget" data-location-widget-options="<?php echo $locationWidgetOpts; ?>">
                </section>
<!-- form action buttons -->
                <div class="form-actions well">
                    <input type="submit" value="Speichern" class="btn btn-primary" />
                </div>
            </section>
            <section class="span4">

            </section>
        </div>
    </form>
</div>
