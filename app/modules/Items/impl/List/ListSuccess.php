<ul>

<?php

foreach ($t['items'] as $item)
{
;
?>
    <li>
        <h3><?php echo $item['title']; ?></h3>
        <p>
            <?php echo $item['content']; ?>
        </p>
    </li>
<?php

}

?>

</ul>