<?php $locale = $tm->getCurrentLocale(); ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="<?php echo $locale->getLocaleLanguage(); ?>" lang="<?php echo $locale->getLocaleLanguage(); ?>"<?php if ($locale->getCharacterOrientation() == 'right-to-left'): ?> dir="rtl"<?php endif; ?>>
    <head>
        <meta http-equiv="Content-Type" content="<?php echo $container->getOutputType()->getParameter('http_headers[Content-Type]', 'text/html; charset=utf-8'); ?>" />
        <title><?php echo $tm->_('ContentWorker Frontend'); ?></title>
        <base href="<?php echo $ro->getBaseHref(); ?>" />
    </head>
    <body>
        <div id="content">
            <h2><?php echo $t['_title']; ?></h2>
<?php echo $inner; // print the content layer output ?>
        </div>
    </body>
</html>