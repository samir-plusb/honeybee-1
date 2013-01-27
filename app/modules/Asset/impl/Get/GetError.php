<div class="error_messages">
    <ul>
<?php
    foreach ($t['errors'] as $error)
    {
?>
        <li><?php echo $error; ?></li>
<?php
    }
?>
    </ul>
</div>