<?php

return array (
  'import' => 
  array (
    'class' => 'ImperiaDataImport',
    'name' => 'Polizeimeldungen',
    'description' => 'Imports the imperia "Polizeimeldungen" data into our couch database.',
    'settings' => 
    array (
      'couch_host' => 'localhost',
      'couch_port' => '5984',
    ),
    'datasource' => 
    array (
      'class' => 'ImperiaXmlDataSource',
      'name' => 'Polizeimeldungen',
      'description' => 'Provides access to the imperia "Polizeimeldungen" data via http.',
      'settings' => 
      array (
        'url' => 'http://imperia.berlinonline.de',
        'account_user' => 'hans',
        'account_pass' => 'wurst',
      ),
    ),
  ),
)

?>
