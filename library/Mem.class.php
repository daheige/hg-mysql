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

    //有了魔术方法__call后connect和addServer都不需要实现
    /* public function connect($ip,$port){
        $this->client->connect($ip,$port);
    } */

    /*
    host               服务器域名或 IP
    port               端口号，默认为 11211
    persistent         是否使用常连接，默认为 TRUE
    weight             权重，在多个服务器设置中占的比重
    timeout          连接服务器失效的秒数，修改默认值 1 时要三思，有可能失去所有缓存方面的优势导致连接变得很慢
    retry_interval    服务器连接失败时的重试频率，默认是 15 秒一次，如果设置为 -1 将禁止自动重试，当扩展中加载了 dynamically via dl() 时，无论本参数还是常连接设置参数都会失效。
                            每一个失败的服务器在失效前都有独自的生存期，选择后端请求时会被跳过而不服务于请求。一个过期的连接将成功的重新连接或者被标记为失败的连接等待下一次 重试。这种效果就是说每一个 web server 的子进程在服务于页面时的重试连接都跟他们自己的重试频率有关。
    status             控制服务器是否被标记为 online，设置这个参数为 FALSE 并设置 retry_interval 为 -1 可以使连接失败的服务器被放到一个描述不响应请求的服务器池子中，对这个服务器的请求将失败，接受设置为失败服务器的设置，默认参数为 TRUE，代表该服务器可以被定义为 online。
    failure_callback   失败时的回调函数，函数的两个参数为失败服务器的 hostname 和 port
    */

   /*  public function addServer(){

    } */

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
