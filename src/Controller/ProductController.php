<?php

namespace App\Controller;

use App\Library\Controller;
use App\Library\Http\Response;
use App\Library\Database;

class ProductController extends Controller
{

    private $db = null;

    public function __construct()
    {
        $this->db = new Database();
    }

    public function new(array $params)
    {
        $vParams = [
            "sku" => "JNDFUIH",
            "name" => "Personal PC",
            "price" => 20.50,
            "created_at" => $this->db->Now()
        ];
        $resultOfInsert = $this->db->insert(Database::TABLE_PRODUCTS, $vParams);
        $result = [];
        if ($resultOfInsert > 0) {
            $result["status"] = true;
            $result["result"] = $resultOfInsert;
        } else {
            $result["status"] = false;
            $result["result"] = $resultOfInsert;
        }
        Response::JSON($result);
    }

    /**
     * Return product list in JSON forrmat
     *
     * @param array $params
     * @return void
     */
    public function list(array $params)
    {
        // Select products with option 1
        //$products = $this->db->querySelect("SELECT t.* FROM ".Database::TABLE_PRODUCTS." t ORDER BY t.id DESC");
        // Select products with option 2
        $products = $this->db->select(Database::TABLE_PRODUCTS, [], 100, "ORDER BY t.id DESC", "t.*", false);
        $_products = [];
        foreach ($products as $item) {
            $product_types = $this->db->select(Database::TABLE_PRODUCT_TYPES, ["product_id" => $item["id"]]);
            foreach ($product_types as $type) {
                $typeName = $this->db->select(Database::TABLE_LIST_TYPES, ["id" => $type["list_product_type_id"]], 1, "", "t.*", true);
                $item["attributes"][] = [
                    "id" => $type["id"],
                    "name" => $typeName["name"],
                    "value" => $type["value"]
                ];
            }
            $_products[] = $item;
        }
        $result = [
            "status" => true,
            "result" => $_products
        ];
        Response::JSON($result);
    }

    public function view(array $params)
    {
        Response::JSON($params);
    }
}
