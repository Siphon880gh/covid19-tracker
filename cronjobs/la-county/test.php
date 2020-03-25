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

// HELPERS
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

// VALIDATION
// Save today's cumulative cases for tomorrow
$yesterdaysComparisonDay = intval(date("d", filemtime("data/yesterday-cumulative.txt")));
$todaysComparisonDay = intval(date("d", time()));
if($yesterdaysComparisonDay >= $todaysComparisonDay) {
    die(json_encode(["error"=>"Cron job ran more than once. Yesterday's cumulative log has same modified date as today's date. No changes done to daily case breakdown."]));
}

// Get today's cumulative cases
$view_source = get_view_source($source);
$todaysCumulativeCases = get_todays_cumulative($view_source);
$todaysCumulativeCases = intval($todaysCumulativeCases);

// Get yesterday's cumulative cases
$yesterdaysCumulativeCases = file_get_contents("data/yesterday-cumulative.txt");
$yesterdaysCumulativeCases = intval($yesterdaysCumulativeCases);

// Get today's breakdown (subtract today from yesterday's cumulative cases)
$todaysCaseBreakdown = $todaysCumulativeCases - $yesterdaysCumulativeCases;
date_default_timezone_set("America/Los_Angeles");

// Save today's breakdown to historic breakdowns
$hadDumped = file_get_contents("data/daily-cases.json");
$hadDumped = json_decode($hadDumped, true);
$todaysDate = date("n/j/y", time());
if( isset($hadDumped[$todaysDate]) ) {
    $hadDumped[$todaysDate] = $todaysCaseBreakdown;
} else {
    $hadDumped = array_merge([$todaysDate=>$todaysCaseBreakdown], $hadDumped);
}
$prettyJson = json_encode($hadDumped, JSON_PRETTY_PRINT);
$prettyJson = str_replace("\/", "/", $prettyJson);
file_put_contents("data/daily-cases.json", $prettyJson);

// Save today's cumulative cases (what will be used tomorrow in another cron job)
file_put_contents("data/yesterday-cumulative.txt", $todaysCumulativeCases);

// Success
echo json_encode(["success"=>"Cron job ran to get today's cumulative cases from LA County Public Health, subtract from yesterday's cumulative cases, then append to the daily case breakdown for the app."]);
// die();


echo "<br/>";
echo "Today's date: $todaysDate";
echo "<br/>";
echo "Todays cumulative cases: $todaysCumulativeCases";
echo "<br/>";
echo "Yesterday's cumulative cases: $yesterdaysCumulativeCases";
echo "<br/>";
echo "Yesterday's cumulative case log last modified day: " . $yesterdaysComparisonDay;
echo "<br/>";
echo "Today's for comparison day: " . $todaysComparisonDay;
echo "<br/>";
echo "Todays case broken down: $todaysCaseBreakdown";
echo "<br/>";
echo "<br/>";
echo "<a href='data/daily-cases.json'>Json daily cases</a>";
echo "<span style='width:30px; height:1px'>&nbsp;</span>";
echo "<a href='data/daily-cases.json'>Json yesterday's cumulative</a>";
die();


?>