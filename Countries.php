<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dbconnect.php";

class Countries
{
    const AGRO_FARM_PRODUCTION = 20000;
    const FACTORY_PRODUCTION = 10000;
    const ENERGY_STATION_PRODUCTION = 20000000;
    const OIL_PLANT_PRODUCTION = 150000000;
    const METAL_PLANT_PRODUCTION = 2000000;
    const BUILDING_PLANT_PRODUCTION = 1;

    const OIL_TO_ENERGY = 0.1;
    const ENERGY_TO_METAL = 0.4;
    const ENERGY_TO_GOOD = 10;
    const METAL_TO_GOOD = 0.1;
    const BARREL = 159;

    const POPULATION_FARM_CONSUMPTION = 1;
    const POPULATION_GOODS_CONSUMPTION = 1;
    const POPULATION_ENERGY_CONSUMPTION = 6;
    const POPULATION_OIL_CONSUMPTION = 14;
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
        $food_consumption = $country["population"] * $country["level"] * self::POPULATION_FARM_CONSUMPTION;
        $goods_consumption = $country["population"] * $country["level"] * self::POPULATION_GOODS_CONSUMPTION;
        $metal_consumption = round($country["factory"] * self::FACTORY_PRODUCTION * self::METAL_TO_GOOD);
        $energy_consumption = round($country["population"] * $country["level"] * self::POPULATION_ENERGY_CONSUMPTION + $metal_consumption * self::ENERGY_TO_METAL + $country["factory"] * self::FACTORY_PRODUCTION * self::ENERGY_TO_GOOD);
        $oil_consumption = round($country["population"] * $country["level"] * self::POPULATION_OIL_CONSUMPTION + $country["energy_station"] * self::ENERGY_STATION_PRODUCTION * self::OIL_TO_ENERGY);
        $building_materials_consumption = 0;

        $result = [
            "food_consumption" => $food_consumption,
            "goods_consumption" => $goods_consumption,
            "energy_consumption" => $energy_consumption,
            "metal_consumption" => $metal_consumption,
            "oil_consumption" => $oil_consumption,
            "building_materials_consumption" => $building_materials_consumption,
        ];

