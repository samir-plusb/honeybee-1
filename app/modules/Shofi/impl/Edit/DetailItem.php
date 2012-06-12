<?php
    $controllerOptions = $t['controller_options'];
    $categoryWidgetOptions = $t['category_widget_opts'];
    $categoriesWidgetOptions = $t['additional_categories_widget_opts'];
    $keywordsWidgetOptions = $t['keywords_widget_opts'];
    $openingTimesWidgetOptions = $t['opening_times_widget_opts'];
    $attrbuteWidgetOptions = $t['attributes_widget_opts'];
    $itemData = $t['item_data'];
    $ticketData = $t['ticket_data'];
    $ticketId = isset($ticketData['identifier']) ? $ticketData['identifier'] : '';
    $detailItem = $itemData['detailItem'];
    $assetListOptions = $t['asset_widget_opts'];
?>
<div class="container-fluid controller-edit container-detail-item"
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
    @todo Pull up over form for the semantic's sake, best would an own top level section.
    Holds the various views available for the editing shofi places.
############################################################################################### -->
            <section class="span3 well navigation" style="padding:8px 0">
                <ul class="nav nav-list">
                    <li class="nav-header">Daten bearbeiten</li>
                    <li><a href="<?php echo $ro->gen(null, array('_page' => 'CoreItem')); ?>"><i class="icon-edit"></i> Stammdaten</a></li>
                    <li class="active"><a href="<?php echo $ro->gen(null, array('_page' => 'DetailItem')); ?>"><i class="icon-edit"></i> Redaktions-Daten</a></li>
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
    Holds form for editing the current shofi place's detailItem. (All form values are nested here.)
############################################################################################### -->
            <div class="span8 editing">
                <input type="hidden" name="ticket" class="ticket-identifier" value="<?php echo $ticketId; ?>" />
                <div class="row-fluid">
                    <div class="span12">
<!-- detailItem textual content (teaser, text...) -->
                        <div class="page-header" style="margin-top:0">
                            <h2>Texte <small>für die redaktionelle Seite</small></h2>
                        </div>
                        <div class="control-group ">
                            <label class="control-label">Teaser</label>
                            <div class="controls">
                                <textarea name="detailItem[teaser]" style="width:100%" rows="6"><?php echo $detailItem['teaser']; ?></textarea>
                            </div>
                        </div>
                        <div class="control-group">
                            <label class="control-label">Beschreibungstext</label>
                            <div class="controls">
                                <textarea name="detailItem[text]" style="width:100%" rows="6"><?php echo $detailItem['text']; ?></textarea>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row-fluid">
                    <div class="span12">
<!-- *tabbable: detailItem additional data such as opening times, categories and attributes eg. -->
                        <div class="page-header">
                            <h2>Weitere Eigenschaften <small></small></h2>
                        </div>
                        <div class="tabbable">
                            <ul class="nav nav-tabs">
                                <li class="active"><a href="#tab-1" data-toggle="tab">Schlagworte</a></li>
                                <li><a href="#tab-2" data-toggle="tab">Branchenzugehörigkeit</a></li>
                                <li><a href="#tab-3" data-toggle="tab">Öffnungszeiten</a></li>
                                <li><a href="#tab-4" data-toggle="tab">Weitere Eigenschaften</a></li>
                                <li><a href="#tab-5" data-toggle="tab">Bilder</a></li>
                            </ul>
                        </div>
                        <div class="tab-content">
<!-- **tab: detailItem.keywords -->
                            <div class="tab-pane well active" id="tab-1">
                                <section class="widget-tags-list widget-keywords"
                                          data-tags-list-options='<?php echo $keywordsWidgetOptions; ?>'>
                                </section>
                            </div>
<!-- **tab: detailItem.categories -->
                            <div class="tab-pane well" id="tab-2">
                                <div class="row-fluid">
                                    <div class="span6">
                                        <h3>Primärbranche</h3>
                                        <section class="widget-tags-list widget-category"
                                                 data-tags-list-options='<?php echo $categoryWidgetOptions; ?>'>
                                        </section>
                                    </div>
                                    <div class="span6">
                                        <h3>Weitere Branchen</h3>
                                        <section class="widget-tags-list widget-additional-categories"
                                                  data-tags-list-options='<?php echo $categoriesWidgetOptions; ?>'>
                                        </section>
                                    </div>
                                </div>
                            </div>
<!-- **tab: detailItem.openingTimes -->
                            <div class="tab-pane well" id="tab-3">
                                <section class="widget-time-table widget-opening-times row-fluid "
                                         data-time-table-options='<?php echo $openingTimesWidgetOptions; ?>'>
                                </section>
                            </div>
<!-- **tab: detailItem.attributes -->
                            <div class="tab-pane well" id="tab-4">
                                <section class="widget-key-values-list widget-attributes"
                                         data-key-values-list-options='<?php echo $attrbuteWidgetOptions; ?>'>
                                </section>
                            </div>
<!-- **tab: salesItem.attachments -->
                            <div class="tab-pane well" id="tab-5">
                                <div class="row-fluid">
                                    <section class="widget-asset-list widget-detail-attachments"
                                             data-asset-list-options='<?php echo $assetListOptions; ?>'>
                                    </section>
                                </div>
                            </div>
                        </div>
                    </div>
<!-- form action buttons -->
                    <div class="row-fluid">
                        <div class="span12">
                            <div class="form-actions ">
                                <input type="submit" value="Speichern" class="btn btn-primary" />
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>