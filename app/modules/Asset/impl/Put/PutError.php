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

<?php

require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'PutInput.php';

?>