<?php
    $searchPhrase = isset($t['search_phrase']) ? $t['search_phrase'] : FALSE;
    $limit = $t['limit'];
    $offset = $t['offset'];
    $sorting = $t['sorting'];
    $sortDirection = $sorting['direction'];
    $sortField = $sorting['field'];
?>

<!-- ###############################################################################################
    Midas Header:
        Presents common information for the current session
        and holds the list's search box.
     ############################################################################################### -->
<header data-scrollspy="scrollspy" class="topbar">
    <div class="topbar-inner">
        <div class="container-fluid">
            <h2 class="left">
                <a href="<?php echo $ro->gen(NULL); ?>" class="brand"><?php echo $t['_title']; ?></a>
            </h2>
            <a class="pull-right logout" href="<?php echo $ro->gen('auth.logout'); ?>">Logout</a>
            <form class="search-form pull-right" action="<?php echo $ro->gen(NULL); ?>" method="GET">
                <input type="text" name="search_phrase" value="<?php echo $searchPhrase ? $searchPhrase : '' ?>" placeholder="Suche" />
                <input type="hidden" name="sorting[field]" value="<?php echo $sortField; ?>" />
                <input type="hidden" name="sorting[direction]" value="<?php echo $sortDirection; ?>" />
                <input type="hidden" name="offset" value="0" />
                <a href="<?php echo $ro->gen(NULL); ?>" class="<?php echo $searchPhrase ? '' : 'hidden' ?> reset-search">×</a>
            </form>
        </div>
    </div>
</header>

<!-- ###############################################################################################
    Searchinfo-Box Section:
        Displays information on the current search result.
    ############################################################################################### -->
<?php
    if ($searchPhrase)
    {
?>
<section class="container-fluid search-message-box">
    <div class="alert-message search-message info">
        <p>
            Du hast nach <strong>&#34;<?php echo $t['search_phrase']; ?>&#34;</strong> gesucht.
            Deine Suche ergab <strong><?php echo $t['totalCount']; ?> Treffer</strong>.
        </p>
    </div>
</section>
<?php
    }
?>

<!-- ###############################################################################################
    News-Table Section:
        Presents the list data and provides buttons and links
        that run per item based operations, such as entering an item's workflow,
        viewing or deleting an item.
        It also wraps the upper&lower pagination.
    ############################################################################################### -->
<section class="container-fluid list-data">
<?php
    echo $slots['pagination'];
?>
    <table class="bordered-table zebra-striped">
        <caption>Newsitems Table</caption>
        <colgroup>
            <col class="col-title" />
            <col class="col-source" />
            <col class="col-created" />
            <col class="col-state" />
            <col class="col-category" />
            <col class="col-district" />
            <col class="col-owner" />
            <col class="col-actions" />
        </colgroup>
        <thead>
            <tr>
<?php
// Render the data(table) header by traversing a list of supported header fields,
// thereby creating a order link and translating each one.
    foreach (array('title', 'source', 'timestamp', 'state', 'category', 'district', 'owner') as $headerField)
    {
        $sortingActive = ($sortField === $headerField);
        $routingData = array(
            'limit'   => $limit,
            'offset'  => 0, // always start a new sorting from page 1
            'sorting' => array(
                'field'     => $headerField,
                'direction' => ($sortingActive && 'asc' === $sortDirection) ? 'desc' : 'asc'
            )
        );
        if ($searchPhrase)
        {
            $routingData['search_phrase'] = $searchPhrase;
        }
?>
                <th class="data-header <?php echo ($sortingActive) ? ('sorted ' . $sortDirection) : ''; ?>">
                    <a href="<?php echo $ro->gen('items.list', $routingData); ?>">
                        <?php echo $tm->_($headerField, 'items.structure'); ?>
                    </a>
                </th>
<?php
    }
