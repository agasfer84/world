<?php


class Market
{

    public function setMarketPositions($balances){

        $positions = [];

        foreach ($balances as $balance) {

            foreach ($balance as $key => $value) {

                if ($key == "id" || $key == "name") continue;

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

    public function setMarketDeals($positions){
        $deals = [];

        foreach ($positions as $position) {

            if ($position["position_type"] == "buy" && $search_position = $this->searchPosition($positions, $position)) {
                $deals[] = [
                    "buyer_id" => $position["country_id"],
                    "buyer_name" => $position["country_name"],
                    "product_type" => $position["product_type"],
                    "product_value" => $search_position["product_value"],
                    "saler_id" => $search_position["country_id"],
                    "saler_name" => $search_position["country_name"],
                ];
            }
        }

        return $deals;
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