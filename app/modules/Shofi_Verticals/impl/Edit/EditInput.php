<?php
    $controllerOptions = $t['controller_options'];
    $ticketData = $t['ticket_data'];
    $itemData = $t['item_data'];
    $verticalData = $itemData['masterRecord'];
    $assetListOptions = $t['asset_widget_opts'];
    $ticketId = isset($ticketData['identifier']) ? $ticketData['identifier'] : '';
    $categoriesWidgetOptions = $t['top_categories_widget_opts'];
    $escape = function($val) { return htmlspecialchars($val); };
?>
<div class="container-fluid controller-edit container-vertical-item" data-edit-controller-options="<?php echo $controllerOptions; ?>">
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
    <form action="<?php echo $ro->gen(NULL); ?>" method="post" data-bind="submit: onFormSubmit">
        <input type="hidden" name="ticket" class="ticket-identifier" value="<?php echo $escape($ticketId); ?>" />
        <div class="row-fluid" >
            <div class="span6 well">
                <h2>Leuchtturm bearbeiten</h2>
                <div class="control-group ">
                    <label for="Name">Name</label>
                    <div class="controls">
                        <input type="input" name="vertical[name]" label="Name der Kategorie" value="<?php echo $escape($verticalData['name']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                     <label>Berlin.de-URL der Leuchtturm-Startseite</label>
                    <div class="controls">
                        <input type="input" name="vertical[url]" value="<?php echo $escape($verticalData['url']); ?>" />
                    </div>
                </div>
                <div class="control-group">
                    <label for="teaser">Teaser</label>
                    <div class="controls">
                        <textarea name="vertical[teaser]" label="Teaser"><?php echo $escape($verticalData['teaser']); ?></textarea>
                        <p class="help-block">
                            Hier kannst Du einen kurzen Teaser-Text für diesen Leuchtturm angelegen. <br />Er wird auf den Übersichtsseiten dieses Leuchtturms angezeigt.
                        </p>
                    </div>
                </div>
<!-- Categories Widget -->
                <h3>Top-Branchen</h3>
                <section class="widget-tags-list widget-top-categories"
                          data-tags-list-options='<?php echo $categoriesWidgetOptions; ?>'>
                </section>
<!-- images widget -->
                <section class="widget-asset-list widget-verticals-images"
                         data-asset-list-options='<?php echo $assetListOptions; ?>'>
                </section>
                <div class="form-actions" style="padding-left: 0; padding-bottom: 0">
                    <input type="submit" value="Speichern" class="btn btn-primary" />
                </div>
            </div>
            <div class="span6">
                <table class="table table-striped table-condensed table-bordered">
                    <tr>
                        <th>Name der Branche</th>
                        <th>Anzahl Datensätze</th>
                    </tr>
<?php
    foreach ($t['category_facets'] as $facet)
    {
?>
                    <tr>
                        <td>
                            <a href="<?php echo $ro->gen('workflow.run', array('type' => 'shofi_categories', 'ticket' => $facet['ticket_id'])); ?>"><?php echo $facet['name']; ?></a>
                        </td>
                        <td><?php echo $facet['count']; ?></td>
                    </tr>
<?php
    }
?>
                </table>
            </div>
        </div>
    </form>
</div>