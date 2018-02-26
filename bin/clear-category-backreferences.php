<?php

$default_context = 'console';
$environment_modifier = '';
$root_dir = dirname(dirname(__FILE__));
require $root_dir . DIRECTORY_SEPARATOR . 'app' . DIRECTORY_SEPARATOR . 'bootstrap.php';


$output = json_decode(getByCurlRequest('http://localhost:5984/famport_production_category/_all_docs'),true);

$allDocs = $output["rows"];

$def = array(
    "topics",
    "guides",
    "news",
    "surveys",
    "localities",
    "events",
    "downloads",
    "externalPages"
);

$export_count = 0;
// iterate through all docs and update if necessary (change verify to verified)
foreach ($allDocs as $doc) {

    $databaseDocument = getByCurlRequest('http://localhost:5984/famport_production_category/' . $doc["key"]);
    $linkObj = json_decode($databaseDocument, true);

    if (strpos($linkObj['_id'],'category') != 0) continue;

    foreach ($def as $field) {
        unset($linkObj[$field]);
    }

    putByCurlRequest('http://localhost:5984/famport_production_category/' . $doc["key"], json_encode($linkObj));

    $export_count++;

}

echo PHP_EOL . "Hi, I just fixed " . $export_count . " categories for you!" . PHP_EOL;


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
