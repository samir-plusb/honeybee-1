<?php

    $listState = $t['state'];
    $searchPhrase = $listState->hasSearch() ? $listState->getSearch() : FALSE;
    $displayedSearch = ($searchPhrase && ! is_array($searchPhrase)) ? htmlspecialchars($searchPhrase) : '';
    $limit = $listState->getLimit();
    $offset = $listState->getOffset();
    $sortDirection = $listState->getSortDirection();
    $sortField = $listState->getSortField();
    $categoryAutoCompOpts = $t['category_autocomplete'];
?>
<section class="container-fluid list-data wrapper">
<?php echo $slots['list']; ?>
<div class="push"></div>
</section>
<!-- ###############################################################################################
    Modal Dialogs:
        Custom dialogs used by the shofi list, one for prompting categories and one for filtering.
     ############################################################################################### -->
<div class="modal" style="display:none" id="batchAssignNewCategoryModal">
    <div class="modal-header">
        <a href="#" class="close" data-dismiss="modal">×</a>
        <h3>Branche ausw&auml;hlen</h3>
    </div>
    <div class="modal-body">
        <input data-provide="typeahead" class="typeahead">
    </div>
    <div class="modal-footer">
        <a href="#" data-twodal-event="categoryselect" class="btn btn-primary">Branche &uuml;bernehmen</a>
    </div>
</div>

<div class="modal modal-search-filter" style="display: none">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h3>Erweiterte Suche</h3>
    </div>
    <form class="modal-body" action="<?php echo $ro->gen('shofi.list', array('limit' => $limit, 'offset' => 0)); ?>" method="get">
        <div class="control-group">
            <label>Straße, Hausnummer</label>
            <div class="controls">
                <input type="text" name="filter[coreItem.location.street]" class="span6" />
                <input type="text" name="filter[coreItem.location.houseNumber]" class="span3" />
            </div>
        </div>
        <div class="control-group">
            <label>PLZ</label>
            <div class="controls">
                <input type="text" name="filter[coreItem.location.postalCode]" class="span3" />
            </div>
        </div>
        <div class="control-group">
            <label>Telefonnummer</label>
            <div class="controls">
                <input type="text" name="filter[coreItem.location.phone]" class="span6" />
            </div>
        </div>
        <div class="control-group">
            <label>E-Mail-Adresse</label>
            <div class="controls">
                <input type="text" name="filter[coreItem.location.email]" class="span6" />
            </div>
        </div>
        <div class="control-group">
            <label>Branche</label>
            <div class="controls widget widget-auto-complete" data-auto-complete-options='<?php echo $categoryAutoCompOpts; ?>'>
                <input type="text" name="filter[detailItem.category]" class="class8 typeahead autocomp-input"/>
            </div>
        </div>
        <p class="help-block">
            Du kannst nach leeren Feldern suchen, indem Du in die Eingabefelder schreibst:
            @leer
        </p>
        <div class="modal-footer">
            <input type="submit" class="btn btn-primary" value="Jetzt suchen">
        </div>
    </form>
</div>