        return $result;
    }

    public function getCountryProduction($country) {
        $food_production = $country["agro_farm"] * self::AGRO_FARM_PRODUCTION;
        $oil_production = $country["oil_plant"] * self::OIL_PLANT_PRODUCTION;

        $oil_energy_need = round($country["energy_station"] * self::ENERGY_STATION_PRODUCTION * self::OIL_TO_ENERGY);
        $k_oil = $oil_production / $oil_energy_need;
        $k_oil = ($k_oil > 1) ? 1: $k_oil;

        $energy_production = $country["energy_station"] * self::ENERGY_STATION_PRODUCTION * $k_oil;

        $energy_product_need = round($country["metal_plant"] * self::METAL_PLANT_PRODUCTION * self::ENERGY_TO_METAL + $country["factory"] * self::FACTORY_PRODUCTION * self::ENERGY_TO_GOOD);
        $k_energy = $energy_production / $energy_product_need;
        $k_energy = ($k_energy > 1) ? 1: $k_energy;

        $goods_production = floor($country["factory"] * self::FACTORY_PRODUCTION *  $k_energy );
        $metal_production = floor($country["metal_plant"] * self::METAL_PLANT_PRODUCTION * $k_energy);

        $building_materials_production = $country["building_plant"] * self::BUILDING_PLANT_PRODUCTION;

        $result = [
            "food_production" => $food_production,
            "goods_production" => $goods_production,
            "metal_production" => $metal_production,
            "energy_production" => $energy_production,
            "oil_production" => $oil_production,
            "building_materials_production" => $building_materials_production,
        ];

        return $result;
    }

    public function getCountryReserves($country) {
        $result = [
            "goods_reserv" => $country["goods_reserv"],
            "food_reserv" => $country["food_reserv"],
            "metal_reserv" => $country["metal_reserv"],
            "oil_reserv" => $country["oil_reserv"],
            "building_materials_reserv" => $country["building_materials_reserv"],
        ];

        return $result;
    }

    public function getCountryBudget($country) {
        return $country["budget"];
    }

    public function getCountryProductBalance($production, $consumption, $country) {
        $food_balance = $production["food_production"] - $consumption["food_consumption"];
        $goods_balance = $production["goods_production"] - $consumption["goods_consumption"];
        $energy_balance = $production["energy_production"] - $consumption["energy_consumption"];
        $metal_balance = $production["metal_production"] - $consumption["metal_consumption"];
        $oil_balance = $production["oil_production"] - $consumption["oil_consumption"];
        $building_materials_balance = $production["building_materials_production"] - $consumption["building_materials_consumption"];

        $result = [
            "food_balance" => ($food_balance) ? $food_balance : -1,
            "goods_balance" => ($goods_balance) ? $goods_balance : -1,
            "energy_balance" => ($energy_balance) ? $energy_balance : -1,
            "metal_balance" => ($metal_balance) ? $metal_balance : -1,
            "oil_balance" => ($oil_balance) ? $oil_balance : -1,
            "building_materials_balance" => ($building_materials_balance) ? $building_materials_balance : -1,

        ];

        return $result;
    }

    public function calculateCountryIncome($id) {
        $country = $this->getCountryById($id);
        $income = ($country["population"] * $country["income"] * $country["level"] * ($country["income_tax"] / 100)) / self::MONTH_CYCLE;

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

        $query = "SELECT * FROM countries WHERE id = :id";
        $params = ["id" => $id];
        $stmt = $connection->prepare($query);
        $stmt->execute($params);
        $country =  $stmt->fetch(PDO::FETCH_ASSOC);

        $food_value = (($country_balance["food_balance"] + (int)$country["food_balance"]) > 0) ? $country_balance["food_balance"] + (int)$country["food_balance"] : 0;
        $goods_value = (($country_balance["goods_balance"] + (int)$country["goods_balance"]) > 0) ? $country_balance["goods_balance"] + (int)$country["goods_balance"] : 0;
        $metal_value = (($country_balance["metal_balance"] + (int)$country["metal_balance"]) > 0) ? $country_balance["metal_balance"] + (int)$country["metal_balance"] : 0;
        $oil_value = (($country_balance["oil_balance"] + (int)$country["oil_balance"]) > 0) ? $country_balance["oil_balance"]+ (int)$country["oil_balance"] : 0;
        $building_materials_value = (($country_balance["building_materials_balance"] + (int)$country["building_materials_balance"]) > 0) ? $country_balance["building_materials_balance"] + (int)$country["building_materials_balance"] : 0;

        $query = "UPDATE countries 
        SET food_reserv=:food_value, 
        goods_reserv=:goods_value, 
        metal_reserv=:metal_value, 
        oil_reserv=:oil_value, 
        building_materials_reserv=:building_materials_value
        WHERE id = :id";

        $params = ["id" => $id,
            "food_value" => $food_value,
            "goods_value" => $goods_value,
            "metal_value" => $metal_value,
            "oil_value" => $oil_value,
            "building_materials_value" => $building_materials_value
        ];

        $stmt = $connection->prepare($query);
        $stmt->execute($params);
    }

    public function setAllCountriesReserves($country_list) {

        foreach ($country_list as $country) {
            $consumption = $this->getCountryConsumption($country);
            $production = $this->getCountryProduction($country);
            $country_balance = $this->getCountryProductBalance($production, $consumption, $country);

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

    public function getWorldProduction() {
        $country_list = $this->getCountryList();
        $countries_production = [];

        foreach ($country_list as $country) {
            $consumption = $this->getCountryConsumption($country);
            $production = $this->getCountryProduction($country);
            $balance = $this->getCountryProductBalance($production, $consumption, $country);
            $countries_production[] = array_merge($consumption, $production, $balance, ["budget" => $country["budget"]]);
        }

        $world_production_groupped = [];

        foreach ($countries_production as $country_production) {
            foreach ($country_production as $key => $value) {
                $world_production_groupped[$key] += $value;
            }
        }

        $world_production_groupped["k_food_deficite"] = round($world_production_groupped["food_consumption"]/(($world_production_groupped["food_production"]!=0) ? $world_production_groupped["food_production"]  : 1), 2);
        $world_production_groupped["k_goods_deficite"] = round($world_production_groupped["goods_consumption"]/(($world_production_groupped["goods_production"]!=0) ? $world_production_groupped["goods_production"]  : 1), 2);
        $world_production_groupped["k_energy_deficite"] = round($world_production_groupped["energy_consumption"]/(($world_production_groupped["energy_production"]!=0) ? $world_production_groupped["energy_production"]  : 1), 2);
        $world_production_groupped["k_metal_deficite"] = round($world_production_groupped["metal_consumption"]/(($world_production_groupped["metal_production"]!=0) ? $world_production_groupped["metal_production"]  : 1), 2);
        $world_production_groupped["k_oil_deficite"] = round($world_production_groupped["oil_consumption"]/(($world_production_groupped["oil_production"]!=0) ? $world_production_groupped["oil_production"]  : 1), 2);
        $world_production_groupped["k_building_materials_deficite"] = round($world_production_groupped["building_materials_consumption"]/(($world_production_groupped["building_materials_production"]!=0) ? $world_production_groupped["building_materials_production"]  : 1), 2);

        return $world_production_groupped;
    }

    public function balancesToMarket () {
        $country_list = $this->getCountryList();
        $balances = [];

        foreach ($country_list as $country) {
            $consumption = $this->getCountryConsumption($country);
            $production = $this->getCountryProduction($country);
            $reserves = $this->getCountryReserves($country);

            $balances[] = array_merge(["id" => $country["id"]], ["name" => $country["name"]],["budget" => (int)$country["budget"]], ["production" => $production], ["consumption" => $consumption], ["reserves" => $reserves],  $this->getCountryProductBalance($production, $consumption, $country));
        }

        return $balances;
    }

    public function acceptMarketDeals($deals) {

        $connection = $this->db;

        $reserves_keys = ["goods_balance" => "goods_reserv", "food_balance" => "food_reserv", "metal_balance" => "metal_reserv", "oil_balance" => "oil_reserv", "building_materials_balance" => "building_materials_reserv"];

        foreach ($deals AS $deal) {

            if ($deal["status"] != "accept") continue;

            $query = "UPDATE countries SET budget=budget-:product_cost,".$reserves_keys[$deal["product_type"]]."=".$reserves_keys[$deal["product_type"]]."+:product_value WHERE id = :buyer_id;"."UPDATE countries SET budget=budget+:product_cost,".$reserves_keys[$deal["product_type"]]."=".$reserves_keys[$deal["product_type"]]."-:product_value WHERE id = :saler_id;";

            $params = ["buyer_id" => $deal["buyer_id"], "saler_id" => $deal["saler_id"], "product_cost" => $deal["value_cost"], "product_value" => $deal["product_value"]];
            $stmt = $connection->prepare($query);
            $stmt->execute($params);

        }
    }



}