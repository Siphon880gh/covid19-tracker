<?php

// MOST RECENT PROB: php -f endpoint.php won't run the ajaxes
// possible solutions:
// https://www.google.com/search?q=cron+job+run+webpage+that+has+ajax&oq=cron+job+run+webpage+that+has+ajax&aqs=chrome..69i57.5631j0j4&sourceid=chrome&ie=UTF-8
// https://stackoverflow.com/questions/35421921/execute-php-ajax-call-using-cron-jobs

/**
 * Problem: CNN does not let you directly download the table because it's dynamically replaced with a json (refer to url). I used cURL on
 * the json but it returns raw binary data. I couldn't convert that data to text using raw binary converters. Json actually returns if you
 * do fetch or ajax but that's not in PHP anymore, so the logic here is from JS => PHP.
 * 
 * json file found from inspecting https://www.cnn.com/interactive/2020/health/coronavirus-us-maps-and-cases/
 * json file is at https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/data.json
 * 
 * To build the historic cumulative cases, you visit this other json file
 * Historics is here: https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/covid19-historical-by-state.json
 */

if(!isset($_GET["next"])) {
	echo json_encode(["error"=>"First step missing. We run node process endpoint.js to pull the information from CNN, and then put the information here to endpoint.php"]);
	die();
}

// Init
$dailyCumulativePath = "data/daily-cumulative.json";
error_reporting(E_ALL ^ E_DEPRECATED);

// Get today's cumulative cases
$todaysCumulativeCases = intval($_GET["next"]);

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
	echo sprintf("<script>alert(\"%s\");</script>", "Updating California's cumulative cases...");
}

// Success
echo json_encode(["success"=>"Cron job ran to get today's cumulative cases from CNN, and then appended to the daily cumulative cases for the app.", "php.time"=>date("m/d/Y H:i:s"), "php.timezone"=>date_default_timezone_get()]);
die();

echo "<br/><br/>";
echo "Today's date: $todaysDate";
echo "<br/>";
echo "Todays cumulative cases: $todaysCumulativeCases";
echo "<br/>";
echo "<a href='$dailyCumulativePath'>Json daily cumulative cases</a>";
die();

?>