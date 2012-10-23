<?php
	$url = 'localhost:5984/midas_shofi/_design/categories/_view/all?limit=50';
	$startkey = isset($_REQUEST['prefix']) ? $_REQUEST['prefix'] : null;
	if ($startkey)
	{
		$url .= "&startkey=" . urlencode('["' . $startkey . '"]');
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
	$result =  json_decode(curl_exec($ch), TRUE);
	$categories = array();
	foreach ($result['rows'] as $row)
	{
		$categories[] = $row['value'];
	}
	header('Content-Type: application/json');
    header('HTTP/1.1 200 Ok');
	echo json_encode($categories);
?>

