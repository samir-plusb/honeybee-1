<div class="topbar" data-scrollspy="scrollspy">
	<div class="topbar-inner">
		<div class="container-fluid">
			<h2 class="left">
				<a class="brand" href="<?php echo $ro->gen('index'); ?>">Midas</a>
			</h2>
		</div>
	</div>
</div>

<div class="container" style="margin-top: 10em; width: 40em;">
	<div class="content">
        <?php
    if (! empty($t['error']))
    {
?>
			<div class="row">
			    <div class=" error span4 offset4">
			        <?php echo htmlspecialchars($t['error'])?>
		        </div>
			</div>
<?php
    }
?>
		<form action="<?php echo $ro->gen(NULL) ?>" method="post">
			<fieldset>
                <legend>
                    <?php echo $tm->_('Login', 'auth.ui') ?>
                </legend>
                <div class="clearfix">
					<label for="username"><?php echo $tm->_('User','auth.ui') ?></label>
                    <div class="input">
                        <input type="text" name="username"/>
                    </div>
                </div>
                <div class="clearfix">
					<label for="password"><?php echo $tm->_('Password','auth.ui') ?></label>
                    <div class="input">
                        <input type="password" name="password" />
                    </div>
                </div>
                    <div class="well">
                        <button class="btn primary" type="submit"><?php echo $tm->_('Sign in','auth.ui') ?></button>
                    </div>
			</fieldset>
		</form>
	</div>
</div>
