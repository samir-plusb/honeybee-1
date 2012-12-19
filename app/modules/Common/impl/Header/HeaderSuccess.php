<?php
    $crumbs = $t['breadcrumbs'];
?>
<!-- ###############################################################################################
    Midas Header:
        Presents common information for the current session
        and holds the list's search box.
     ############################################################################################### -->
<header class="navbar navbar-inverse navbar-fixed-top" data-scrollspy="scrollspy">
  <div class="navbar-inner">
    <div class="container-fluid upper-bar" style="width: auto;">
      <a class="brand" href="<?php echo $ro->gen('index'); ?>" title="Honeybee 3.0 - Erato"> <span class="icon-wrench"> Honeybee</span></a>
<?php
    if ($us->isAuthenticated())
    {
?>
      <ul class="nav" role="navigation">
<?php
      foreach ($t['modules'] as $moduleName => $module)
      {
?>
        <li class="dropdown">
          <a id="drop1" href="#" role="button" class="dropdown-toggle" data-toggle="dropdown"><?php echo $tm->_($moduleName, 'modules.labels'); ?> <b class="caret"></b></a>
          <ul class="dropdown-menu" role="menu" aria-labelledby="drop1">
            <li>
              <a tabindex="-1" href="<?php echo $module['create_link']; ?>"><span class="icon-edit"></span> Neuer Eintrag</a>
            </li>
            <li>
              <a tabindex="-1" href="<?php echo $module['list_link']; ?>"><span class="icon-list"></span> &Uuml;bersicht</a>
            </li>
          </ul>
        </li>
<?php
      }
?>
      </ul>
      <ul class="nav pull-right user-stats">
        <li id="fat-menu" class="dropdown">
          <p>Du bist angemeldet als:</p>
          <a href="#" id="drop3" role="button" class="dropdown-toggle" data-toggle="dropdown">
            <?php echo $us->getAttribute('login'); ?><b class="caret"></b>
          </a>
          <ul class="dropdown-menu" role="menu" aria-labelledby="drop3">
            <li><a href="<?php echo $ro->gen('auth.logout'); ?>" title="Sitzung beenden"><span class="icon-signout"></span> abmelden</a></li>
          </ul>
<?php
  if (isset($t['avatar_url']))
  {
?>
          <img class="avatar-image img-rounded" src="<?php echo $t['avatar_url']; ?>" /> 
<?php
  }
?>
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