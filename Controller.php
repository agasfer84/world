<?php

require_once $_SERVER['DOCUMENT_ROOT']."/Countries.php";

$_action = $_REQUEST["action"];
$_id = $_REQUEST["id"];

$result = [];

$Countries = new Countries();

if ($_action == "actionCountryInfo") {
    $result = $Countries->getCountryById($_id);
}

$response = $result;
header("Content-type: application/json; charset=utf-8");
echo json_encode($response);