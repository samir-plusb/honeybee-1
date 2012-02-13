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
        <h1><?php echo $tm->_('Task Overview','default.ui') ?></h1>
        <ul class="module-list">
            <li>
                <a href="<?php echo $ro->gen('news.list'); ?>">
                    <h3 class="label"><?php echo $tm->_('News Stream','default.ui')?></h3>
                </a>
            </li>
            <li>
                <a href="<?php echo $ro->gen('news.stats'); ?>">
                    <h3 class="label"><?php echo $tm->_('News Stats','default.ui')?></h3>
                </a>
            </li>
        </ul>
	</div>
</div>
