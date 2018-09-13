<?php

require_once $_SERVER['DOCUMENT_ROOT']."/dbconnect.php";

class Countries
{

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
}