<?php

require_once $_SERVER['DOCUMENT_ROOT']."/Countries.php";
require_once $_SERVER['DOCUMENT_ROOT']."/Market.php";

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
            $balance = $Countries->getCountryProductBalance($production, $consumption, $country);

            if (!$country || !$consumption || !$production || !$balance) {
                return false;
            }

            $result = array_merge($country, $consumption, $production, $balance);
        }
    }
}

if ($_action == "actionWorldProduction") {
    $country_list = $Countries->getCountryList();
    $Countries->setAllCountriesIncome($country_list);
    $Countries->setAllCountriesReserves($country_list);

    $result  = $Countries->getWorldProduction();
}

if ($_action == "actionMarketPrices") {
    $world_production_groupped = $Countries->getWorldProduction();
    $result  = $Market->setPrices($world_production_groupped);
}

if ($_action == "actionMarketPositions") {
    $balances = $Countries->balancesToMarket();
    $result  = $Market->setMarketPositions($balances);
}

if ($_action == "actionMarketDeals") {
    $balances = $Countries->balancesToMarket();
    $positions  = $Market->setMarketPositions($balances);
    $world_production_groupped = $Countries->getWorldProduction();
    $prices = $Market->setPrices($world_production_groupped);
    $deals = $Market->setMarketDeals($positions, $prices);
    $Countries->acceptMarketDeals($deals);
    $result  = $deals;
}

if ($_action == "actionMarketWorldPositions") {
    $positions  = json_decode($_body, true);
    $result  = $Market->getWorldPositions($positions["positions"]);
}

$response = $result;

header("Content-type: application/json; charset=utf-8");
echo json_encode($response);