<!-- <?php echo htmlspecialchars(__FILE__) ?> -->
<div class="error">
	<h1><?php echo $tm->_($t['_title']); ?></h1>
<?php if (! empty($t['_module'])) : ?>
	<div class="module"><?php echo htmlspecialchars($tm->_('Module').": ".$t['_module'])?></div>
	<div class="action"><?php echo htmlspecialchars($tm->_('Action').": ".$t['_action'])?></div>
	<div class="errors">
<?php endif ?>
<?php
	if (! empty($t['errors']) && is_array($t['errors']))
	{
		echo '<dl>';
		foreach ($t['errors'] as $error)
		{
			if ($error instanceof AgaviValidationError)
			{
				echo '<dt>'.htmlspecialchars(implode(', ',$error->getFields())).'</dt>';
				echo '<dd>'.htmlspecialchars($error->getMessage()).'</dd>';
			}
		}
		echo '</dl>';
	}
?>
	</div>
</div>
<!-- /<?php echo htmlspecialchars(__FILE__) ?> -->