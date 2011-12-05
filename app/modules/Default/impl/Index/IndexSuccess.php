<div class="topbar" data-scrollspy="scrollspy">
	<div class="topbar-inner">
		<div class="container-fluid">
			<h2 class="left">
				<a class="brand" href="<?php echo $ro->getBaseHref() ?>">Midas - News Stream</a>
			</h2>
		</div>
	</div>
</div>

<div class="container" style="margin-top:40px">
	<div class="content">
		<div class="page-header">
			<h1><?php echo $tm->_('Welcome to Midas','default.ui') ?></h1>
		</div>
		<div class="row">
			<div class="span8">
			    <ul>
			        <li>
                        <a href="<?php echo $ro->gen('items.list'); ?>"><?php echo $tm->_('News item list','default.ui')?></a>
                    </li>
			    </ul>
			</div>
		</div>
	</div>
</div>
