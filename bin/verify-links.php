<?php

use Honeybee\Agavi\Filter;

$default_context = 'web';
$environment_modifier = '';
$root_dir = dirname(dirname(__FILE__));
require $root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';

require(
    str_replace(
        '/', DIRECTORY_SEPARATOR, 
       $root_dir.'/vendor/agavi/agavi/src/build/agavi/build.php'
    )
);
AgaviBuild::bootstrap();

//logic starts here?

$modules = array();
foreach(new DirectoryIterator(AgaviConfig::get('core.modules_dir')) as $file)
{
    if($file->isDot())
    {
        continue;
    }

    $check = new AgaviModuleFilesystemCheck();
    $check->setConfigDirectory('config');
    $check->setPath($file->getPathname());

    if($check->check())
    {
        $modules[] = (string)$file;
    }
}

echo PHP_EOL . "Hi, I found the following modules: " . PHP_EOL;
echo implode(', ', $modules) . PHP_EOL;

if(in_array('ExternalLink', $modules)){

    $host = 'localhost:5984';
    $dbName = 'famport_production_external_link';

    echo PHP_EOL . "Starting verification of ExternalLinks. Searching database " . $dbName . PHP_EOL;

    // is cURL installed yet?
    if (!function_exists('curl_init')){
        echo PHP_EOL . 'Sorry cURL is not installed!' . PHP_EOL;
        exit(0);
    }

    // check if database is available
    $allDBs = getByCurlRequest($host . '/_all_dbs');

    if(strpos($allDBs, $dbName) < 0){
        echo PHP_EOL . 'Database not found!' . PHP_EOL;
        exit(0);
    }

    // get all docs from db
    $output = json_decode(getByCurlRequest($host . '/' . $dbName . '/_all_docs'));

    echo PHP_EOL . 'Found ' . $output->total_rows . ' external_links. Looking for external_link documents stuck in verify state' . PHP_EOL;

    $allDocs = $output->rows;
    $export_count = 0;

    // iterate through all docs and update if necessary (change verify to verified)
    foreach ($allDocs as $doc) {

        $databaseDocument = getByCurlRequest($host . '/' . $dbName . '/' . $doc->key);
        $linkObj = json_decode($databaseDocument);

        // skip if there is no workflowTicket (first and last item)
        if(!property_exists($linkObj, 'workflowTicket')) continue;

        $workflowTicket = $linkObj->workflowTicket[0];

        if ($workflowTicket->workflowStep == 'verify' || $workflowTicket->workflowStep == 'edit' ) {

            $putUrl = $host . '/' . $dbName . '/' . $linkObj->_id;

            // update workflowstep and blocked status
            $workflowTicket->workflowStep = 'verified';
            $workflowTicket->blocked = false;

            // assign workflowticket to external_link
            $linkObj->workflowTicket[0] = $workflowTicket;

            // send data to db
            $result = putByCurlRequest($putUrl, json_encode($linkObj));

            if(strpos($result, '"ok"') > 0){
                echo PHP_EOL . 'successfully verified ' . $linkObj->_id . PHP_EOL;
                $export_count++;
            }
            else echo PHP_EOL . 'update failed - ' . $result . PHP_EOL;

        }

        if ($workflowTicket->workflowStep == 'delete') {

            $putUrl = $host . '/' . $dbName . '/' . $linkObj->_id;

            // show in list -> only necessary once
            $linkObj->meta = null;

            // add "delete me" description
            $linkObj->description = 'deleted';

            // send data to db
            $result = putByCurlRequest($putUrl, json_encode($linkObj));

            if(strpos($result, '"ok"') > 0){
                echo PHP_EOL . 'successfully reset ' . $linkObj->_id . PHP_EOL;
                $export_count++;
            }
            else echo PHP_EOL . 'update failed - ' . $result . PHP_EOL;

        }

    }

    echo PHP_EOL . 'Finished update. Updated ' . $export_count . ' documents' . PHP_EOL;
    echo PHP_EOL . 'Exporting external_link documents in 5.' . PHP_EOL;
    sleep(1);
    echo '4' . PHP_EOL;
    sleep(1);
    echo '3' . PHP_EOL;
    sleep(1);
    echo '2' . PHP_EOL;
    sleep(1);
    echo '1' . PHP_EOL;
    sleep(1);


} else {
    echo PHP_EOL . "Module 'ExternalLink' not found" . PHP_EOL;
}

return;

//helper functions//

function getByCurlRequest($Url){

    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();

    // Now set some options (most are optional)

    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);

    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Download the given URL, and return output
    $output = curl_exec($ch);

    // Close the cURL resource, and free system resources
    curl_close($ch);

    return $output;
}

function putByCurlRequest($Url, $data_json){

    // OK cool - then let's create a new cURL resource handle
    $ch = curl_init();

    // Now set some options (most are optional)

    // Set URL to download
    curl_setopt($ch, CURLOPT_URL, $Url);

    // Include header in result? (0 = yes, 1 = no)
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // Should cURL return or print out the data? (true = return, false = print)
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    // Timeout in seconds
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);

    // Set Header
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json','Content-Length: ' . strlen($data_json)));

    // Set request method PUT
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');

    // Post data as json string
    curl_setopt($ch, CURLOPT_POSTFIELDS,$data_json);

    // Download the given URL, and return output
    $output = curl_exec($ch);

    // Close the cURL resource, and free system resources
    curl_close($ch);

    return $output;
}
