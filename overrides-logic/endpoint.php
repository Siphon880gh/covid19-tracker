<?php
$ls_filenames = [];
$prefix_path = "../overrides-data/";
$overrides = []; // keyValue pairs: json filename and json content

$cmd = sprintf("ls $prefix_path");
$stdout = exec($cmd, $ls_filenames);

if(count($ls_filenames)) {
  for($i=0; $i<count($ls_filenames); $i++) {
    $filename = $ls_filenames[$i];
    $str = file_get_contents($prefix_path . $filename);
    $overrides[$filename] = json_decode($str);
  } // for
}

// Final result: $overrides
if(count($overrides))
  echo json_encode($overrides);

?>