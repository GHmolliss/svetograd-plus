<?php

namespace App;

use App\DB;

abstract class Model {

    public $db;

    public function __construct() {
        $this->db = new DB;
    }
}
