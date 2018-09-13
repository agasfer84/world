<?php

require_once $_SERVER['DOCUMENT_ROOT']."/Countries.php";

$_action = $_REQUEST["action"];
$_id = $_REQUEST["id"];
$_body = file_get_contents('php://input');

$result = [];

$Countries = new Countries();
$Market = new Market();

if ($_action == "actionCountryInfo") {
    if( strtoupper($_SERVER['REQUEST_METHOD']) == "POST" ) {
        $result = false;
    } else {
        if( strtoupper($_SERVER['REQUEST_METHOD']) == "GET" ) {
            $country = $Countries->getCountryById($_id);
            $consumption = $Countries->getCountryConsumption($country);
            $production = $Countries->getCountryProduction($country);
            $balance = $Countries->getCountryProductBalance($production, $consumption);

            if (!$country || !$consumption || !$production || !$balance) {
                return false;
            }

            $result = array_merge($country, $consumption, $production, $balance);
        }
    }
}

$response = $result;

header("Content-type: application/json; charset=utf-8");
echo json_encode($response);