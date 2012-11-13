<?xml version="1.0" encoding="utf-8" ?>
<?php
    $locale = $tm->getCurrentLocale();
    $dir = (($locale->getCharacterOrientation() == 'right-to-left') ? 'rtl' : 'ltr');
?>
<!DOCTYPE HTML>
<html 
    xmlns="http://www.w3.org/1999/xhtml" 
    xml:lang="<?php echo $locale->getLocaleLanguage(); ?>" 
    lang="<?php echo $locale->getLocaleLanguage(); ?>" 
    dir="<?php echo $dir; ?>">
    <head>
        <title><?php echo htmlspecialchars($t['_title']); ?></title>
        <base href="<?php echo $ro->getBaseHref(); ?>" id="base_href"/>
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
        <!-- GoogleMaps code - @todo get this sucker oughta here! -->
        <script type="text/javascript" src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <!-- %%STYLESHEETS%% -->
    </head>
    <body>
        <?php echo $slots['header']; ?>
        <?php echo $inner; ?>
        <!-- @todo Move to a dedicated footer action. -->
        <footer class="footer container-fluid">
            <p>
                Copyright &#169;2012 <a href="http://www.berlinonline.de">BerlinOnline</a> - Honeybee v3.0 Erato
            </p>
        </footer>
        <!-- %%JAVASCRIPTS%% -->
    </body>
</html>