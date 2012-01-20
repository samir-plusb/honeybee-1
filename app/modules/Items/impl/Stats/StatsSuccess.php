<div class="topbar" data-scrollspy="scrollspy">
	<div class="topbar-inner">
		<div class="container-fluid">
			<h2 class="left">
				<a class="brand" href="<?php echo $ro->gen('index') ?>">Midas</a>
			</h2>
            <a class="pull-right logout" href="<?php echo $ro->gen('auth.logout'); ?>">Logout</a>
		</div>
	</div>
</div>

<div class="container-fluid">
	<div class="content">
        <h1><?php echo $tm->_('Statistische Angeben zu den Bezirken','default.ui') ?></h1>
        <ul class="stats-list">
<?php
    foreach ($t['statistics'] as $district => $stats)
    {
        if ('_all' === $district)
        {
            continue;
        }
?>
            <li>
                <h3><?php echo ucwords($district); ?></h3>
                <dl>
                    <dt>Anzahl Items gesamt:</dt>
                    <dd>345</dd>
                    <dt>Anzahl Items letzte 7 Tage:</dt>
                    <dd><?php echo $stats['published']['week']; ?></dd>
                    <dt>Anzahl Items heute:</dt>
                    <dd><?php echo $stats['published']['today']; ?></dd>
                    <dt>Anzahl Items gestern:</dt>
                    <dd><?php echo $stats['published']['yesterday']; ?></dd>
                </dl>
            </li>
<?php
    }
?>
        </ul>
	</div>
</div>
