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
			<h1><?php echo $tm->_('Login','auth.ui') ?></h1>
		</div>
		<form action="<?php echo $ro->gen(NULL) ?>" method="post">
<?php if (! empty($t['error'])) : ?>
			<div class="row">
			    <div class=" error span4 offset4">
			        <?php echo htmlspecialchars($t['error'])?>
		        </div>
			</div>
<?php endif ?>
			<div class="row">
				<div class="span4">
					<label for="username"><?php echo $tm->_('User','auth.ui') ?></label>
				</div>
				<div class="span4">
					<input type="text" name="username"/>
				</div>
			</div>
			<div class="row">
				<div class="span4">
					<label for="password"><?php echo $tm->_('Password','auth.ui') ?></label>
				</div>
				<div class="span4">
					<input type="password" name="password" />
				</div>
			</div>
			<div class="row">
				<div class="span4 offset4">
					<button class="btn primary" type="submit"><?php echo $tm->_('Sign in','auth.ui') ?></button>
				</div>
			</div>
		</form>
	</div>
</div>
