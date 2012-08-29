<?php
    $crumbs = $t['breadcrumbs'];
    $modulecrumb = $t['modulecrumb'];
?>
<!-- ###############################################################################################
    Midas Header:
        Presents common information for the current session
        and holds the list's search box.
     ############################################################################################### -->
<header class="navbar navbar-fixed-top" data-scrollspy="scrollspy">
    <div class="navbar-inner">
        <div class="container-fluid">
            <h2 class="left">
                <a style="padding-right: 10px;" href="<?php echo $ro->gen('index'); ?>" class="brand icon-home" title="Midas 2.1 Kalliope - Dashboard"> Midas 2.1</a>
<?php
    if ($modulecrumb)
    {
?>
                <span style="float: left; color: white; padding-right: 10px;">-</span>
                <a href="<?php echo $modulecrumb['link']; ?>" title="<?php echo $modulecrumb['info']; ?>" class="brand"><?php echo $modulecrumb['text']; ?></a>
<?php
    }
?>
            </h2>
            <a class="pull-right logout icon-signout icon-white" style="color: white;" href="<?php echo $ro->gen('auth.logout'); ?>" title="Aktuelle Sitzung beenden"> ausloggen</a>
        </div>
<?php 
    if (! empty($crumbs))
    {
?>
        <ul class="breadcrumb">
<?php
        $max = count($crumbs);
        for ($i = 0; $i < $max; $i++)
        {
            $crumb = $crumbs[$i];
            if ($i < $max - 1)
            {
?>
            <li>
                <a href="<?php echo $crumb['link']; ?>" title="<?php echo $crumb['info']; ?>"><span class="<?php echo $crumb['icon']; ?>">&nbsp;</span><?php echo $crumb['text']; ?></a>
            </li>
            <li>&#x2192;</li>
<?php
            }
            else
            {
?>
            <li class="active">
                <span class="<?php echo $crumb['icon']; ?>">&nbsp;</span><?php echo $crumb['text']; ?>
            </li>
<?php
            }
        }
?>
        </ul>
<?php
    }
?>
    </div>
</header>