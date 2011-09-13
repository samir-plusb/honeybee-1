<div class="info">
    <ul>
<?php
    foreach ($t['info'] as $infoLabel => $infoValue)
    {
?>
        <li>
            <h4><?php echo $infoLabel; ?></h4>
            <p><?php echo $infoValue; ?></p>
        </li>
<?php
    }
?>
    </ul>
</div>