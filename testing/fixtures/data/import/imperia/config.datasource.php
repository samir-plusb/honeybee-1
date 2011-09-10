<?php

return array(
    'url' => 'https://imperia.berlinonline.de/',
    'account_user' => 'bo-xml-export',
    'account_pass' => 'P8TaamVVwOb4JGbPWtqwo',
    'record'       => 'PoliceReportDataRecord',
    //'doc_idlist_url'  => 'https://imperia.berlinonline.de/imperia/ContentWorker/export-to-mail.php?getNotifications=1'
    'doc_idlist_url'  => 'http://localhost/importer.php',
    'doc_ids'      => array( // normally these are provided by the imperia-trigger script.
        '/2/10330/10343/10890/1385807',
        '/2/10330/10343/10890/1385806',
        '/2/10330/10343/10890/1385805'
    )
);

?>