<?php

$los_angeles_override = "../overrides-data/Los Angeles.json";
$target = "http://publichealth.lacounty.gov/media/Coronavirus/"; 
// Protocol must match http:// on their website as of 3/20/20

/* gets the data from a URL */
function get_data($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

/* add key value pair to beginning of associative array */
function array_unshift_assoc(&$arr, $key, $val)
{
    $arr = array_reverse($arr, true);
    $arr[$key] = $val;
    $arr = array_reverse($arr, true);
}

/* sets timezone */
function set_timezone($areaCode) {
    date_default_timezone_set($areaCode);
}

/* get yesterday's cases */
function getYesterdaysCases($overrides) {
    $keys = array_keys($overrides); // array of keys
    $yesterdays_cases = $overrides[$keys[0]]; // first date
    return $yesterdays_cases;
}

set_timezone("America/Los_Angeles");
$doms = get_data($target);
// var_dump($doms);

// query dom
$results = array();
require("phpQuery/phpQuery.php");

$doc = phpQuery::newDocument($doms);
$todays_cases_cumulative = $doc['.counter-text:eq(0)']->text();
$todays_date = date("n/j/y", time());

$json = file_get_contents($los_angeles_override);
$overrides  = json_decode($json, true);

$yesterday_cases = getYesterdaysCases($overrides);
$todays_cases = intval($todays_cases_cumulative) - intval($yesterday_cases);
// TODO: ^^ uh oh. Not right!

echo "Todays cumulative cases: $todays_date... $todays_cases_cumulative";
echo "<br/>";
echo "Todays cases: $todays_date... $todays_cases";
echo "<br/>";
echo "Yesterday's cases: $yesterday_cases";

die();

// $override[$todays_date] = $todays_cases;
array_unshift_assoc($override, $todays_date, $todays_cases);

$keys = array_keys($override);
$today = $override[$keys[0]]; // first date
$yesterday = 
// echo json_encode($override);
die();

