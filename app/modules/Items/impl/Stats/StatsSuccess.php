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
            <li>
                <h3>Charlottenburg</h3>
                <dl>
                    <dt>Anzahl Items gesamt:</dt>
                    <dd>499</dd>
                    <dt>Anzahl Items letzte 7 Tage:</dt>
                    <dd>29</dd>
                    <dt>Anzahl Items heute:</dt>
                    <dd>3</dd>
                    <dt>Anzahl Items gestern:</dt>
                    <dd>5</dd>
                </dl>
            </li>
            <li>
                <h3>Wedding</h3>
                <dl>
                    <dt>Anzahl Items gesamt:</dt>
                    <dd>369</dd>
                    <dt>Anzahl Items letzte 7 Tage:</dt>
                    <dd>23</dd>
                    <dt>Anzahl Items heute:</dt>
                    <dd>6</dd>
                    <dt>Anzahl Items gestern:</dt>
                    <dd>8</dd>
                </dl>
            </li>
        </ul>
	</div>
</div>
