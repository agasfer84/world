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

                $positions[] = [$balance["id"], $balance["name"], $key, $value, $position];
            }

        }

        return $positions;
    }

    public static function getPrices(){
        $prices = ["food" => 20, "oil" => 10, "energy" => 10];

        return $prices;
    }

}