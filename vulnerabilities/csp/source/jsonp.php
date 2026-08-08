<?php
header("Content-Type: application/json; charset=UTF-8");

// The callback name is not taken from user input; an attacker controlled
// callback turns a JSONP endpoint into arbitrary script execution.
$callback = "solveSum";

$outp = array ("answer" => "15");

echo $callback . "(".json_encode($outp).")";
?>
