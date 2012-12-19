<div class="container-fluid wrapper">
	<div class="row-fluid">
        <div class="span12">
            <h1>Willkommen im Honeybee CMF</h1>
            <hr />
            <ul class="dl-horizontal">
                <li><b class="label">Datum</b>  <?php echo date('d.m.Y'); ?></li>
                <li><b class="label">Zeit</b>  <?php echo date('H:i'); ?></li>
                <li><b class="label">Status</b>  Honeybee erwartet freudig deine Aufgaben</li>
            </ul>
            <hr />
        </div>
	</div>
    <div class="info-box well">
<?php
    if (empty($t['modules']))
    {
?>
        <p class="info-paragraph">Diese Honeybee Instanz verwaltet bisher noch keine Daten.</p>
<?php
    }
    else
    {
?>
        <p class="info-paragraph">
            Derzeit verwaltet Honeybee für dich Daten zu 
<?php
        $count = 0;
        foreach ($t['modules'] as $moduleName => $module)
        {
?>
            <a href="<?php echo $module['list_link']; ?>"><?php echo $tm->_($moduleName, 'modules.labels'); ?></a>
<?php
            if ((count($t['modules']) - 1) !== $count)
            {
                echo ", ";
            }

            $count++;
        }
?>
        </p>
        <p class="tip-paragraph">
            Die Aufklappmenus in der oberen Leiste geben dir Zugriff auf die Funktionen der einzelnen Resourcen. 
        </p>
<?php
    }
?>
    </div>
    <div class="push"></div>
</div>
