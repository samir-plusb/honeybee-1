<?php

return array(
    'dataimports' =>
    array(
        'couchdb' =>
        array(
            'class' => 'CouchDbDataImport',
            'description' => 'Imports data into a configured couchdb database.',
            'datasources' =>
            array(
                0 => 'imperia',
                1 => 'dpa',
                2 => 'rss'
            ),
            'settings' =>
            array(
                'couchdb_database' => 'CouchImport'
            )
        ),
        'news' =>
        array(
            'class' => 'NewsDataImport',
            'description' => 'Imports news data in form of NewsWorkflowItems.',
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
        ),
        'shofi' =>
        array(
            'class' => 'ShofiDataImport',
            'description' => 'Imports shofi data in form of ShofiWorkflowItems.',
            'datasources' =>
            array(
                0 => 'wkg',
                1 => 'prototype',
            ),
            'settings' =>
            array(
                'notify' => false,
            )
        ),
        'shofi.categories' =>
        array(
            'class' => 'ShofiCategoryDataImport',
            'description' => 'Imports shofi-category data records directly into the couch (no workflow).',
            'datasources' =>
            array(
                0 => 'prototype.category'
            ),
            'settings' =>
            array(
                'notify' => 'false',
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
                'path' => 'var/tmp/dpa/regio-berlinbrandenburg/dpa-BerlinBrandenburg/',
                'regexp' => '\.xml$',
                'timestamp_file' => '/var/tmp/dpa.time.stamp'
            )
        ),
        'wkg' =>
        array(
            'class' => 'WkgDataSource',
            'description' => 'Provides wkg location data coming from xml files.',
            'recordType' => 'WkgDataRecord',
            'settings' =>
            array(
                'directory' => '/home/vagrant/source/kalliope/app/../testing/fixtures/Shofi/import/wkg/',
                'file_pattern' => '^10195_\d{8,8}_00145\.xml$'
            )
        ),
        'prototype' =>
        array(
            'class' => 'ArrayDataSource',
            'description' => 'Provides location data coming from the shofi prototype.',
            'recordType' => 'PrototypeDataRecord',
            array()
        ),
        'prototype.category' =>
        array(
            'class' => 'ArrayDataSource',
            'description' => 'Provides category data coming from the shofi prototype.',
            'recordType' => 'PrototypeCategoryDataRecord',
            array()
        )
    )
);

?>