<ul>

<?php

foreach ($t['items'] as $item)
{
    $class = 'item_' . $item['_id'];

    echo '<!-- '.print_r($item,1).' -->';
?>
    <li class="<?php echo $class; ?>">
        <h3><?php echo $item['title']; ?></h3>
        <p>
            <?php echo $item['content']; ?>
        </p>
    </li>
<?php

}

?>

</ul>