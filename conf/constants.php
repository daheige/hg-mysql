<?php
// 生产环境
defined('IS_PRO') or define('IS_PRO', is_file('/etc/php.env.production'));
defined('PRODUCTION') or define('PRODUCTION', IS_PRO);
// 预发环境
defined('STAGING') || define('STAGING', is_file('/etc/php.env.staging'));
// 测试环境
defined('TESTING') || define('TESTING', is_file('/etc/php.env.testing'));
// 开发环境
defined('DEVELOPMENT') || define('DEVELOPMENT', !(IS_PRO || STAGING || TESTING));
//测试或本地环境打开调试模式，线上环境关闭
defined('APP_DEBUG') || define('APP_DEBUG', !IS_PRO);
