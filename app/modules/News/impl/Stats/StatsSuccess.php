<!-- ###############################################################################################
    Midas Header:
        Presents common information for the current session.
     ############################################################################################### -->
<header class="navbar navbar-fixed-top" data-scrollspy="scrollspy">
	<div class="navbar-inner">
		<div class="container-fluid">
			<h2 class="left">
                <a href="<?php echo $ro->gen('index'); ?>" class="brand">Midas 2.0</a>
            </h2>
            <a class="pull-right logout icon-signout" href="<?php echo $ro->gen('auth.logout'); ?>"> Logout</a>
		</div>
	</div>
</header>

<div class="container-fluid">
	<div class="row-fluid stats-wrapper">
        <div class="span12">
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
                    <table class="table stats-data" data-stats-uri="<?php echo $ro->gen('news.stats', array('district' => $district, 'days_back' => 20)); ?>">

                        <thead>
                            <tr>
                                <th class="col-district">
                                    <h3><?php echo $district; ?></h3>
                                    <div class="graph-trigger-container">
                                        <img src="images/loading-small.gif" />
                                        <button class="graph-trigger btn">Chart</button>
                                    </div>
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
                                    <span class="label <?php echo (5 > $stats['lastDays'][2]) ? 'label-warning' : 'label-success'; ?>">Vorgestern</span>
<?php
            }
            elseif (1 == $i)
            {
?>
                                    <span class="label <?php echo (5 > $stats['lastDays'][1]) ? 'label-warning' : 'label-success'; ?>">Gestern</span>
<?php
            }
            elseif (0 == $i)
            {
?>
                                    <span class="label <?php echo (5 > $stats['lastDays'][0]) ? 'label-warning' : 'label-success'; ?>">Heute</span>
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
</div>
