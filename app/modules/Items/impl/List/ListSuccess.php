<!-- Topbar with logo and search form -->
<div data-scrollspy="scrollspy" class="topbar">
    <div class="topbar-inner">
        <div class="container-fluid">
            <h2 class="left"><a href="<?php echo $ro->gen(NULL); ?>" class="brand">Midas Prototype: Eingegangene News</a></h2>
            <form class="pull-right">
                <input type="text" placeholder="Suche" />
            </form>
        </div>
    </div>
</div>

<div class="container-fluid">
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
    foreach ($t['items'] as $row)
    {
            $item = $row['doc']['importItem'];
            $ticket = $row['value'];
            $date = new DateTime($item['timestamp']);
            $state = isset($ticket['step']) ? $ticket['step'] : 'Neu';
?>
            <tr>
                <td class="title"><a href="#edit"><?php echo $item['title']; ?></a></td>
                <td class="source"><?php echo $item['source']; ?></td>
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
                <td class="category">Kultur</td>
                <td class="district">Berlin</td>
                <td class="priority">wichtig</td>
                <td class="avail-actions">
                    <a class="btn small danger">L&ouml;schen</a>
                </td>
            </tr>
<?php
    }
?>
        </tbody>
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
    </table>

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
</div>