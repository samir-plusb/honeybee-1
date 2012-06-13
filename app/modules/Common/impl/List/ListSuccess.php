<?php
    $searchPhrase = isset($t['search_phrase']) ? $t['search_phrase'] : FALSE;
    $isFiltered = $t['is_filtered'];
    $filter = isset($t['filter']) ? $t['filter'] : FALSE;
    $limit = $t['limit'];
    $totalCount = $t['total_count'];
    $offset = $t['offset'];
    $sorting = $t['sorting'];
    $sortDirection = $sorting['direction'];
    $sortField = $sorting['field'];
    $listFields = $t['list_fields'];
    $typeKey = $t['module_type_key'];
    $clientsideController = $t['client_side_controller'];
    $routeName = $t['list_route'];
    $translationDomain = $t['translation_domain'];
    $itemActions = array_values($t['item_actions']);
    $actionNames = array_keys($t['item_actions']);
    $batchActions = array_values($t['batch_actions']);
    $batchActionNames = array_keys($t['batch_actions']);
    $displayedSearch = ($searchPhrase && ! is_array($searchPhrase)) ? htmlspecialchars($searchPhrase) : '';
    $searchWidgetOpts = $t['search_widget_opts'];
?>
<section class="container-list-data row-fluid"
    data-controller="<?php echo $clientsideController['implementor']; ?>"
    data-controller-options="<?php echo htmlspecialchars(json_encode($clientsideController['options'])); ?>">

<!-- **************************************************
    List Controls/Widgets such as search and batch processing.
************************************************** -->
    <section class="well container-batch" style="min-height: 60px;">
        <section class="pull-left search-widget" style="height: 65px;"
             data-search-widget-options="<?php echo $searchWidgetOpts; ?>">
        </section>
        <section style="margin-left: 20px; border-left: 1px solid #ababab; padding-left: 20px; height: 65px;" class="pull-left">
            <h3>Stapelverarbeitung</h3>
            <div class="btn-group dropdown">
                <button class="btn midas-action midas-action-<?php echo $batchActionNames[0]; ?>"
                        data-bind="click: function(item, event) { var is_batch = true; <?php echo $batchActions[0]; ?> },
                                   enable: has_selection">
                    <?php echo $tm->_($batchActionNames[0], $translationDomain); ?>
                </button>
                <button class="btn dropdown-toggle" data-bind="enable: has_selection" data-toggle="dropdown">
                    <span class="caret"></span>
                </button>
                <ul class="dropdown-menu">
<?php

    for ($i = 1; $i < count($batchActions); $i++)
    {
?>
                    <li>
                        <a class="midas-action midas-action-<?php echo $batchActionNames[$i]; ?>"
                           data-bind="click: function(item, event) { var is_batch = true; <?php echo $batchActions[$i]; ?> },
                                      enable: has_selection"><?php echo $tm->_($batchActionNames[$i], $translationDomain); ?></a>
                    </li>
<?php
    }
?>
                </ul>
            </div>
        </section>
        <section style="margin-left: 20px; border-left: 1px solid #ababab; padding-left: 20px; height: 65px;" class="pull-left">
            <h3>Kreation</h3>
            <a class="btn" href="<?php echo $ro->gen($typeKey.'.edit'); ?>">Neuer Eintrag</a>
        </section>
    </section>
<!-- **************************************************
    Search/Filter and other notifications.
************************************************** -->
<?php
    if ($searchPhrase || $isFiltered)
    {
?>
    <section class="row-fluid" style="margin-bottom: 20px;">
        <div class="alert search-message alert-info">
            <p>
<?php
        if ($isFiltered)
        {
?>
                Deine erweiterte Suche trifft auf <strong><?php echo $totalCount; ?> Einträge zu</strong>.
<?php
        }
        else
        {
?>
                Du hast nach <strong>&#34;<?php echo $displayedSearch; ?>&#34;</strong> gesucht.
                Deine Suche ergab <strong><?php echo $totalCount; ?> Treffer</strong>.
<?php
        }
?>
            </p>
        </div>
    </section>
<?php
    }
?>
<!-- **************************************************
    ListData Pagination Top
************************************************** -->
    <section class="row-fluid">
<?php
    // @todo This should be controller by a setting, as you probally not always want to have a pagination.
    echo $slots['pagination'];
?>
<!-- **************************************************
    ListData Table
