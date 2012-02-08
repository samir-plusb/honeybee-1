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
        <h1><?php echo $tm->_('Statistische Angaben zu den Bezirken', 'default.ui') ?></h1>
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
                <table class="stats-data" data-stats-uri="<?php echo $ro->gen('items.stats', array('district' => $district, 'days_back' => 20)); ?>">

                    <thead>
                        <tr>
                            <th class="col-district">
                                <h3><?php echo $district; ?></h3>
                                <button class="graph-trigger btn">Chart</button>
                            </th>
                            <th>
                                <span class="label">Insgesamt</span>
                            </th>
                            <th>
                                <span class="label">letzte 7 Tage</span>
                            </th>
<?php
        for ($i = $t['days_back'] - 1; 0 <= $i; $i--)
        {
?>
                            <th class="col-day <?php echo 'col-day-'.$i; ?>">
<?php
            if (2 == $i)
            {
?>
                                <span class="label <?php echo (5 > $stats['lastDays'][2]) ? 'warning' : 'success'; ?>">Vorgestern</span>
<?php
            }
            elseif (1 == $i)
            {
?>
                               <span class="label <?php echo (5 > $stats['lastDays'][1]) ? 'warning' : 'success'; ?>">Gestern</span>
<?php
            }
            elseif (0 == $i)
            {
?>
                               <span class="label <?php echo (5 > $stats['lastDays'][0]) ? 'warning' : 'success'; ?>">Heute</span>
<?php
            }
            else
            {
?>
                               <span class="label"><?php echo "Vor $i Tagen"; ?></span>
<?php
            }
?>
                            </th>
<?php
        }
?>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <th>veredelte und publizierte Einträge</th>
                            <td><?php echo $stats['totalCount']; ?></td>
                            <td><?php echo $stats['week']; ?></td>
<?php
        for ($m = $t['days_back'] - 1; $m >= 0; $m--)
        {
?>
                            <td class="col-day <?php echo 'col-day-'.$m; ?>"><?php echo $stats['lastDays'][$m]; ?></td>
<?php
        }
?>
                        </tr>
                    </tbody>
                </table>
                <div class="graph-container"></div>
            </li>
<?php
    }
?>
        </ul>
	</div>
</div>
