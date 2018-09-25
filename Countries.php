<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dbconnect.php";

class Countries
{
    const AGRO_FARM_PRODUCTION = 5000;
    const POPULATION_FARM_CONSUMPTION = 1;
    const AGRO_CYCLE = 50;
    const MONTH_CYCLE = 4;

    public function __construct()
    {
        $db = new Database();
        $this->db = $db;
    }

    public function getCountryById($id) {
        $connection = $this->db;

        $query = "SELECT * FROM countries WHERE id = :id";
        $params = ["id" => $id];
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        $result =  $stmt->fetch(PDO::FETCH_ASSOC);

        return $result;
    }

    public function getCountryConsumption($country) {
        $food_consumption = $country["population"] * self::POPULATION_FARM_CONSUMPTION;
        $result = ["food_consumption" => $food_consumption];

        return $result;
    }

    public function getCountryProduction($country) {
        $food_production = $country["agro_farm"] * self::AGRO_FARM_PRODUCTION;
        $result = ["food_production" => $food_production];

        return $result;
    }

    public function getCountryProductBalance($production, $consumption) {
        $food_balance = $production["food_production"] - $consumption["food_consumption"];
        $result = ["food_balance" => $food_balance];

        return $result;
    }

    public function calculateCountryIncome($id) {
        $country = $this->getCountryById($id);
        $income = ($country["population"] * $country["income"] * ($country["income_tax"] / 100)) / self::MONTH_CYCLE;

        return $income;
    }

    public function setCountryIncome($id, $value) {
        $connection = $this->db;

        $query = "UPDATE countries SET budget=budget+:value WHERE id = :id";
        $params = ["id" => $id, "value" => $value];
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
    }

    public function setCountryReserves($id, $country_balance) {
        $connection = $this->db;

        $food_value = $country_balance["food_balance"];

        $query = "UPDATE countries SET food_reserv=food_reserv+:food_value WHERE id = :id";
        $params = ["id" => $id, "food_value" => $food_value];
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
    }

    public function setAllCountriesReserves($country_list) {

        foreach ($country_list as $country) {
            $consumption = $this->getCountryConsumption($country);
            $production = $this->getCountryProduction($country);
            $country_balance = $this->getCountryProductBalance($production, $consumption);

            $this->setCountryReserves($country["id"], $country_balance);
        }
    }

    public function setAllCountriesIncome($country_list) {

        foreach ($country_list as $country) {
            $country_income_value = $this->calculateCountryIncome($country["id"]);
            $this->setCountryIncome($country["id"], $country_income_value);
        }
    }

    public function getCountryList() {
        $connection = $this->db;
        $query = "SELECT * FROM countries";
        $stmt = $connection->prepare($query);
        $stmt->execute();
        $result =  $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $result;
    }

    public function balancesToMarket () {
        $country_list = $this->getCountryList();
        //return $country_list;
        $balances = [];

        foreach ($country_list as $country) {
            $consumption = $this->getCountryConsumption($country);
            $production = $this->getCountryProduction($country);
            $balances[] = array_merge(["id" => $country["id"]], ["name" => $country["name"]], $this->getCountryProductBalance($production, $consumption));
        }

        return $balances;
    }



}