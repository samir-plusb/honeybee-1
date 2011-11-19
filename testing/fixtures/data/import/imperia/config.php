<?php

return array(
    'dataimports' =>
    array(
        'couchdb' =>
        array(
            'class' => 'CouchDbDataImport',
            'description' => 'Imports data into a configured coucdb database.',
            'datasources' =>
            array(
                0 => 'imperia',
                1 => 'dpa',
                2 => 'rss'
            ),
            'settings' =>
            array(
                'couchdb_host' => 'localhost',
                'couchdb_port' => '5984',
                'couchdb_database' => 'midas_import_testing'
            )
        ),
        'workflow' =>
        array(
            'class' => 'WorkflowItemDataImport',
            'description' => 'Imports data in form of IWorkflowItems.',
            'datasources' =>
            array(
                0 => 'imperia',
                1 => 'dpa',
                2 => 'rss'
            ),
            'settings' =>
            array(
                'notify' => false,
            )
        )
    ),
    'datasources' =>
    array(
        'imperia' =>
        array(
            'class' => 'ImperiaDataSource',
            'description' => 'Provides access to imperia-cms content via imperia\'s export-xml api.',
            'recordType' => 'PoliceReportDataRecord',
            'settings' =>
            array(
                'account_pass' => 'P8TaamVVwOb4JGbPWtqwo',
                'account_user' => 'bo-xml-export',
                'url' => 'https://imperia.berlinonline.de/'
            )
        ),
        'dpa' =>
        array(
            'class' => 'NewswireDataSource',
            'description' => 'Provides access to the DPA messages.',
            'recordType' => 'DpaNitfNewswireDataRecord',
            'settings' =>
            array(
                'glob' => 'var/tmp/dpa/regio-berlinbrandenburg/dpa-BerlinBrandenburg/*.xml',
                'timestamp_file' => '/var/tmp/dpa.time.stamp'
            )
        )
    )
);

?>