************************************************** -->
        <table class="data-list table table-striped table-bordered">
            <thead>
                <tr>
                    <th>
                        <input type="checkbox" data-bind="checked: all_selected" />
                        <!-- @todo These values should be places to the bottom of the document. -->
                        <input type="hidden" class="list-data" value="<?php echo htmlspecialchars(json_encode($t['list_data'])); ?>" />
                    </th>
<?php
// Render the data(table) header by traversing a list of supported header fields,
// thereby creating a order link and translating each one.
    foreach ($listFields as $fieldname => $field)
    {
        $sortingActive = ($sortField === $fieldname);
        $routingData = array(
            'limit'   => $limit,
            'offset'  => 0, // always start a new sorting from page 1
            'sorting' => array(
                'field'     => $fieldname,
                'direction' => ($sortingActive && 'asc' === $sortDirection) ? 'desc' : 'asc'
            )
        );
        if ($searchPhrase)
        {
            $routingData['search_phrase'] = $searchPhrase;
        }
        elseif ($isFiltered)
        {
            $routingData['filter'] = $filter;
        }
        if ($field->hasSortfield())
        {
?>
                    <th class="data-header sortable <?php echo ($sortingActive) ? ('sorted ' . $sortDirection) : ''; ?>">
                        <a href="<?php echo $ro->gen($routeName, $routingData); ?>">
                            <?php echo $tm->_($fieldname, $translationDomain); ?>
                        </a>
                    </th>
<?php
        }
        else
        {
?>
                    <th class="data-header">
                        <span><?php echo $tm->_($fieldname, $translationDomain); ?></span>
                    </th>
<?php
        }
    }
?>
                    <th class="header-actions"><?php echo $tm->_('actions', $translationDomain); ?></th>
                </tr>
            </thead>
            <tbody data-bind="foreach: list_items">
                <tr>
                    <td>
                        <input type="checkbox" data-bind="checked: selected" />
                    </td>
<?php
        foreach ($t['templates'] as $fieldname => $listTemplate)
        {
?>
                    <?php echo $listTemplate; ?>
<?php
        }
?>
                    <td>
                        <div class="btn-group dropdown">
                            <button class="btn btn-primary midas-action midas-action-<?php echo $itemActions[0]; ?>"
                                    data-bind="click: function(item, event) { var is_batch = false; <?php echo '$parent.'.$itemActions[0]; ?> }">
                                <?php echo $tm->_($actionNames[0], $translationDomain); ?>
                            </button>
                            <button class="btn dropdown-toggle btn-primary" data-toggle="dropdown">
                                <span class="caret"></span>
                            </button>
                            <ul class="dropdown-menu pull-right">
<?php

    for ($i = 1; $i < count($itemActions); $i++)
    {
?>
                                <li>
                                    <a class="midas-action midas-action-<?php echo $itemActions[$i]; ?>"
                                       data-bind="click: function(item, event) { var is_batch = false; <?php echo '$parent.'.$itemActions[$i]; ?> }"><?php echo $tm->_($actionNames[$i], $translationDomain); ?></a>
                                </li>
<?php
    }
?>
                            </ul>
                        </div>
                    </td>
                </tr>
        </table>
<!-- **************************************************
    ListData Pagination Bottom
************************************************** -->
<?php
    if (10 <= count($t['list_data']))
    {
        echo $slots['pagination'];
    }
?>
    </section>
</section>
<!-- **************************************************
    Modal dialogs
************************************************** -->
<div class="modal modal-batch-progress" style="display:none">
    <div class="modal-header">
        <h3>Stapelverarbeitungs Fortschritt</h3>
    </div>
    <div class="modal-body">
        <div class="progress progress-striped active">
            <div class="bar" style="width: 0%;"></div>
        </div>
    </div>
    <div class="modal-footer">
        <a href="#" data-twodal-event="cancelbatch" class="btn btn-primary">Batchedit abbrechen</a>
    </div>
</div>
<div class="modal dialog-confirm" style="display:none">
    <div class="modal-header">
        <a class="close" data-dismiss="modal">×</a>
        <h4>Bitte bestätigen</h4>
    </div>
    <div class="modal-body">
        <p>Willst du den Eintrag wirklich l&ouml;schen?</p>
    </div>
    <div class="modal-footer">
        <a href="#" data-twodal-event="confirm" class="btn btn-primary">Ja</a>
    </div>
</div>