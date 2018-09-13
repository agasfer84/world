<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dbconnect.php";

class Countries
{
    const AGRO_FARM_PRODUCTION = 5000;
    const POPULATION_FARM_CONSUMPTION = 1;
    const AGRO_CYCLE = 50;

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

}