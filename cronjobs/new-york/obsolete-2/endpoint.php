<?php
if (!empty($argv[1])) {
	parse_str($argv[1], $_GET); // some_var=12&some_other_var=14 => $_GET["some_var"]=12; $_GET["some_other_var"]=14;
}

/**
 * Problem/Solution: CNN does not let you directly download the table because it's dynamically replaced with a json (refer to json url). I used cURL on
 * the json but it returns gzip data. So I am adding a new layer of code that unzips the gzip for json.
 * 
 * Quality control:
 * The CNN website that displays the json file: https://www.cnn.com/interactive/2020/health/coronavirus-us-maps-and-cases/
 * json file is at https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/data.json
 * 
 * To build the historic cumulative cases, you visit this other json file from CNN:
 * Recent Historics is here: https://ix.cnn.io/data/novel-coronavirus-2019-ncov/us/historical.min.json
 * Older Historics is here: https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/covid19-historical-by-state.json
 * If the historics get too old, find the app.js file from https://www.cnn.com/interactive/2020/health/coronavirus-us-maps-and-cases/, then find the historical json (check all of them)
 * Note that there may be discrepancies between the CNN source and the current source based on their methods of collecting data.
 * 
 */

// Init
$source = "https://ix.cnn.io/dailygraphics/graphics/20200306-us-covid19/data.json";
$dailyCumulativePath = "data/daily-cumulative.json";
error_reporting(E_ALL ^ E_DEPRECATED);

// Get today's cumulative cases
function getTodaysCumulativeCases() {
	global $source;
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $source);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);
	$data = gzdecode($data); // unzip
	$obj_type = json_decode($data, true);
	$states = $obj_type["data"];
	$ny = [];

	for($i=0; $i<count($states); $i++) {
		$state = $states[$i];
		if($state["name"]==="New York") {
			$ny = $state;
			break;
		} // if
	} // for

	$ny_cases = 0;
	$ny_cases = @$ny["cases"];
	
	return $ny_cases;
}
$todaysCumulativeCases = getTodaysCumulativeCases();
if($todaysCumulativeCases===0) {
	echo json_encode(["error"=>"Is 0.", "php.time"=>date("m/d/Y H:i:s"), "php.timezone"=>date_default_timezone_get(), "parsed.\$todaysCumulativeCases"=>$todaysCumulativeCases]);
	die();
}

// echo $todaysCumulativeCases;
// die();

// Save today's cumulative cases to history
$hadDumped = file_get_contents($dailyCumulativePath);
$hadDumped = json_decode($hadDumped, true);
// var_dump($hadDumped);
date_default_timezone_set("America/Los_Angeles");
$todaysDate = date("n/j/y", time());
if(isset($hadDumped[$todaysDate])) unset($hadDumped[$todaysDate]); // removes duplicated entries in case cron job runs multiple times
$hadDumped[$todaysDate] = $todaysCumulativeCases;
$hadDumped = array_merge([$todaysDate=>$todaysCumulativeCases], $hadDumped); // similar to array_unshift
$prettyJson = json_encode($hadDumped, JSON_PRETTY_PRINT);
$prettyJson = str_replace("\/", "/", $prettyJson);
// var_dump($hadDumped); die();
file_put_contents($dailyCumulativePath, $prettyJson);

if(isset($_GET["manual"])) {
	echo sprintf("<script>alert(\"%s\");</script>", "Updating New York's cumulative cases...");
}

// Success
echo json_encode(["success"=>"Cron job ran to get today's cumulative cases from CNN, and then appended to the daily cumulative cases for the app.", "php.time"=>date("m/d/Y H:i:s"), "php.timezone"=>date_default_timezone_get(), "parsed.\$todaysCumulativeCases"=>$todaysCumulativeCases]);
die();

echo "\n\n\n";
echo "Today's date: $todaysDate";
echo "\n";
echo "Todays cumulative cases: $todaysCumulativeCases";
echo "\n";
echo "<a href='$dailyCumulativePath'>Json daily cumulative cases</a>";
die();

?>