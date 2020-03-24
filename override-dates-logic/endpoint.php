<?php
$ls_filenames = [];
$prefix_path = "../override-dates-data/";
$overrides = []; // keyValue pairs: json filename and json content

$cmd = sprintf("cd $prefix_path; ls *.json");
$stdout = exec($cmd, $ls_filenames);

if(count($ls_filenames)) {
  for($i=0; $i<count($ls_filenames); $i++) {
    $filename = $ls_filenames[$i];
    $keyarea = $filename;
    $str = file_get_contents($prefix_path . $filename);
    $overrides[$keyarea] = json_decode($str);
  } // for
}

// Final result: $overrides
if(count($overrides))
  echo json_encode($overrides); // Props // {area: [dateStr...]}

?>