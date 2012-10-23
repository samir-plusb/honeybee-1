<?php
    $listState = $t['state'];
    $searchPhrase = $listState->hasSearch() ? $listState->getSearch() : FALSE;
    $displayedSearch = ($searchPhrase && ! is_array($searchPhrase)) ? htmlspecialchars($searchPhrase) : '';
    $limit = $listState->getLimit();
    $offset = $listState->getOffset();
    $sortDirection = $listState->getSortDirection();
    $sortField = $listState->getSortField();
    $categoryAutoCompOpts = $t['category_autocomplete'];
    $verticalAutoCompOpts = $t['vertical_autocomplete'];
?>
<section class="container-fluid list-data wrapper">
    <?php echo $slots['list']; ?>
    <div class="push"></div>
</section>
<!-- ###############################################################################################
    Modal Dialogs:
        Custom dialog used for filtering and vertical batch assignment.
     ############################################################################################### -->
     <div class="modal modal-vertical-select" style="display:none">
    <div class="modal-header">
        <a href="#" class="close" data-dismiss="modal">×</a>
        <h3>Leuchtturm ausw&auml;hlen</h3>
    </div>
    <div class="modal-body">
        <input data-provide="typeahead" class="typeahead">
    </div>
    <div class="modal-footer">
        <a href="#" data-twodal-event="verticalselect" class="btn btn-primary">Leuchtturm &uuml;bernehmen</a>
    </div>
</div>
<div class="modal modal-search-filter" style="display: none">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h3>Erweiterte Suche</h3>
    </div>
    <form class="modal-body" action="<?php echo $ro->gen('shofi_categories.list', array('limit' => $limit, 'offset' => 0)); ?>" method="get">
        <div class="control-group">
            <label>Branchen Name</label>
            <div class="controls widget widget-auto-complete" data-auto-complete-options='<?php echo $categoryAutoCompOpts; ?>'>
                <input type="text" name="filter[masterRecord.name]" class="class8 typeahead autocomp-input"/>
            </div>
        </div>
        <div class="control-group">
            <label>Leuchtturm</label>
            <div class="controls widget widget-auto-complete" data-auto-complete-options='<?php echo $verticalAutoCompOpts; ?>'>
                <input type="text" name="filter[masterRecord.vertical.name]" class="class8 typeahead autocomp-input"/>
            </div>
        </div>
        <div class="modal-footer">
            <input type="submit" class="btn btn-primary" value="Jetzt suchen">
        </div>
    </form>
</div>