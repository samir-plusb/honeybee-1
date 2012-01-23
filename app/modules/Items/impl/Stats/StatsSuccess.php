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
                <table class="stats-data">
                    <colgroup>
                        <col class="col-title" />
                        <col class="col-all" />
                        <col class="col-week" />
                        <col class="col-yesterday" />
                        <col class="col-today" />
                    </colgroup>
                    <thead>
                        <tr>
                            <th></th>
                            <th>
                                <span class="label">Insgesamt</span>
                            </th>
                            <th>
                                <span class="label">letzte 7 Tage</span>
                            </th>
                            <th>
                                <span class="label">Gestern</span>
                            </th>
                            <th>
                                <span class="label <?php echo (5 > $stats['published']['today']) ? 'warning' : 'success'; ?>">Heute</span>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <!--<tr>
                            <th>importierte Einträge</th>
                            <td>333</td>
                            <td><?php echo $stats['imported']['week']; ?></td>
                            <td><?php echo $stats['imported']['yesterday']; ?></td>
                        <td><?php echo $stats['imported']['today']; ?></td>
                        </tr>-->
                        <tr>
                            <th>veredelte und publizierte Einträge</th>
                            <td><?php echo $stats['published']['eversince']; ?></td>
                            <td><?php echo $stats['published']['week']; ?></td>
                            <td><?php echo $stats['published']['yesterday']; ?></td>
                            <td><?php echo $stats['published']['today']; ?></td>
                        </tr>
                    </tbody>
                </table>
            </li>
<?php
    }
?>
        </ul>
	</div>
</div>
