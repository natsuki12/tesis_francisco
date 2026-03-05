<?php
$_ENV['DB_NAME'] = 'spdss';
require __DIR__ . '/vendor/autoload.php';
require __DIR__ . '/src/Core/DB.php';
$db = \App\Core\DB::connect();
var_dump($db->query('SELECT * FROM sim_cat_parentescos')->fetchAll(PDO::FETCH_ASSOC));
?>