<?php

namespace App;

use App\TarifModel;

final class TarifController {

    public function action_get_tarifs($user_id, $service_id) {
        $model = new TarifModel;
        $tarifs = $model->get_tarifs($user_id, $service_id);
        //echo '<pre>';
        //print_r($tarifs);
        $this->response('Content-Type: application/json', $tarifs);
    }

    public function action_set_tarif($tarif_id, $user_id, $service_id) {
        $model = new TarifModel;
        $tarif = $model->set_tarif($tarif_id, $user_id, $service_id);
        //echo '<pre>';
        //print_r($tarif);
        $this->response('Content-Type: application/json', $tarif);
    }

    public function page(int $code = 404, string $message = 'Not Found') {
        $this->response(
            "HTTP/1.1 {$code} {$message}",
            [
                'result' => 'error',
            ]
        );
    }

    public function response(string $header, array $data) {
        header($header);
        print json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        die;
    }
}
