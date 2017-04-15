<?php

namespace PX;

spl_autoload_register(function ($className) {
    $name = str_replace("\\", "/", $className);
    $str = "../{$name}.php";
    include $str;
});

