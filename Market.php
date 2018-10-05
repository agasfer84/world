<?php


class Market
{

    public function setMarketPositions($balances){
        //return $balances;

        $reserves_keys = ["goods_balance" => "goods_reserv", "food_balance" => "food_reserv", "metal_balance" => "metal_reserv", "oil_balance" => "oil_reserv", "building_materials_balance" => "building_materials_reserv"];

        $positions = [];

        foreach ($balances as $balance) {

            foreach ($balance as $key => $value) {

                if ($key == "id" || $key == "name" || $key =='budget' || $key =='energy_balance' || $key =='building_materials_balance' || $key =='production' || $key =='consumption' || $key =='reserves') continue;

                $with_reserv_value = $value + $balance["reserves"][$reserves_keys[$key]];

                if($with_reserv_value == 0) continue;

                if($with_reserv_value < 0) {
                    $position = "buy";
                } else {
                    $position = "sale";
                }

                $positions[] = ["country_name" => $balance["name"], "country_budget" => $balance["budget"], "product_type" => $key, "product_value" => $with_reserv_value, "position_type" => $position, "country_id" => $balance["id"], "uniqid" => uniqid()];
            }

        }

        return $positions;
    }

    public function setPrices($world_production_groupped){
        $food_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["food_balance"]))* $world_production_groupped["k_food_deficite"]);
        $goods_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["goods_balance"]))* $world_production_groupped["k_goods_deficite"]);
        $energy_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["energy_balance"]))* $world_production_groupped["k_energy_deficite"]);
        $metal_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["metal_balance"]))* $world_production_groupped["k_metal_deficite"]);
        $oil_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["oil_balance"]))* $world_production_groupped["k_oil_deficite"]);
        $building_materials_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["building_materials_balance"]))* $world_production_groupped["k_building_materials_deficite"]);

        if($world_production_groupped["food_balance"]>0 || !$food_price){
            $food_price = 1;
        }

        if($world_production_groupped["goods_balance"]>0 || !$goods_price){
            $goods_price = 1;
        }

        if($world_production_groupped["energy_balance"]>0 || !$energy_price){
            $energy_price = 1;
        }

        if($world_production_groupped["metal_balance"]>0 || !$metal_price){
            $metal_price = 1;
        }
        if($world_production_groupped["oil_balance"]>0 || !$oil_price){
            $oil_price = 1;
        }
        if($world_production_groupped["building_materials_balance"]>0 || !$building_materials_price){
            $building_materials_price = 1;
        }

        $energy_minimal_price = round(0.1 * $oil_price);

        $energy_price = ($energy_price > $energy_minimal_price) ? $energy_price : $energy_minimal_price;

        $metal_minimal_price = round(0.4 * $energy_price);

        $metal_price = ($metal_price > $metal_minimal_price) ? $metal_price : $metal_minimal_price;

        $goods_minimal_price = round(0.1 * $metal_price + 10 * $energy_price);

        $goods_price = ($goods_price > $goods_minimal_price) ? $goods_price : $goods_minimal_price;

        $prices = ["food" => $food_price, "goods" => $goods_price, "energy" => $energy_price, 'metal' =>$metal_price, 'oil' => $oil_price, 'building_materials' => $building_materials_price];

        return $prices;
    }

    public function setMarketDeals($positions, $prices){

        $products_keys = ["goods_balance" => "goods", "food_balance" => "food", "energy_balance" => "energy", "metal_balance" => "metal", "oil_balance" => "oil", "building_materials_balance" => "building_materials"];

        $deals = [];

        foreach ($positions as $position) {

            if ($position["position_type"] == "buy" && $search_positions = $this->searchPosition($positions, $position)) {
                $cummulative_product_value[$position["uniqid"]] = 0;

                foreach ($search_positions AS $search_position) {
                    $product_value = ($search_position["product_value"] <= abs($position["product_value"])) ? $search_position["product_value"] : abs($position["product_value"]);

                    if ($cummulative_product_value[$position["uniqid"]] >= abs($position["product_value"])) continue;

                    $product_price = $prices[$products_keys[$position["product_type"]]];
                    $value_cost = $product_value * $product_price;

                    if ($value_cost >= $position["country_budget"]) {
                        $product_value = floor($position["country_budget"] / $product_price);
                        $value_cost = $product_value * $product_price;
                    }

                    $cummulative_product_value[$position["uniqid"]] += $product_value;

                    $status = (($value_cost <= $position["country_budget"]) && ($position["country_budget"] > 0)) ? "accept" : "reject";

                    $deals[] = [
                        "status" => $status,
                        "buyer_name" => $position["country_name"],
                        "product_type" => $position["product_type"],
                        "product_value" => abs($product_value),
                        "product_price" => $product_price,
                        "value_cost" => abs($value_cost),
                        "saler_id" => $search_position["country_id"],
                        "saler_name" => $search_position["country_name"],
                        "buyer_budget" => $position["country_budget"],
                        "buyer_id" => $position["country_id"]

                    ];
                }


            }
        }

        return $deals;
    }

    public function searchPosition($positions, $position) {

        $search_positions = [];

        foreach ($positions as $search_position) {

            if ($position["product_type"] == $search_position["product_type"] && $search_position["position_type"] == "sale") {
                $search_positions[] = $search_position;
            }
        }

        return $search_positions;
    }

    public function getWorldPositions($positions)
    {
        $world_positions = [];

        foreach ($positions as $position) {
            $world_positions[$position["product_type"]] += $position["product_value"];
        }

        return $world_positions;
    }

    public static function getPrices(){
        $prices = ["food" => 20, "oil" => 10, "energy" => 10];

        return $prices;
    }

}