?>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
<?php
    foreach ($t['listData'] as $workflowItem)
    {
// Render the table's body traversing our data and rendering one tablerow per dataset.
            $importItem = $workflowItem['importItem'];
            $date = new DateTime($importItem['created']['date']);
            $step = $workflowItem['currentState']['step'];
            $grabTicketLink = $ro->gen('workflow.grab', array('ticket' => $workflowItem['ticket']));
            $processTicketLink = $ro->gen('workflow.run', array('ticket' => $workflowItem['ticket']['id']));
            $releaseLink = $ro->gen('workflow.release', array('ticket' => $workflowItem['ticket']['id']));
?>
            <tr>
                <td class="title">
                    <a href="<?php echo $processTicketLink; ?>" data-checkout-url="<?php echo $grabTicketLink; ?>">
                        <?php echo htmlspecialchars($importItem['title']); ?>
                    </a>
                </td>
                <td class="source">
                    <?php echo $importItem['source']; ?>
                </td>
                <td class="date">
                    <?php echo $date->format('Y-m-d H:i:s'); ?>
                </td>
                <td class="state">
                    <!-- @todo Map steps to label colors. -->
                    <span class="label success">
                        <?php echo $tm->_($step, 'items.workflow'); ?>
                    </span>
                </td>
                <td class="category">
                    <?php echo empty($importItem['category']) ? '&#160;' : $importItem['category']; ?>
                </td>
                <td class="district">
                    &#160;<!-- Take the district of the first content-item? -->
                </td>
                <td class="owner">
<?php
        if ($workflowItem['owner'] === $t['user'])
        {
?>
                    <a class="label notice" href="<?php echo $releaseLink; ?>">
                        <?php echo $workflowItem['owner']; ?>
                    </a>
<?php
        }
        else
        {
?>
                    <span class="label <?php echo (WorkflowTicket::NULL_USER !== $workflowItem['owner']) ? 'important' : ''; ?>"><?php echo $workflowItem['owner']; ?></span>
<?php
        }
?>
                </td>
                <td class="avail-actions">
                    <a class="btn small danger">L&#246;schen</a>
                </td>
            </tr>
<?php
    }
?>
        </tbody>
<?php
    if (20 <= count($t['listData']))
    {
// If we have more rows than we assume fit on to the viewport without scrolling,
// display a table footer with the column names and a pagination below the table too.
?>
        <tfoot class="data-header">
            <tr>
<?php
// Render the data(table) header by traversing a list of supported header fields,
// thereby creating a order link and translating each one.
    foreach (array('title', 'source', 'timestamp', 'state', 'category', 'district', 'owner') as $headerField)
    {
        $sortingActive = ($sortField === $headerField);
        $routingData = array(
            'limit'   => $limit,
            'offset'  => $offset,
            'sorting' => array(
                'field'     => $headerField,
                'direction' => ($sortingActive && 'asc' === $sortDirection) ? 'desc' : 'asc'
            )
        );
        if ($searchPhrase)
        {
            $routingData['search_phrase'] = $searchPhrase;
        }
?>
                <td class="<?php echo ($sortingActive) ? ('sorted ' . $sortDirection) : ''; ?>">
                    <a href="<?php echo $ro->gen('items.list', $routingData); ?>">
                        <?php echo $tm->_($headerField, 'items.structure'); ?>
                    </a>
                </td>
<?php
    }
?>
                <td>Actions</td>
            </tr>
        </tfoot>
<?php
    }
?>
    </table>
<?php
    if (20 <= count($t['listData']))
    {
        echo $slots['pagination'];
    }
?>
</section>
<div id="ajax-error" class="modal fade">
    <div class="modal-header">
        <a href="#" class="close">×</a>
        <h3 class="error-title">Taking ticket ownership failed!</h3>
    </div>
    <div class="modal-body">
        <p class="error-text" />
    </div>
    <div class="modal-footer">
        <button class="btn primary">Ok</button>
    </div>
</div>