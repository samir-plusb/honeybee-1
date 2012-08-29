<?php
    $controllerOptions = $t['controller_options'];
    $categoriesWidgetOptions = $t['additional_categories_widget_opts'];
    $keywordsWigetOptions = $t['keywords_widget_opts'];

    $itemData = $t['item_data'];
    $ticketData = $t['ticket_data'];
    $ticketId = isset($ticketData['identifier']) ? $ticketData['identifier'] : '';
    $salesItem = $itemData['salesItem'];
    $assetListOptions = $t['asset_widget_opts'];
    $escape = function($val) { return htmlspecialchars($val); };
?>
<div class="container-fluid controller-edit container-sales-item"
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
                    <li><a href="<?php echo $ro->gen(null, array('_page' => 'CoreItem')); ?>"><i class="icon-edit"></i> Stammdaten</a></li>
                    <li><a href="<?php echo $ro->gen(null, array('_page' => 'DetailItem')); ?>"><i class="icon-edit"></i> Redaktions-Daten</a></li>
                    <li class="active"><a href="<?php echo $ro->gen(null, array('_page' => 'SalesItem')); ?>"><i class="icon-edit icon-white"></i> Vertriebsdaten</a></li>
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
    Holds form for editing the current shofi place's salesItem. (All form values are nested here.)
############################################################################################### -->
			<div class="span8 editing">
                <input type="hidden" name="ticket" class="ticket-identifier" value="<?php echo $escape($ticketId); ?>" />
				<div class="row-fluid ">
					<div class="span12">
<!-- salesItem data -->
						<div class="page-header" style="margin-top:0">
							<h2>Sales-Einstellungen</h2>
						</div>
						<div class="control-group">
							<label class="control-label">Eintrag gekündigt zum</label>
							<div class="controls">
								<input type="input" name="salesItem[expireDate]" value="<?php echo $escape($salesItem['expireDate']); ?>" />
								<p class="help-block">Hier legst Du fest, bis wann dieser Eintrag verkauft wurde.</p>
							</div>
						</div>
						<div class="control-group">
							<label class="control-label">Produkt</label>
							<div class="controls">
								<select name="salesItem[product]">
									<option value="no-product" <?php echo ('no-product' == $salesItem['product']) ? 'selected="selected"' : ''; ?>>nicht verkauft</option>
                                    <option value="basic" <?php echo ('basic' == $salesItem['product']) ? 'selected="selected"' : ''; ?>>Basis verkauft</option>
									<option value="business" <?php echo ('business' == $salesItem['product']) ? 'selected="selected"' : ''; ?>>Business-Eintrag</option>
									<option value="premium" <?php echo ('premium' == $salesItem['product']) ? 'selected="selected"' : ''; ?>>Premium-Eintrag</option>
                                    <option value="freemium" <?php echo ('freemium' == $salesItem['product']) ? 'selected="selected"' : ''; ?>>Freemium-Eintrag</option>
								</select>
							</div>
						</div>
					</div>
<!-- salesItem data -->
					<div class="page-header" style="margin-top:0">
						<h2>Texte <small>für den vertrieblichen Eintrag</small></h2>
					</div>
					<div class="control-group ">
						<label class="control-label">Teaser</label>
						<div class="controls">
							<textarea name="salesItem[teaser]" style="width:100%" rows="6"><?php echo $escape($salesItem['teaser']); ?></textarea>
						</div>
					</div>
					<div class="control-group">
						<label class="control-label">Beschreibungstext</label>
						<div class="controls">
							<textarea name="salesItem[text]" style="width:100%" rows="6"><?php echo $escape($salesItem['text']); ?></textarea>
						</div>
					</div>

<!-- *tabbable: salesItem - additional data -->
					<div class="page-header">
						<h2>Weitere Eigenschaften <small></small></h2>
					</div>
					<div class="tabbable">
						<ul class="nav nav-tabs">
							<li class="active"><a href="#tab-1" data-toggle="tab">Schlagworte</a></li>
							<li><a href="#tab-2" data-toggle="tab">Branchenzugehörigkeit</a></li>
                            <li><a href="#tab-3" data-toggle="tab">Bilder</a></li>
						</ul>
					</div>
					<div class="tab-content">
<!-- **tab: salesItem.keywords -->
						<div class="tab-pane active well" id="tab-1">
                            <div class="row-fluid">
                                <p class="help-inline">Hier trägst Du die <strong>Schlagworte</strong> in, die der Kunde gekauft hat. Unter diesen Schlagworten wird sein Eintrag in der Suche gefunden.</p>
                                <br /><br />
                                <section class="widget-tags-list widget-keywords"
                                         data-tags-list-options='<?php echo $keywordsWigetOptions; ?>'>
                                </section>
                            </div>
						</div>
<!-- **tab: salesItem.categories -->
						<div class="tab-pane well" id="tab-2">
                            <div class="row-fluid">
                                <p class="help-inline">Hier trägst Du die <strong>zusätzlichen Branchen</strong> ein, unter denen der Kunde gefunden werden will.</p>
                                <br /><br />
                                <section class="widget-tags-list widget-additional-categories"
                                         data-tags-list-options='<?php echo $categoriesWidgetOptions; ?>'>
                                </section>
                            </div>
						</div>
<!-- **tab: salesItem.attachments -->
                        <div class="tab-pane well" id="tab-3">
                            <div class="row-fluid">
                                <section class="widget-asset-list widget-sales-attachments"
                                         data-asset-list-options='<?php echo $assetListOptions; ?>'>
                                </section>
                            </div>
                        </div>
                    </div>
<!-- form action buttons -->
					<div class="form-actions ">
						<input type="submit" value="Speichern" class="btn btn-primary" />
					</div>
				</div>
			</div>
		</div>
	</form>
</div>