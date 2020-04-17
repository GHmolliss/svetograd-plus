<?php

//ini_set('error_reporting',E_ALL);
//ini_set('display_errors',1);
//ini_set('display_startup_errors',1);

spl_autoload_register(function ($class_name) {
    include SG_ROOT . DIRECTORY_SEPARATOR . "{$class_name}.php";
});