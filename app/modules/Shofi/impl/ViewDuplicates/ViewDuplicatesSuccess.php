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
<section class="container-fluid list-data">
<?php echo $slots['list']; ?>
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