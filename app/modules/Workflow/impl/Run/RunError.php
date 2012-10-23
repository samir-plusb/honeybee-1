<div class="topbar" data-scrollspy="scrollspy">
	<div class="topbar-inner">
		<div class="container-fluid">
			<h2 class="left">
				<a class="brand" href="<?php echo $ro->gen('index'); ?>">Midas</a>
			</h2>
		</div>
	</div>
</div>

<div class="container" style="margin-top: 15em;">
    <div class="content">
        <p style="color: red; font-size: 16pt;">
<?php
    if (isset($t['content']))
    {
        echo $t['content'];
    }
?>
        </p>
    </div>
</div>