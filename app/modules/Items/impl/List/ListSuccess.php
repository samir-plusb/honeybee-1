<!-- Topbar with logo and search form -->
<div data-scrollspy="scrollspy" class="topbar">
    <div class="topbar-inner">
        <div class="container-fluid">
            <h2 class="left">
                <a href="<?php echo $ro->gen(NULL); ?>" class="brand"><?php echo $t['_title']; ?></a>
            </h2>
            <form class="pull-right search-form" action="<?php echo $ro->gen(NULL); ?>" method="GET">
                <input type="text" name="search_phrase" value="<?php echo isset($t['search_phrase']) ? htmlspecialchars($t['search_phrase']) : '' ?>" placeholder="Suche" />
                <a href="#" class="<?php echo isset($t['search_phrase']) ? '' : 'hidden' ?> reset-search">×</a>
            </form>
        </div>
    </div>
</div>

<section class="filter container-fluid">
<!--    <h3>Filter</h3> -->
</section>


<div class="container-fluid">
<?php
    if (isset($t['search_phrase']))
    {
?>
    <div class="alert-message search-message info">
        <p>
            Du hast nach <strong>&quot;<?php echo $t['search_phrase']; ?>&quot;</strong> gesucht.
            Deine Suche ergab <strong><?php echo count($t['listData']); ?> Treffer</strong>.
        </p>
        <a href="<?php echo $ro->gen('items.list'); ?>">Suche zur&uuml;cksetzen</a>
    </div>
<?php
    }  
?>
    <!-- Pagination -->
    <div class="pagination pagination-top">
        <ul>
            <li class="prev disabled"><a href="#">&larr; Previous</a></li>
            <li class="active"><a href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">4</a></li>
            <li><a href="#">5</a></li>
            <li class="next"><a href="#">Next &rarr;</a></li>
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
                <td class="title"><a href="#edit"><?php echo $importItem['title']; ?></a></td>
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
                <td class="category"><?php echo $importItem['category']; ?></td>
                <td class="district"><!-- Take the district of the first content-item? --></td>
                <td class="priority"><!-- Find out priority based on content-items? --></td>
                <td class="avail-actions">
                    <a class="btn small danger">L&ouml;schen</a>
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
?>
    <!-- Pagination -->
    <div class="pagination">
        <ul>
            <li class="prev disabled"><a href="#">&larr; Previous</a></li>
            <li class="active"><a href="#">1</a></li>
            <li><a href="#">2</a></li>
            <li><a href="#">3</a></li>
            <li><a href="#">4</a></li>
            <li><a href="#">5</a></li>
            <li class="next"><a href="#">Next &rarr;</a></li>
        </ul>
    </div>
<?php 
    }
?>
</div>