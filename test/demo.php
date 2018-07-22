<?php
/*系统类*/
$m = new Memcached();
/*添加服务器*/
$m->addServer('127.0.0.1', 11211);
/*添加多台服务器*/
$array = [
    ['127.0.0.1', 11211], ['127.0.0.1', 11211],
];
$m->addServers($array);
/*查看服务器状态*/
print_r($m->getStats());
/*查看服务端版本号*/
print_r($m->getVersion());
/*数据类*/
$m->add('mkey', 'mvalue', 0);
//第三个参数是数据存在的时间，0表示永久
echo $m->get('mkey');
//获取数据
/*假设对同一个key值进行两次添加的话，后边的操作不会覆盖前边的value，如果想替换掉，就使用replace*/
$m->replace('mkey', 'mvalue2');
/*还可以使用set方法表添加数据，它的好处就是，当我们的数据不存在时会帮我们新建数据，如果存在，就会覆盖原值*/
$m->add('mkey', 'mvalue', 600);
/*删除数据*/
$m->delete('mkey');
/*清空memcache中的所有缓存*/
$m->flush();
/*对memcache中整形数据进行+1或+任意数值的操作*/
$m->set('num', 5, 0);    //第三个参数是过期时间,0是永久不过期
$m->increment('num', 5); //每次刷新页面，num自增5
$m->get('num');
/*自减decrement用法相同*/
/*下边的方法只支持Memcached，不支持Memcache*/
/*一次添加多条数据*///原始方法,多次使用set()
//现在可以使用setMulti()
$data = ['key' => 'value', 'key1' => 'value1'];
$m->setMulti($data, 0);
$result = $m->getMulti(['key', 'key1']); //获取多条数据
print_r($result);
//删除多条数据
$m->deleteMulti(['key', 'key1']);
//返回上一次操作返回的编码(数字的形式存在) 可以到手册中查看每一个编码的含义
echo $m->getResultCode(); //比如  成功  返回0

//获取操作结果
echo $m->getResultMessage(); //比如  成功  返回SUCCESS
