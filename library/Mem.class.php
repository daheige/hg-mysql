<?php
class Mem
{
    private $client;
    private $time  = 0; //过期时间,默认不过期
    private $error = '';
    private $debug = 'true';

    public function __construct()
    {
        $this->client = new Memcached;
    }

    public function cache($key, $value = '', $time = null)
    {
        $number = func_num_args(); //该函数用来判断传递过来了几个参数
        if ($number == 1) {
            return $this->get($key);
        } else if ($number >= 2) {
            if ($value === null) { //一定要使用“全等于”，因为如果不是全等于，传递过来0也会通过
                $this->delete($key);
            } else {
                $this->set($key, $value, $time);
            }
        }
    }

    public function set($key, $value, $time = null)
    {
        if ($time === null) {
            $time = $this->time;
        }

        $this->client->set($key, $value, $time);
        //getResultCode 结果状态码
        if ($this->debug && $this->client->getResultCode() != 0) {
            return false;
        }

        return true;
    }

    public function get($key)
    {
        $result = $this->client->get($key);
        if ($this->debug && $this->client->getResultCode() != 0) {
            return false;
        }

        return $result;
    }

    public function delete($key)
    {
        $this->client->delete($key);
    }

    public function getError()
    {
        if ($this->error) {
            return $this->error();
        } else {
            return $this->client->getResultMessage();
        }
    }

    //禁止清空memcache中的所有缓存,防止所有的cache失效
    public function flush()
    {
        // $this->client->flush();
        return true;
    }

    /**
     * 透明地调用Memcached其它操作方法
     * 比如addServer,addServers,getStats,replace等
     * 用来支持memcached命令调用
     * @param  string  $name
     * @param  array   $params
     * @return mixed
     */
    public function __call($name, $params)
    {
        return call_user_func_array([$this->client, $name], $params);
    }

}
