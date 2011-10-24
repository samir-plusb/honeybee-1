<ul>

<?php
foreach ($t['items'] as $item)
{
    echo '<!-- '.print_r($item,1).' -->';
?>

    <li>
        <h3><?php echo $item['title']; ?></h3>
        <p>
            <?php echo strip_tags($item['content']); ?>
        </p>
    </li>

<?php
}
?>

</ul>