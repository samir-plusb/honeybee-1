<!-- Topbar with logo and search form -->
<div data-scrollspy="scrollspy" class="topbar">
    <div class="topbar-inner">
        <div class="container-fluid">
            <h2 class="left">
                <a href="<?php echo $ro->gen(NULL); ?>" class="brand"><?php echo $t['_title']; ?></a>
            </h2>
            <a class="pull-right logout" href="<?php echo $ro->gen('auth.logout'); ?>">Logout</a>
            <form class="search-form pull-right" action="<?php echo $ro->gen(NULL); ?>" method="GET">
                <input type="text" name="search_phrase" value="<?php echo isset($t['search_phrase']) ? $t['search_phrase'] : '' ?>" placeholder="Suche" />
                <a href="#" class="<?php echo isset($t['search_phrase']) ? '' : 'hidden' ?> reset-search">×</a>
            </form>
        </div>
    </div>
</div>
<?php
    if (isset($t['search_phrase']))
    {
?>
<section class="container-fluid search-message-box">
    <div class="alert-message search-message info">
        <p>
            Du hast nach <strong>&#34;<?php echo $t['search_phrase']; ?>&#34;</strong> gesucht.
            Deine Suche ergab <strong><?php echo count($t['listData']); ?> Treffer</strong>.
        </p>
    </div>
</section>
<?php
    }
?>

<div class="container-fluid top">
<?php
    $hasPrev = $t['offset'] > 0;
    $hasNext = $t['offset'] + $t['limit'] < $t['totalCount'];
    $nextLink = $ro->gen(
        'items.list',
        array(
            'limit'  => $t['limit'],
            'offset' => $t['offset'] + $t['limit']
        )
    );
    $prevLink = $ro->gen(
        'items.list',
        array(
            'limit'  => $t['limit'],
            'offset' => $t['offset'] - $t['limit']
        )
    );
?>
    <!-- Pagination -->
    <div class="pagination pagination-top">
        <ul>
            <li class="prev <?php echo $hasPrev ? '' : 'disabled'; ?>">
                <a href="<?php echo $hasPrev ? $prevLink : $ro->gen(NULL); ?>">&#8592; Previous</a>
            </li>
<?php
    for ($i = 0; $i < 5; $i++)
    {
        $offset = $t['limit'] * $i;
        if ($t['totalCount'] <= $offset)
        {
            continue;
        }
        $link = $ro->gen(
            'items.list',
            array(
                'limit'  => $t['limit'],
                'offset' => $offset
            )
        );
        $active = $t['offset'] == $offset;
?>
            <li class="<?php echo $active ? 'active' : ''; ?>">
                <a href="<?php echo $link; ?>"><?php echo ($i + 1); ?></a>
            </li>
<?php
    }
?>
            <li class="next <?php echo $hasNext ? '' : 'disabled'; ?>">
                <a href="<?php echo $hasNext ? $nextLink : $ro->gen(NULL); ?>">Next &#8594;</a>
            </li>
        </ul>
    </div>

    <!-- Table section with heading and table. -->
    <table class="bordered-table zebra-striped" id="sortTableExample">
        <thead>
            <tr>
                <th>Titel</th>
                <th>Quelle</th>
                <th>Eingangsdatum</th>
                <th>Status</th>
                <th>Rubrik</th>
                <th>Alt-Bezirk</th>
                <th>Relevanz</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
<?php
    foreach ($t['listData'] as $ticketData)
    {
            $workflowItem = $ticketData['item'];
            $importItem = $workflowItem['importItem'];

            $date = new DateTime($ticketData['ts']);
            $state = isset($ticketData['step']) ? $ticketData['step'] : 'Neu';
?>
            <tr>
                <td class="title"><a href="<?php echo $ro->gen('workflow.run', array('ticket' => $ticketData['_id'])); ?>"><?php echo htmlspecialchars($importItem['title']); ?></a></td>
                <td class="source"><?php echo $importItem['source']; ?></td>
                <td class="date"><?php echo $date->format('Y-m-d H:i:s'); ?></td>
<?php
        if (!isset($ticket['step']))
        {
?>
                <td class="state"><span class="label success">Neu</span></td>
<?php
        }
        else
        {
?>
                <td class="state"><?php echo $state; ?></td>
<?php
        }
?>
                <td class="category"><?php echo empty($importItem['category']) ? '&#160;' : $importItem['category']; ?></td>
                <td class="district">&#160;<!-- Take the district of the first content-item? --></td>
                <td class="priority">&#160;<!-- Find out priority based on content-items? --></td>
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
?>
        <tfoot>
            <tr>
                <td>Titel</td>
                <td>Quelle</td>
                <td>Eingangsdatum</td>
                <td>Status</td>
                <td>Rubrik</td>
                <td>Alt-Bezirk</td>
                <td>Relevanz</td>
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
        $hasPrev = $t['offset'] > 0;
        $hasNext = $t['offset'] + $t['limit'] < $t['totalCount'];
        $nextLink = $ro->gen(
            'items.list',
            array(
                'limit'  => $t['limit'],
                'offset' => $t['offset'] + $t['limit']
            )
        );
        $prevLink = $ro->gen(
            'items.list',
            array(
                'limit'  => $t['limit'],
                'offset' => $t['offset'] - $t['limit']
            )
        );
?>
    <!-- Pagination -->
    <div class="pagination pagination-top">
        <ul>
            <li class="prev <?php echo $hasPrev ? '' : 'disabled'; ?>">
                <a href="<?php echo $hasPrev ? $prevLink : $ro->gen(NULL); ?>">&#8592; Previous</a>
            </li>
<?php
    for ($i = 0; $i < 5; $i++)
    {
        $offset = $t['limit'] * $i;
        if ($t['totalCount'] <= $offset)
        {
            continue;
        }
        $link = $ro->gen(
            'items.list',
            array(
                'limit'  => $t['limit'],
                'offset' => $offset
            )
        );
        $active = $t['offset'] == $offset;
?>
            <li class="<?php echo $active ? 'active' : ''; ?>">
                <a href="<?php echo $link; ?>"><?php echo ($i + 1); ?></a>
            </li>
<?php
    }
?>
            <li class="next <?php echo $hasNext ? '' : 'disabled'; ?>">
                <a href="<?php echo $hasNext ? $nextLink : $ro->gen(NULL); ?>">Next &#8594;</a>
            </li>
        </ul>
    </div>
<?php
    }
?>
</div>