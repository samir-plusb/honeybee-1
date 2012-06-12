<?php
    $controllerOptions = $t['controller_options'];
    $itemData = $t['item_data'];
    $categoryData = $itemData['masterRecord'];
    $ticketData = $t['ticket_data'];
    $salesManager = $categoryData['salesManager'];
    $salesManagerName = isset($salesManager['name']) ? $salesManager['name'] : '';
    $salesManagerPhone = isset($salesManager['phone']) ? $salesManager['phone'] : '';
    $salesManagerEmail = isset($salesManager['email']) ? $salesManager['email'] : '';
    $vertical = isset($categoryData['vertical']) ? $categoryData['vertical'] : array();
    $verticalDropdownOpts = $t['vertical_dropdown_opts'];
    $placesData = $t['places_data'];
    $assetListOptions = $t['asset_widget_opts'];
    $ticketId = isset($ticketData['identifier']) ? $ticketData['identifier'] : '';
?>
<div class="container-fluid controller-edit container-category-item" data-edit-controller-options="<?php echo $controllerOptions; ?>">
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
        <input type="hidden" name="ticket" class="ticket-identifier" value="<?php echo $ticketId; ?>" />
        <div class="row-fluid" >
            <div class="span12">
                <div class="row-fluid">
                    <section class="span4">
                        <h2>Basisdaten</h2>
                        <fieldset class="well">
                            <div class="control-group">
                                <label class="control-label">WKG-Branchen-Bezeichnung</label>
                                <div class="controls">
                                    <input type="input" name="category[name]" disabled="disabled" value="<?php echo $categoryData['name']; ?>" />
                                </div>
                            </div>
                            <div class="control-group">
                                <label class="control-label">Text</label>
                                <div class="controls">
                                    <textarea name="category[text]" rows="8"><?php echo $categoryData['text']; ?></textarea>
                                </div>
                            </div>
                        </fieldset>
                    </section>
                    <section class="span4">
                        <h2>Allgemeines</h2>
                        <div class="well">
                            <div class="control-group">
                                <label class="control-label">Datensätze i. d. Branche <em><?php echo $placesData['total_count']; ?></em></label>
                            </div>
<?php 
    if (isset($t['contentmachine_link']))
    {
?>
                            <div class="clipboard-widget" data-clipboard-widget-options="<?php echo $t['clipboard_widget_opts']; ?>">
                                <a href="#clipboard" class="clipboard-widget-trigger" title="<?php echo $t['contentmachine_link']; ?>"><i class="icon-copy"></i> CM-Url nach Zwischenablage kopieren</a>
                            </div>
<?php
    }
?>
          
                            <div class="control-group widget-verticals-dropdown" data-dropdown-widget-options="<?php echo $verticalDropdownOpts; ?>">
                                <label class="control-label">Leuchtturm</label>
                                <div class="controls">
                                    <select name="category[vertical][id]" class="value-select">
<?php
    foreach ($t['verticals'] as $verticalData)
    {
        $selected = '';
        if (isset($vertical['id']) && $vertical['id'] === $verticalData['id'])
        {
            $selected = 'selected="selected"';
        }
?>
                                        <option value="<?php echo $verticalData['id']; ?>" <?php echo $selected; ?>>
                                            <?php echo $verticalData['name']; ?>
                                        </option>
<?php
    }
?>
                                    </select>
                                    <input type="hidden" class="text-hidden" name="category[vertical][name]" value="<?php echo isset($vertical['name']) ? $vertical['name'] : ''; ?>" />
                                </div>
                            </div>
                        </div>
                    </section>
                    <section class="span4">
                        <h2>Alle Datensätze</h2>
                        <div class="well places places-list-container">
                            <ul id="placesList">
<?php
    foreach ($placesData['items'] as $place)
    {
?>
                                <li>
                                    <a target="_blank" href="<?php echo $ro->gen('workflow.run', array('type' => 'shofi', 'ticket' => $place['ticket'])) ?>"><?php echo $place['name']; ?></a>
                                </li>
<?php
    }
