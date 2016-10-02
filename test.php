<?php

// var_dump($_SERVER);

$str = '&nbsp;'.'あいうえお';
$str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
echo $str;