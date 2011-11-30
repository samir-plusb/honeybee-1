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
			<h1>Anmeldung</h1>
		</div>
		<form action="<?php echo $ro->gen(NULL) ?>" method="post">
			<div class="row">
				<div class="span4 offset4">
					<label for="input_username" id="input_password">Username</label>
				</div>
				<div class="span4">
					<input type="text" name="username" />
				</div>
			</div>
			<div class="row">
				<div class="span4 offset4">
					<label for="input_password">Password</label>
				</div>
				<div class="span4">
					<input type="password" name="password" id="input_password" />
				</div>
			</div>
			<div class="row">
				<div class="span4 offset8">
					<button class="btn primary" type="submit">Anmelden</button>
				</div>
			</div>
		</form>
	</div>

	<footer>
		<p>© BerlinOnline 2011</p>
	</footer>
</div>
