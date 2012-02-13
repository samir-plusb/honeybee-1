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
                <a href="<?php echo $ro->gen('index'); ?>" class="brand">Midas</a>
            </h2>
            <a class="pull-right logout" href="<?php echo $ro->gen('auth.logout'); ?>">Logout</a>
            <form class="jsb-searchbox search-form pull-right" action="<?php echo $ro->gen(NULL); ?>" method="GET">
                <input type="hidden" value="<?php echo htmlspecialchars(json_encode(array('foo' => 'bar'))); ?>" class="jsb-searchbox-config" />
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
            <col class="col-owner" />
            <col class="col-actions" />
        </colgroup>
        <thead>
            <tr>
<?php
// Render the data(table) header by traversing a list of supported header fields,
// thereby creating a order link and translating each one.
    foreach (array('title', 'source', 'timestamp', 'state', 'category', 'owner') as $headerField)
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
                    <a href="<?php echo $ro->gen('news.list', $routingData); ?>">
                        <?php echo $tm->_($headerField, 'news.structure'); ?>
                    </a>
                </th>
<?php
    }
?>
                <th><?php echo $tm->_('actions', 'news.structure'); ?></th>
            </tr>
        </thead>
        <tbody>
<?php
    $pos = 0;
    foreach ($t['listData'] as $workflowItem)
    {
// Render the table's body traversing our data and rendering one tablerow per dataset.
            $importItem = $workflowItem['importItem'];
            $date = $tm->createCalendar(
                new DateTime($importItem['created']['date'])
            );
            $step = $workflowItem['currentState']['step'];
            $grabTicketLink = $ro->gen('workflow.grab', array('ticket' => $workflowItem['ticket']));
            $processListFilterParams = array(
                'sorting' => $sorting,
                'limit' => $limit,
                'offset' => $offset
            );
            if ($searchPhrase)
            {
                $processListFilterParams['search_phrase'] = $searchPhrase;
            }
            $processListFilterParams['list_pos'] = $offset + $pos++;
            $processListFilterParams['ticket'] = $workflowItem['ticket']['id'];
            $processTicketLink = $ro->gen('workflow.run', $processListFilterParams);
            $releaseLink = $ro->gen('workflow.release', array('ticket' => $workflowItem['ticket']['id']));
            $deleteLink = $ro->gen('workflow.proceed', array('ticket' => $workflowItem['ticket']['id'], 'gate' => 'delete'));
            $ticketCheckoutRel = sprintf('data-checkout-url="%s"', $grabTicketLink);
            $demoteLink = $ro->gen('workflow.proceed', array('ticket' => $workflowItem['ticket']['id'], 'gate' => 'demote'));
            $preEditDemote = '';

            if ('publish_news' === $step)
            {
                $preEditDemote = sprintf('data-demote-url="%s"', $demoteLink);
            }

?>
            <tr>
                <td class="title">
                    <a href="<?php echo $processTicketLink; ?>" <?php echo sprintf('%s %s', $ticketCheckoutRel, $preEditDemote); ?>>
                        <?php echo htmlspecialchars($importItem['title']); ?>
                    </a>
                </td>
                <td class="source">
                    <?php echo $importItem['source']; ?>
                </td>
                <td class="date">
                    <?php echo $tm->_d($date, 'date_formats.datetime'); ?>
                </td>
                <td class="state">
                    <!-- @todo Map steps to label colors. -->
<?php
            $label = 'warning';
            if ('refine_news' == $step)
            {
                $label = 'success';
            }
            elseif ('edit_news' == $step)
            {
                $label = 'notice';
            }
?>
                    <span class="label <?php echo $label; ?>">
                        <?php echo $tm->_($step, 'news.news_workflow'); ?>
                    </span>
                </td>
                <td class="category">
                    <?php echo empty($importItem['category']) ? '&#160;' : $importItem['category']; ?>
                </td>
                <td class="owner">
<!--
    The owner label is rendered differently for three possible states:
    1.) The current user is also the owner.
    - We highlight and add a 'release ticket on click' behaviour.
    2.) Someone else currently owns the item.
    - We just highlight the field differently.
    3.) Nobody owns the item.
    - Just highlight with the 'nobody' color.

<?php echo "###########" . $workflowItem['owner'] . "::" . $t['user'] . "##################" ?>
-->
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
                    <span class="label <?php echo (WorkflowTicket::NULL_USER !== $workflowItem['owner']) ? 'important' : ''; ?>">
                        <?php echo $workflowItem['owner']; ?>
                    </span>
<?php
        }
?>
                </td>
                <td class="avail-actions">
                    <form action="<?php echo $deleteLink; ?>" class="delete-item" method="POST" <?php echo $ticketCheckoutRel; ?>>
                        <input type="hidden" name="ticket" value="<?php echo $workflowItem['ticket']['id']; ?>" />
                        <button type="submit" class="btn small danger">
                            l&#246;schen
                        </button>
                    </form>
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
        <tfoot>
            <tr>
<?php
// Render the data(table) header by traversing a list of supported header fields,
// thereby creating a order link and translating each one.
    foreach (array('title', 'source', 'timestamp', 'state', 'category', 'owner') as $headerField)
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
                <td class="data-header <?php echo ($sortingActive) ? ('sorted ' . $sortDirection) : ''; ?>">
                    <a href="<?php echo $ro->gen('news.list', $routingData); ?>">
                        <?php echo $tm->_($headerField, 'news.structure'); ?>
                    </a>
                </td>
<?php
    }
?>
                <td><?php echo $tm->_('actions', 'news.structure'); ?></td>
            </tr>
        </tfoot>
<?php
    }
?>
    </table>
<?php
    if (10 <= count($t['listData']))
    {
        echo $slots['pagination'];
    }
?>
</section>
<!--
    Markup for the popups used to display user notifications.
-->
<div class="dialog-modal-overlay" />
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
<div id="confirm-delete-dialog" class="modal fade">
    <div class="modal-header">
        <a href="#" class="close">×</a>
        <h3 class="error-title"><?php echo $tm->_('confirm_delete_title', 'news.dialogs'); ?></h3>
    </div>
    <div class="modal-body">
        <p class="error-text"><?php echo $tm->_('confirm_delete_text', 'news.dialogs'); ?></p>
    </div>
    <div class="modal-footer">
        <button class="btn deny"><?php echo $tm->_('deny_delete', 'news.dialogs'); ?></button>
        <button class="btn danger confirm"><?php echo $tm->_('confirm_delete', 'news.dialogs'); ?></button>
    </div>
</div>