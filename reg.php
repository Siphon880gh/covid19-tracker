<?php
// TODO: var arr = $(".js-table").first().find("tr td:nth-child(2)").toArray().reverse().map(el=>parseInt(el.innerText));
// http://localhost:8888/wff/tools/covid19/reg.php?y_points=[0,1,2,3]

    if(isset($_GET["y_points"]) && $_GET["y_points"]!=="") {
        // sanitize
        $y_points = $_GET["y_points"];
        $y_points = preg_replace("/[^0-9,]/", "", $y_points);

        $output = "";
        exec('node reg.js "' . $y_points . '"', $output);
        $formulas = $output[0]; // exec keeps the results in an array at 0th index
        echo $formulas;
    }
?>