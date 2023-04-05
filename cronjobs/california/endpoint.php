<?php
/**
 * Problem: The data source we are using for California is LA Times. They do not have a public API that breaks down the number of cases 
 * daily in a table. But they do show a counter of cumulative cases that updates daily. Another possible source is CNN however I chose
 * LA Times because it's more local and therefore more accurate.
 * 
 * Solution: Our app downloads the cumulative cases daily with a cron job. The app has an algorithm that converts cumulative cases to
 * daily breakdown cases when a data source only provides cumulative cases.
 *
 * To build the historic cumulative cases, you visit this other json file from CNN:
 * Recent Historics is here: https://ix.cnn.io/data/novel-coronavirus-2019-ncov/us/historical.min.json
 * Older Historics is here: https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/covid19-historical-by-state.json
 * If the historics get too old, find the app.js file from https://www.cnn.com/interactive/2020/health/coronavirus-us-maps-and-cases/, then find the historical json (check all of them)
 * Note that there may be discrepancies between the CNN source and the current source based on their methods of collecting data.
 *
 */

// Init
$source = "https://www.latimes.com/projects/california-coronavirus-cases-tracking-outbreak/"; // Protocol must match http:// on their website as of 3/20/20
$selector = ".big-number";

$dailyCumulativePath = "data/daily-cumulative.json";
error_reporting(E_ALL ^ E_DEPRECATED);
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
	global $selector;
    $doc = phpQuery::newDocument($view_source);
	$todays_cases_cumulative = $doc[$selector]->text(); //$(".big-mumber).tex() // document.querySelector(".big-number").textContent
	$todays_cases_cumulative = str_replace(",", "", $todays_cases_cumulative);
    $todays_cases_cumulative = intval($todays_cases_cumulative);
    return $todays_cases_cumulative;
}

// Get today's cumulative cases
$view_source = get_view_source($source);
$todaysCumulativeCases = get_todays_cumulative($view_source);
// var_dump($todaysCumulativeCases);
if($todaysCumulativeCases===0) {
	echo json_encode(["error"=>"Is 0.", "php.time"=>date("m/d/Y H:i:s"), "php.timezone"=>date_default_timezone_get(), "parsed.\$todaysCumulativeCases"=>$todaysCumulativeCases]);
	die();
}

// Save today's cumulative cases to history
$hadDumped = file_get_contents($dailyCumulativePath);
$hadDumped = json_decode($hadDumped, true);
date_default_timezone_set("America/Los_Angeles");
$todaysDate = date("n/j/y", time());
if(isset($hadDumped[$todaysDate])) unset($hadDumped[$todaysDate]); // removes duplicated entries in case cron job runs multiple times
$hadDumped[$todaysDate] = $todaysCumulativeCases;
$hadDumped = array_merge([$todaysDate=>$todaysCumulativeCases], $hadDumped); // similar to array_unshift
$prettyJson = json_encode($hadDumped, JSON_PRETTY_PRINT);
$prettyJson = str_replace("\/", "/", $prettyJson);
file_put_contents($dailyCumulativePath, $prettyJson);

if(isset($_GET["manual"])) {
	echo sprintf("<script>alert(\"%s\");</script>", "Updating LA County's cumulative cases...");
}

// Success
echo json_encode(["success"=>"Cron job ran to get today's cumulative cases from LA Times, and then appended to the daily cumulative cases for the app.", "php.time"=>date("m/d/Y H:i:s"), "php.timezone"=>date_default_timezone_get(), "parsed.\$todaysCumulativeCases"=>$todaysCumulativeCases]);
die();

echo "<br/><br/>";
echo "Today's date: $todaysDate";
echo "<br/>";
echo "Todays cumulative cases: $todaysCumulativeCases";
echo "<br/>";
echo "<a href='$dailyCumulativePath'>Json daily cumulative cases</a>";
die();

?>