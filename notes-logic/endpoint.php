<?php
$ls_filenames = [];
$prefix_path = "../notes-data/";
$overrides = []; // keyValue pairs: json filename and json content

$cmd = sprintf("cd $prefix_path; ls *.json");
$stdout = exec($cmd, $ls_filenames);

if(count($ls_filenames)) {
  for($i=0; $i<count($ls_filenames); $i++) {
    $filename = $ls_filenames[$i];
    $keyarea = $filename;
    $str = file_get_contents($prefix_path . $filename);
    $obj = json_decode($str, true);
    $str2 = $obj["note"];
    $notes[$keyarea] = $str2;
  } // for
}

// Final result: $notes
if(count($notes))
  echo json_encode($notes); // Props {note: string}

?>