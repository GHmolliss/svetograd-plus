<?php

namespace App;

use App\TarifController;

final class API {

    public function __construct() {
        $this->route($_SERVER['REQUEST_URI']);
    }

    public function route ($uri) {
        $controller = new TarifController;

        $method = $_SERVER['REQUEST_METHOD'];
        if ($method === 'PUT' and preg_match("/^\/users\/([\d]+)\/services\/([\d]+)\/tarif$/", $uri, $matches)) {
            $put = file_get_contents('php://input');
            $put = json_decode($put);

            $controller->action_set_tarif((int) $put->tarif_id, (int) $matches[1], (int) $matches[2]);
        }
        elseif ($method === 'GET' and preg_match("/^\/users\/([\d]+)\/services\/([\d]+)\/tarifs$/", $uri, $matches)) {
            $controller->action_get_tarifs((int) $matches[1], (int) $matches[2]);
        }
/*
        elseif ($method === 'GET' and preg_match("/^\/put$/", $uri)) {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => 'http://skynet.ru/users/1/services/1/tarif',
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 30,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "PUT",
                CURLOPT_POSTFIELDS => '{ "tarif_id": 2 }',
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json",
                    "cache-control: no-cache"
                ),
            ));

            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);

            if ($err) {
                echo "cURL Error #:" . $err;
            } else {
                echo $response;
            }
        }
*/
        else
            return $controller->page();
    }
}
