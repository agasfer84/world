<?php


class Market
{

    public function setMarketPositions($balances){
        //return $balances;

        $positions = [];

        foreach ($balances as $balance) {

            foreach ($balance as $key => $value) {

                if ($key == "id" || $key == "name" || $key =='energy_balance' || $key =='building_materials_balance' || $key =='production' || $key =='consumption') continue;

                if($value == 0) continue;

                if($value < 0) {
                    $position = "buy";
                } else {
                    $position = "sale";
                }

                $positions[] = ["country_id" => $balance["id"], "country_name" => $balance["name"], "product_type" => $key, "product_value" => $value, "position_type" => $position];
            }

        }

        return $positions;
    }

    public function setPrices($world_production_groupped){
        $food_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["food_balance"]))* $world_production_groupped["k_food_deficite"]);
        $goods_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["goods_balance"]))* $world_production_groupped["k_goods_deficite"]);
        $energy_price = round(($world_production_groupped["budget"]/abs($world_production_groupped["energy_balance"]))* $world_production_groupped["k_energy_deficite"]);

        if($world_production_groupped["food_balance"]>0){
            $food_price = 1;
        }

        if($world_production_groupped["goods_balance"]>0){
            $goods_price = 1;
        }

        if($world_production_groupped["energy_balance"]>0){
            $energy_price = 1;
        }

        $prices = ["food" => $food_price, "goods" => $goods_price, "energy" => $energy_price];

        return $prices;
    }

    public function setMarketDeals($positions){
        $deals = [];

        foreach ($positions as $position) {

            if ($position["position_type"] == "buy" && $search_position = $this->searchPosition($positions, $position)) {
                $deals[] = [
                    "buyer_id" => $position["country_id"],
                    "buyer_name" => $position["country_name"],
                    "product_type" => $position["product_type"],
                    "product_value" => (abs($search_position["product_value"])>= $position["product_value"]) ? $search_position["product_value"] : abs($position["product_value"]),
                    "saler_id" => $search_position["country_id"],
                    "saler_name" => $search_position["country_name"],
                ];
            }
        }

        return $deals;
    }

    public function getWorldPositions($positions)
    {
        $world_positions = [];

        foreach ($positions as $position) {
            $world_positions[$position["product_type"]] += $position["product_value"];
        }

        return $world_positions;
    }

    public function searchPosition($positions, $position) {

        foreach ($positions as $search_position) {

            if ($position["product_type"] == $search_position["product_type"] && $search_position["position_type"] == "sale") {
                return $search_position;
            }
        }

        return false;
    }

    public static function getPrices(){
        $prices = ["food" => 20, "oil" => 10, "energy" => 10];

        return $prices;
    }

}