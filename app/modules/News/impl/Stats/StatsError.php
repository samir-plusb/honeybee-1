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
        <div>
            <p>An error occured while trying to proccess your request.</p>
            <p><?php echo implode("</ br>", $t['errors']); ?></p>
        </div>
	</div>
</div>
