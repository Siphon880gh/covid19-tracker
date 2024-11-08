<?php
error_reporting(E_ALL ^ E_DEPRECATED);
ini_set('display_errors', '1');
/**
 * Problem: The data source we are using is LA County Public Health. They do not have a public API that breaks down the number of cases 
 * daily in a table. But they do show a counter of cumulative cases that updates daily. 
 * 
 * Solution: Our app downloads the cumulative cases daily with a cron job. The app has an algorithm that converts cumulative cases to
 * daily breakdown cases when a data source such as LA County Public Health's only provides cumulative cases.
 * 
 * Usually we use phpQuery select specific DOM. But LA County Public Health keeps messing up on their HTML so instead we opt for finding
 * two string positions, then eliminating all HTML tags which may or may not contain numbers as part of their attributes, then 
 * eliminating all non-numerical characters. The remaining numerical characters make up the number of cases.
 * 
 */

// BEFORE 4/4/2023
// Init
// $source = "http://publichealth.lacounty.gov/media/Coronavirus/locations.htm"; // Protocol must match http:// on their website as of 3/20/20
// $leftToken = "Laboratory Confirmed Cases (LCC)";
// $rightToken = "</tr>";
// $source = "http://publichealth.lacounty.gov/media/Coronavirus/js/casecounter.js"; // Before 4/4/23
$source = "http://publichealth.lacounty.gov/media/Coronavirus/json/covid19_location_casecounter.json";


$leftToken = '"hospitalizations": "';
$rightToken ='",';
// $leftToken = "hospitalizations";
// $rightToken = "var content";
$dailyCumulativePath = "data/daily-cumulative.json";
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



// AFTER 4/4/23
// LA County's website now is JSON based rather than manually edited in javascript js file's case numbers. 
/*
function get_sandwiched_inner_text($view_source, $leftToken, $rightToken) {
	// look for a string left of the number
	$a = strpos($view_source, $leftToken);
	// look for a string right of the number:
	$b = strpos($view_source, $rightToken, $a);
	// extract the string inbetween
	$partial = substr($view_source, $a, $b-$a);
	// remove all html tags because their attributes may contain numbers
	$partial = preg_replace("/<.*?>/m", "", $partial);
	// then extract only the numbers
	$partial = preg_replace("/[^0-9]{1,}/m", "", $partial);
	return $partial;
} 
*/

function get_from_json($source, $key) {
	$val = $source[$key];
	return $val;
}

// Get today's cumulative cases
$view_source = get_view_source($source);

// After 4/4
// $todaysCumulativeCases = get_sandwiched_inner_text($view_source, $leftToken, $rightToken);
$json = json_decode($view_source, true);
$todaysCumulativeCases = get_from_json($json, "hospitalizations");
// var_dump($json);
// die();
// var_dump(["debug_parsed"=>$todaysCumulativeCases]);
$todaysCumulativeCases = intval($todaysCumulativeCases);

// var_dump($view_source);
if($todaysCumulativeCases===0) die();

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
	echo sprintf("<script>alert(\"%s\");</script>", "Updating LA County's hospitalized cases...");
}

// Success
echo json_encode(["success"=>"Cron job ran to get today's cumulative *hospitalized* cases from LA County Public Health, and then appended to the daily hospitalized cumulative cases for the app.", "php.time"=>date("m/d/Y H:i:s"), "php.timezone"=>date_default_timezone_get()]);
die();

echo "<br/><br/>";
echo "Today's date: $todaysDate";
echo "<br/>";
echo "Todays cumulative cases: $todaysCumulativeCases";
echo "<br/>";
echo "<a href='$dailyCumulativePath'>Json daily cumulative cases</a>";
die();

?>