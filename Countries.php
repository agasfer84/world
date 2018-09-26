<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dbconnect.php";

class Countries
{
    const AGRO_FARM_PRODUCTION = 5000;
    const FACTORY_PRODUCTION = 10000;
    const ENERGY_STATION_PRODUCTION = 50000000;
    const OIL_PLANT_PRODUCTION = 1;
    const BUILDING_PLANT_PRODUCTION = 1;

    const POPULATION_FARM_CONSUMPTION = 1;
    const POPULATION_GOODS_CONSUMPTION = 1;
    const POPULATION_ENERGY_CONSUMPTION = 25;
    const POPULATION_OIL_CONSUMPTION = 1;
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
        $goods_consumption = $country["population"] * self::POPULATION_GOODS_CONSUMPTION;
        $energy_consumption = $country["population"] * self::POPULATION_ENERGY_CONSUMPTION;
        $oil_consumption = $country["population"] * self::POPULATION_OIL_CONSUMPTION;
        $building_materials_consumption = 0;

        $result = [
            "food_consumption" => $food_consumption,
            "goods_consumption" => $goods_consumption,
            "energy_consumption" => $energy_consumption,
            "oil_consumption" => $oil_consumption,
            "building_materials_consumption" => $building_materials_consumption,
        ];

        return $result;
    }

    public function getCountryProduction($country) {
        $food_production = $country["agro_farm"] * self::AGRO_FARM_PRODUCTION;
        $goods_production = $country["factory"] * self::FACTORY_PRODUCTION;
        $energy_production = $country["energy_station"] * self::ENERGY_STATION_PRODUCTION;
        $oil_production = $country["oil_plant"] * self::OIL_PLANT_PRODUCTION;
        $building_materials_production = $country["building_plant"] * self::BUILDING_PLANT_PRODUCTION;

        $result = [
            "food_production" => $food_production,
            "goods_production" => $goods_production,
            "energy_production" => $energy_production,
            "oil_production" => $oil_production,
            "building_materials_production" => $building_materials_production,
        ];

        return $result;
    }

    public function getCountryProductBalance($production, $consumption) {
        $food_balance = $production["food_production"] - $consumption["food_consumption"];
        $goods_balance = $production["goods_production"] - $consumption["goods_consumption"];
        $energy_balance = $production["energy_production"] - $consumption["energy_consumption"];
        $oil_balance = $production["oil_production"] - $consumption["oil_consumption"];
        $building_materials_balance = $production["building_materials_production"] - $consumption["building_materials_consumption"];

        $result = [
            "food_balance" => $food_balance,
            "goods_balance" => $goods_balance,
            "energy_balance" => $energy_balance,
            "oil_balance" => $oil_balance,
            "building_materials_balance" => $building_materials_balance,

        ];

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
        $goods_value = $country_balance["goods_balance"];
        $metal_value = $country_balance["metal_balance"];
        $oil_value = $country_balance["oil_balance"];
        $building_materials_value = $country_balance["building_materials_balance"];

        $query = "UPDATE countries 
        SET food_reserv=food_reserv+:food_value, 
        goods_reserv=goods_reserv+:goods_value, 
        metal_reserv=metal_reserv+:metal_value, 
        oil_reserv=oil_reserv+:oil_value, 
        building_materials_reserv=building_materials_reserv+:building_materials_value
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

    public function getWorldProduction() {
        $country_list = $this->getCountryList();
        $countries_production = [];

        foreach ($country_list as $country) {
            $consumption = $this->getCountryConsumption($country);
            $production = $this->getCountryProduction($country);
            $balance = $this->getCountryProductBalance($production, $consumption);
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
            $balances[] = array_merge(["id" => $country["id"]], ["name" => $country["name"]], ["production" => $production], ["consumption" => $consumption],  $this->getCountryProductBalance($production, $consumption));
        }

        return $balances;
    }



}