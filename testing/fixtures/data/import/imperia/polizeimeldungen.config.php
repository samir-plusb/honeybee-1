<?php

return array(
    'import' => array(
        'class' => 'ImperiaDataImportMockUp',
        'name' => 'Polizeimeldungen',
        'description' => 'Imports the imperia "Polizeimeldungen" data into our couch database.',
        'settings' => array(
            'couchdb_host' => 'localhost',
            'couchdb_port' => '5984',
            'couchdb_database' => 'midas_import_testing'
        ),
        'datasource' => array(
            'class' => 'ImperiaDataSource',
            'record' => 'PoliceReportDataRecord',
            'name' => 'Polizeimeldungen',
            'description' => 'Provides access to the imperia "Polizeimeldungen" data via http.',
            'settings' => array(
                'url' => 'https://imperia.berlinonline.de/',
                'account_user' => 'bo-xml-export',
                'account_pass' => 'P8TaamVVwOb4JGbPWtqwo',
                //'doc_idlist_url'  => 'https://imperia.berlinonline.de/imperia/ContentWorker/export-to-mail.php?getNotifications=1'
                'doc_idlist_url'  => 'http://localhost/importer.php'
            )
        )
    )
)
?>
