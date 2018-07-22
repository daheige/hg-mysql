<?php
/**
 * 将当前环境转换为字符串
 */
function env_str()
{
    switch (true) {
        case PRODUCTION:
            return 'production';
        case STAGING:
            return 'staging';
        case TESTING:
            return 'testing';
        default:
            return 'local';
    }
}

if (!function_exists('get_config')) {
    /**
     * 加载配置文件数据
     *     get_config('database')
     *     get_config('database.default.adapter')
     *
     * @param  string  $name
     * @return mixed
     */
    function get_config($name, $value = null)
    {
        static $info = [];
        $name_hash   = md5($name);
        if (array_key_exists($name_hash, $info)) {
            return $info[$name_hash];
        }
        if (strpos($name, '.') !== false) {
            $arr = explode('.', $name);
            //优先从环境目录读取,最后从Conf目录下读取
            $filename = CONF_PATH . '/' . env_str() . '/' . $arr[0] . '.php';
            if (!is_file($filename)) {
                $filename = CONF_PATH . '/' . $arr[0] . '.php';
                if (!is_file($filename)) {
                    $info[$name_hash] = $value;
                    return $info[$name_hash];
                }
            }
            //缓存文件内容，防止反复导入
            $filename_hash = md5($filename);
            if (!isset($info[$filename_hash])) {
                $info[$filename_hash] = include $filename;
            }
            $config = $info[$filename_hash];
            if (count($arr) == 2) {
                $info[$name_hash] = array_key_exists($arr[1], $config) ? $config[$arr[1]] : $value;
                return $info[$name_hash];
            }
            if (count($arr) == 3) {
                $secondArr        = array_key_exists($arr[1], $config) ? $config[$arr[1]] : [];
                $info[$name_hash] = array_key_exists($arr[2], $secondArr) ? $secondArr[$arr[2]] : $value;
                return $info[$name_hash];
            }
            $info[$name_hash] = null;
            return $info[$name_hash];
        }
        //读取整个文件内容
        //优先从环境目录读取,最后从Conf目录下读取
        $filename = CONF_PATH . '/' . env_str() . '/' . $name . '.php';
        if (!is_file($filename)) {
            $filename = CONF_PATH . '/' . $name . '.php';
            if (!is_file($filename)) {
                $info[$name_hash] = $value;
                return $info[$name_hash];
            }
        }
        //缓存文件内容，防止反复导入
        $filename_hash = md5($filename);
        if (!isset($info[$filename_hash])) {
            $info[$filename_hash] = include $filename;
        }
        $info[$name_hash] = $info[$filename_hash];
        return $info[$name_hash];
    }
}
