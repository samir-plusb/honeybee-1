<div class="container-fluid wrapper">
	<div class="row-fluid">
        <div class="span12">
            <h1>Willkommen in Honeybee (Erato)</h1>
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
        <p class="info-paragraph">
            Derzeit verwaltet Honeybee für dich Daten zu <a href="<?php echo $ro->gen('news.list') ?>">Nachrichten</a>, 
            <a href="<?php echo $ro->gen('shofi.list'); ?>">Orten</a>, 
            <a href="<?php echo $ro->gen('events.list'); ?>">Veranstaltungen</a> und 
            <a href="<?php echo $ro->gen('movies.list'); ?>">Filmen</a>.
        </p>
        <p class="tip-paragraph">
            Die Aufklappmenus in der oberen Leiste geben dir Zugriff auf die Funktionalitäten der einzelnen Resourcen. 
        </p>
    </div>
    <div class="push"></div>
</div>
