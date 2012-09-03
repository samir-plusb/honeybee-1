<?php
    $crumbs = $t['breadcrumbs'];
?>
<!-- ###############################################################################################
    Midas Header:
        Presents common information for the current session
        and holds the list's search box.
     ############################################################################################### -->
<header class="navbar navbar-fixed-top" data-scrollspy="scrollspy">
  <div class="navbar-inner">
    <div class="container-fluid" style="width: auto;">
      <a class="brand icon-wrench" href="<?php echo $ro->gen('index'); ?>" title="Midas 2.1 - Kalliope"> Midas 2.1</a>
<?php
    if ($us->isAuthenticated())
    {
?>
      <ul class="nav" role="navigation">
        <li class="dropdown">
          <a id="drop1" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown">Nachrichten <b class="caret"></b></a>
          <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
            <li><a tabindex="-1" href="<?php echo $ro->gen('news.list'); ?>"><span class="icon-list"></span> &Uuml;bersicht</a></li>
            <li><a tabindex="-1" href="<?php echo $ro->gen('news.stats'); ?>"><span class="icon-bar-chart"></span> Statistik</a></li>
          </ul>
        </li>
        <li class="dropdown">
          <a href="#" id="drop2" role="button" class="dropdown-toggle" data-toggle="dropdown">Orte <b class="caret"></b></a>
          <ul class="dropdown-menu" role="menu" aria-labelledby="drop2">
            <li><a tabindex="-1" href="<?php echo $ro->gen('shofi.list'); ?>"><span class="icon-list"></span> &Uuml;bersicht</a></li>
            <li><a tabindex="-1" href="<?php echo $ro->gen('shofi.config'); ?>"><span class="icon-list-alt"></span> Branchen Matching</a></li>
            <li class="divider"></li>
            <li><a tabindex="-1" href="<?php echo $ro->gen('shofi_categories.list'); ?>"><span class="icon-list"></span> Branchen</a></li>
            <li><a tabindex="-1" href="<?php echo $ro->gen('shofi_verticals.list'); ?>"><span class="icon-list"></span> Leuchtt&uuml;rme</a></li>
          </ul>
        </li>
      </ul>
      <ul class="nav">
        <li id="fat-menu" class="dropdown">
          <a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown">Veranstaltungen <b class="caret"></b></a>
          <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
            <li><a tabindex="-1" href="<?php echo $ro->gen('events.list'); ?>"><span class="icon-list"></span> &Uuml;bersicht</a></li>
          </ul>
        </li>
      </ul>
      <ul class="nav">
        <li id="fat-menu" class="dropdown">
          <a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown">Filme <b class="caret"></b></a>
          <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
            <li><a tabindex="-1" href="<?php echo $ro->gen('movies.list'); ?>"><span class="icon-list"></span> &Uuml;bersicht</a></li>
          </ul>
        </li>
      </ul>
      <ul class="nav pull-right">
        <li id="fat-menu" class="dropdown">
          <a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown"><span class="icon-user"></span> <?php echo $us->getAttribute('login'); ?><b class="caret"></b></a>
          <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
            <li><a href="<?php echo $ro->gen('auth.logout'); ?>" title="Sitzung beenden"><span class="icon-signout"></span> abmelden</a></li>
          </ul>
        </li>
      </ul>
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
    }
?>
  </div>
</header>
<!--
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
-->