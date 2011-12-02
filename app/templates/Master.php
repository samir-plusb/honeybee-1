<?php
$locale = $tm->getCurrentLocale();
$dir = (($locale->getCharacterOrientation() == 'right-to-left') ? 'rtl' : 'ltr');
?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale->getLocaleLanguage(); ?>" lang="<?php echo $locale->getLocaleLanguage(); ?>" dir="<?php echo $dir; ?>">
    <head>
        <title><?php echo htmlspecialchars($t['_title']); ?></title>
        <base href="<?php echo $ro->getBaseHref(); ?>" id="base_href"/>
        <link rel="shortcut icon" type="image/x-icon" href="images/favicon.ico" />
    </head>
    <body>
        <?php echo $inner; // print the content layer output ?>
        <div class="footer">
<?php if ($us instanceof AgaviISecurityUser && $us->isAuthenticated()) : ?>
            <a class="logout" href="auth/logout">Logout</a>
<?php endif ?>
        </div>
    </body>
</html>