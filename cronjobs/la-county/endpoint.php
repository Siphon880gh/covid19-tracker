<?php
/**
 * Problem: The data source we are using is LA County Public Health. They do not have a public API that breaks down the number of cases 
 * daily in a table. But they do show a counter of cumulative cases that updates daily. Our app breaks down the number of cases daily.
 * There is a mismatch of the type of data to pull from their website.
 * 
 * Solution: Have a cron job that loads this endpoint daily to get the cumulative cases for that day. Then subtract from yesterday's
 * cumulative cases in order to build up the number of cases daily up to today.
 */

// Init
$source = "http://publichealth.lacounty.gov/media/Coronavirus/"; // Protocol must match http:// on their website as of 3/20/20
$dailyCases = "data/daily-cases.json";
$yesterdayCumulative = "data/yesterday-cumulative.json"; 
require("../includes/phpQuery/phpQuery.php");

// Helpers
/* add key value pair to beginning of associative array */
function array_unshift_assoc(&$arr, $key, $val)
{
    $arr = array_reverse($arr, true);
    $arr[$key] = $val;
    $arr = array_reverse($arr, true);
}


/* Gets the HTML code from a URL */
function get_view_source($url) {
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	return $data;
}

function get_todays_cumulative($view_source) {
    $doc = phpQuery::newDocument($view_source);
    $todays_cases_cumulative = $doc['.counter-text:eq(0)']->text();
    $todays_cases_cumulative = intval($todays_cases_cumulative);
    return $todays_cases_cumulative;
}

$view_source = get_view_source($source);
$todaysCumulativeCases = get_todays_cumulative($view_source);
// var_dump($todaysCumulativeCases);

$yesterdaysCumulativeCases = file_get_contents("data/yesterday-cumulative.txt");
$yesterdaysCumulativeCases = intval($yesterdaysCumulativeCases);

$todaysCaseBreakdown = $todaysCumulativeCases - $yesterdaysCumulativeCases;
date_default_timezone_set("America/Los_Angeles");
$todaysDate = date("n/j/y", time());

// var_dump([ $yesterdaysCumulativeCases, $todaysCumulativeCases, $todaysCaseBreakdown, $todaysDate ]);

$hadDumped = file_get_contents("data/daily-cases.json");
$hadDumped = json_decode($hadDumped, true);
if( isset($hadDumped[$todaysDate]) ) {
    $hadDumped[$todaysDate] = $todaysCaseBreakdown;
} else {
    $hadDumped = array_merge([$todaysDate=>$todaysCaseBreakdown], $hadDumped);
}

// Save today's case breakdown for the app
$prettyJson = json_encode($hadDumped, JSON_PRETTY_PRINT);
$prettyJson = str_replace("\/", "/", $prettyJson);
file_put_contents("data/daily-cases.json", $prettyJson);

// Save today's cumulative cases for tomorrow
file_put_contents("data/yesterday-cumulative.txt", $todaysCumulativeCases);

die(json_encode(["success"=>"Cron job ran to get today's cumulative cases from LA County Public Health, subtract from yesterday's cumulative cases, then append to the daily case breakdown for the app."]));

/* get yesterday's cases */
// function getYesterdaysCases($overrides) {
//     $keys = array_keys($overrides); // array of keys
//     $yesterdays_cases = $overrides[$keys[0]]; // first date
//     return $yesterdays_cases;
// }

// var_dump($doms);

// query dom


// $json = file_get_contents($los_angeles_override);
// $overrides  = json_decode($json, true);

// $yesterday_cases = getYesterdaysCases($overrides);
// $todays_cases = intval($todays_cases_cumulative) - intval($yesterday_cases);
// // TODO: ^^ uh oh. Not right!

// echo "Todays cumulative cases: $todays_date... $todays_cases_cumulative";
// echo "<br/>";
// echo "Todays cases: $todays_date... $todays_cases";
// echo "<br/>";
// echo "Yesterday's cases: $yesterday_cases";

// die();

// // $override[$todays_date] = $todays_cases;
// array_unshift_assoc($override, $todays_date, $todays_cases);

// $keys = array_keys($override);
// $today = $override[$keys[0]]; // first date
// $yesterday = 
// // echo json_encode($override);
// die();


?>