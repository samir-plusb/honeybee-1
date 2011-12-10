<?php
    $searchPhrase = isset($t['search_phrase']) ? $t['search_phrase'] : FALSE;
    $limit = $t['limit'];
?>
<!-- 
    Midas Header:
        Presents common information for the current session
        and holds the list's search box.
-->
<header data-scrollspy="scrollspy" class="topbar">
    <div class="topbar-inner">
        <div class="container-fluid">
            <h2 class="left">
                <a href="<?php echo $ro->gen(NULL); ?>" class="brand"><?php echo $t['_title']; ?></a>
            </h2>
            <a class="pull-right logout" href="<?php echo $ro->gen('auth.logout'); ?>">Logout</a>
            <form class="search-form pull-right" action="<?php echo $ro->gen(NULL); ?>" method="GET">
                <input type="text" name="limit" value="<?php echo $limit; ?>" />
                <input type="text" name="search_phrase" value="<?php echo $searchPhrase ? $searchPhrase : '' ?>" placeholder="Suche" />
                <input type="hidden" name="offset" value="0" />
                <a href="#" class="<?php echo $searchPhrase ? '' : 'hidden' ?> reset-search">×</a>
            </form>
        </div>
    </div>
</header>

<!-- 
    Searchinfo-Box Section:
        Displays information on the current search result.
-->
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

<!-- 
    News-Table Section:
        Presents the list data and provides buttons and links
        that run per item based operations, such as entering an item's workflow,
        viewing or deleting an item.
        It also wraps the upper pagination.
-->
<section class="container-fluid top">
    
<?php
    echo $slots['pagination'];
?>
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
// Render the table's body traversing our data and rendering one tablerow per dataset.
            $workflowItem = $ticketData['item'];
            $importItem = $workflowItem['importItem'];

            $date = new DateTime($ticketData['ts']);
            $state = isset($ticketData['step']) ? $ticketData['step'] : 'Neu';
?>
            
            <tr>
                <td class="title">
                    <a href="<?php echo $ro->gen('workflow.run', array('ticket' => $ticketData['_id'])); ?>">
                        <?php echo htmlspecialchars($importItem['title']); ?>
                    </a>
                </td>
                <td class="source">
                    <?php echo $importItem['source']; ?>
                </td>
                <td class="date">
                    <?php echo $date->format('Y-m-d H:i:s'); ?>
                </td>
                
<?php
        if (!isset($ticket['step']))
        {
// @todo A ticket's step should always be consistently available.
// Ensure the latter and remove isset hack.
?>
                
                <td class="state">
                    <span class="label success">Neu</span>
                </td>
                
<?php
        }
        else
        {
?>
                
                <td class="state">
                    <?php echo $state; ?>
                </td>
                
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
    if (10 <= count($t['listData']))
    {
// If we have more rows than we assume fit on to the viewport without scrolling,
// display a table footer with the column names and a pagination below the table too.
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
    if (10 <= count($t['listData']))
    {
        echo $slots['pagination'];
    } 
?>
</section>