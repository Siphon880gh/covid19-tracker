<?php
if (!empty($argv[1])) {
	parse_str($argv[1], $_GET); // some_var=12&some_other_var=14 => $_GET["some_var"]=12; $_GET["some_other_var"]=14;
}

/**
 * John Hopkins university reopened state reporting. CNN refers to them now.
 * 
 */

// Init
// Daily reports are new files named by the date. So we build the URL with the current date. 
date_default_timezone_set("America/Los_Angeles");
$todaysDate = date("m-d-Y", time()); // ##/##/####
$source = "https://raw.githubusercontent.com/CSSEGISandData/COVID-19/master/csse_covid_19_data/csse_covid_19_daily_reports_us/$todaysDate.csv";
$dailyCumulativePath = "data/daily-cumulative.json";
// echo $source; die();
error_reporting(E_ALL ^ E_DEPRECATED);

// Get today's cumulative cases

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
	$partial = preg_replace("/[^0-9,]{1,}/m", "", $partial);
	return $partial;
} 

function getCommaPositionText($partial, $pos) {
	$items = explode(",", $partial);
	// var_dump($items); die();
	$extracted = "0"; // default
	$extracted = $items[$pos];
	return intval($extracted);
}

function getTodaysCumulativeCases() {
	global $source;
	$ch = curl_init();
	$timeout = 5;
	curl_setopt($ch, CURLOPT_URL, $source);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
	$data = curl_exec($ch);
	curl_close($ch);

	$text = $data; // ...New York,US,###,###,...California,US,###,###,...
	$ny_partial = get_sandwiched_inner_text($text, "New York,US", "USA"); // ###,###,...
	// var_dump($partial); die();
	
	$ny_cases = getCommaPositionText($ny_partial, 5); // 5th value on a csv line is cases
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
date_default_timezone_set("America/Los_Angeles"); // Dup allowed because this is a boilerplate
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
echo json_encode(["success"=>"Cron job ran to get today's cumulative cases from John Hopkins University, and then appended to the daily cumulative cases for the app.", "php.time"=>date("m/d/Y H:i:s"), "php.timezone"=>date_default_timezone_get(), "parsed.\$todaysCumulativeCases"=>$todaysCumulativeCases, "php.source"=>$source]);
die();

echo "\n\n\n";
echo "Today's date: $todaysDate";
echo "\n";
echo "Todays cumulative cases: $todaysCumulativeCases";
echo "\n";
echo "<a href='$dailyCumulativePath'>Json daily cumulative cases</a>";
die();

?>