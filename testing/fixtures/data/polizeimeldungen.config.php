<?php

return array(
    'import' => array(
        'class' => 'ImperiaDataImport',
        'name' => 'Polizeimeldungen',
        'description' => 'Imports the imperia "Polizeimeldungen" data into our couch database.',
        'settings' => array(
            'couchdb_host' => 'localhost',
            'couchdb_port' => '5984',
            'couchdb_database' => 'imperia',
            'doc_ids_url' => 'https://imperia.berlinonline.de/imperia/ContentWorker/export-to-mail.php?getNotifications=1'
        ),
        'datasource' => array(
            'class' => 'ImperiaXmlDataSource',
            'record' => 'PoliceReportDataRecord',
            'name' => 'Polizeimeldungen',
            'description' => 'Provides access to the imperia "Polizeimeldungen" data via http.',
            'settings' => array(
                'url' => 'https://imperia.berlinonline.de/',
                'account_user' => 'hans',
                'account_pass' => 'wurst'
            )
        )
    )
)
?>
