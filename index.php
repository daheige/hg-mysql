<?php
define('ROOT_PATH', __DIR__);
define('CONF_PATH', ROOT_PATH . '/conf');
define('LIB_PATH', ROOT_PATH . '/library');
include_once CONF_PATH . '/constants.php';
include_once LIB_PATH . '/helper.php';

include_once "mysql/mysqlPdo.php";
include_once LIB_PATH . "/Mem.class.php";
$id  = 1000 + mt_rand(0, 99000);
$sql = "select id,name,age from big_data where id = " . $id;

$pdo = mysqlPdo::getInstance(get_config('database.db.default'));

var_dump($pdo->getOne('select * from big_data where id = 1'));

$mem = new Mem; //php7这里发生了改变
                // var_dump($mem);die;
$mem->addServer("127.0.0.1", 11211);

$mem->set('key', 'This is a test!', 0);
$val = $mem->get('key');
echo $val;

$mem->cache('daheige', 123, 0);
echo "<br/>";
echo $mem->cache('daheige');

print_r($mem->getVersion());
