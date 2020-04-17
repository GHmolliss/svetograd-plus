<?php

namespace App;

final class TarifModel extends Model {

    private $result = [
        'result' => 'error',
    ];

    public function get_tarifs($user_id, $service_id) {
        $tarif = $this->user_select_tarif($user_id, $service_id);
        if (!$tarif)
            return $this->result;

        $this->db->select('`ID`, `title`, `price`, `speed`, `pay_period`');
        $this->db->from('`tarifs`');
        $this->db->where('`tarif_group_id`', $tarif['tarif_group_id']);
        $this->db->orderby('`pay_period`');
        $tarifs = $this->db->query_all();

        $tarif['speed'] = (int) $tarif['speed'];
        unset($tarif['tarif_id'], $tarif['pay_period'], $tarif['tarif_group_id']);
        $this->result['tarifs'][] = $tarif;
        foreach ($tarifs as &$tarif) {
            $tarif['ID']         = (int) $tarif['ID'];
            $tarif['price']      = (float) $tarif['price'];
            $tarif['speed']      = (int) $tarif['speed'];
            $tarif['new_payday'] = strtotime("today +{$tarif['pay_period']} months") . '+0300';
            //$tarif['test'] = date('d.m.Y H:i:s', strtotime("today +{$tarif['pay_period']} months"));
        }
        $this->result['result'] = 'ok';
        $this->result['tarifs'][0]['tarifs'] = $tarifs;

        return $this->result;
    }

    public function set_tarif($tarif_id, $user_id, $service_id) {
        $tarif = $this->user_select_tarif($user_id, $service_id);
        if (!$tarif)
            return $this->result;

        // Тариф пользователя = переданному тарифу
        if ($tarif['tarif_id'] == $tarif_id)
            return $this->result;

        // Есть ли такой тариф и может ли пользователь выбрать данный тариф?
        if (!$this->validate_tarif_id_and_group_id($tarif_id, $tarif['tarif_group_id']))
            return $this->result;

        $this->db->where('`ID`', $service_id);
        $this->db->update('services', ['tarif_id' => $tarif_id, 'payday' => date('Y-m-d', strtotime("today +{$tarif['pay_period']} months"))]);

        $this->result['result'] = 'ok';
        return $this->result;
    }

    private function user_select_tarif($user_id, $service_id) {
        if (!$user_id || !$service_id)
            return false;

        $this->db->select('S.`tarif_id`, T.`title`, T.`link`, T.`speed`, T.`pay_period`, T.`tarif_group_id`');
        $this->db->from('`services` S');
        $this->db->join('`tarifs` T', 'S.`tarif_id` = T.`ID`');
        $this->db->where('S.`user_id` = ? AND S.`ID` = ?', [$user_id, $service_id]);
        return $this->db->query_one();
    }

    private function validate_tarif_id_and_group_id($tarif_id, $tarif_group_id) {
        if (!$tarif_id || !$tarif_group_id)
            return false;

        $this->db->select('`ID`, `title`, `price`, `speed`, `pay_period`');
        $this->db->from('`tarifs`');
        $this->db->where('`ID` = ? AND `tarif_group_id` = ?', [$tarif_id, $tarif_group_id]);
        return $this->db->query_one();
    }
}