?>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <section>
                        <h2>Kategorisierung und Sucheinstellungen</h2>
                        <section class="tabbable">
                            <ul class="nav nav-tabs">
                                <li class="active">
                                    <a href="#tab-1" data-toggle="tab">Suchworte & Aliase</a>
                                </li>
                                <li>
                                    <a href="#tab-2" data-toggle="tab">Verzeichnisbildende Schlagworte</a>
                                </li>
                                <li>
                                    <a href="#tab-3" data-toggle="tab">Zuständiger Mitarbeiter BO-Sales</a>
                                </li>
                                <li>
                                    <a href="#tab-4" data-toggle="tab">Bild</a>
                                </li>
                            </ul>
                        </section>
                        <section class="tab-content">
                            <section class="tab-pane active well" id="tab-1">
                                <div class="row-fluid">
                                    <div class="span3">
                                        <div class="control-group">
                                            <label class="control-label">Populärname Singular</label>
                                            <div class="controls">
                                                <input type="input" name="category[alias]" value="<?php echo $categoryData['alias']; ?>" />
                                                <a href="http://www.semager.de/keywords/?q=" target="_blank" style="display: inline-block;">
                                                    <i class="icon-book"></i>
                                                </a>
                                            </div>
                                            <div class="control-group">
                                                <label class="control-label">Populärname Plural</label>
                                                <div class="controls">
                                                    <input type="input" name="category[plural]" value="<?php echo $categoryData['plural']; ?>" />
                                                </div>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label">Genus</label>
                                            <div class="controls">
                                                <select name="category[genderArticle]">
<?php
    foreach (array('der', 'die', 'das', 'kein-genus') as $option)
    {
        $selected = ($option === $categoryData['genderArticle']) ? 'selected="selected"' : '';
?>
                                                    <option value="<?php echo $option; ?>" <?php echo $selected; ?>><?php echo $tm->_($option, 'shofi.input') ?></option>
<?php
    }
?>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label">Autotext</label>
                                            <div class="controls">
                                                <input type="input" name="category[singular]" value="<?php echo $categoryData['singular']; ?>" />
                                            </div>
                                        </div>
                                    </div>
                                    <div class="span6">
                                        <div class="control-group top-category-select">
                                            <label>Als Top-Branche markieren</label>
                                            <div class="controls">
                                                <input type="hidden" name="category[isTopCategory]" value="0" />
                                                <input type="checkbox" name="category[isTopCategory]" value="1" <?php echo $categoryData['isTopCategory'] ? 'checked="checked' : ''; ?> />
                                            </div>
                                        </div>
                                        <div class="control-group">
                                            <label class="control-label">Suchworte</label>
                                            <section class="widget-tags-list widget-category-keywords"
                                                 data-tags-list-options="<?php echo $t['keywords_widget_opts'] ?>">
                                            </section>
                                            <p class="help-block">
                                                Suchworte sind Begriffe, mittels derer Du bestimmen kannst, unter welchen Begriffen diese
                                                Branche in der Suche zusätzlich noch gefunden wird.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            </section>
                            <section class="tab-pane well" id="tab-2">
                                <div class="control-group">
                                    <label class="control-label">Verzeichnisbildende Schlagworte</label>
                                    <section class="widget-tags-list widget-category-tags"
                                             data-tags-list-options="<?php echo $t['tags_widget_opts'] ?>">
                                    </section>
                                    <p class="help-block">
                                        Schlagworte oder "Verzeichnisbildende Schlagworte" sind solche Schlagworte,
                                        die Du vergeben kannst, damit diese Kategorie zu Übersichtsseiten zusammengestellt werden kann.
                                        Ein Redakteur kann zum Beispiel ein Schlagwort "Eiscreme" verwenden, um ein
                                        Themenspecial rund um "Eiscreme" auf Berlin.de mit Shofi-Einträgen zu ergänzen.
                                    </p>
                                </div>
                            </section>
                            <section class="tab-pane well" id="tab-3">
                                <div class="control-group">
                                    <label class="control-label">Name</label>
                                    <div class="controls">
                                        <input type="input" name="category[salesManager][name]" value="<?php echo $salesManagerName; ?>" />
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">E-Mail</label>
                                    <div class="controls">
                                        <input type="input" name="category[salesManager][email]" value="<?php echo $salesManagerEmail; ?>" />
                                    </div>
                                </div>
                                <div class="control-group">
                                    <label class="control-label">Telefon</label>
                                    <div class="controls">
                                        <input type="input" name="category[salesManager][phone]" value="<?php echo $salesManagerPhone; ?>" />
                                    </div>
                                </div>
                            </section>
                            <div class="tab-pane well" id="tab-4">
                                <div class="row-fluid">
                                    <section class="widget-asset-list widget-category-attachments"
                                             data-asset-list-options='<?php echo $assetListOptions; ?>'>
                                    </section>
                                </div>
                            </div>
                        </section>
                    </div>
                </div>
            </div>
            <div class="row-fluid">
                <div class="span12">
                    <div class="form-actions">
                        <input type="submit" value="Speichern" class="btn btn-primary" />
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>