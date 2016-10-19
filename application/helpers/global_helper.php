<?php

if (!function_exists('app_config')) {
    /**
     * 获取系统配置参数
     * @param string $key
     * @return mixed
     */
    function app_config($key = null)
    {
        static $_api_config;
        if ($_api_config) {
            if ($key == null) {
                return $_api_config;
            }
            if (isset($_api_config[$key])) {
                return $_api_config[$key];
            }
        }
        $m = M('config');
        $list = $m->fetAll();
        $_api_config = array();
        foreach ($list as $row) {
            $_api_config[$row['key']] = $row['val'];
        }

        if ($key == null) {
            return $_api_config;
        }
        if (isset($_api_config[$key])) {
            return $_api_config[$key];
        }
        return false;
    }
}

//读取XML $xml = join("",file($filePath));
if (!function_exists('xml_to_array')) {
    function xml_to_array($xml)
    {
        $reg = "/<(\\w+)[^>]*?>([\\x00-\\xFF]*?)<\\/\\1>/";
        if (preg_match_all($reg, $xml, $matches)) {
            $count = count($matches[0]);
            $arr = array();
            for ($i = 0; $i < $count; $i++) {
                $key = $matches[1][$i];
                $val = xml_to_array($matches[2][$i]); // 递归
                if (array_key_exists($key, $arr)) {
                    if (is_array($arr[$key])) {
                        if (!array_key_exists(0, $arr[$key])) {
                            $arr[$key] = array($arr[$key]);
                        }
                    } else {
                        $arr[$key] = array($arr[$key]);
                    }
                    $arr[$key][] = $val;
                } else {
                    $arr[$key] = $val;
                }
            }
            return $arr;
        } else {
            return $xml;
        }
    }
}


if (!function_exists('zh_json_encode')) {
    function zh_json_encode($data)
    {
        if (defined('JSON_UNESCAPED_UNICODE')) {
            $ret = json_encode($data, JSON_UNESCAPED_UNICODE);
        } else {
            $ret = json_encode($data);
//            function utf8_json_encode($arr)
//            {
//                array_walk_recursive($arr, function (&$item, $key) {
//                    if (is_string($item)) $item = mb_encode_numericentity($item, array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
//                });
//                return mb_decode_numericentity(json_encode($arr), array(0x80, 0xffff, 0, 0xffff), 'UTF-8');
//            }
//
//            $ret = utf8_json_encode($data);
        }
        return $ret;
    }
}

if (!function_exists('guid')) {
    /**
     * 生成GUID {xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx}
     * @return string
     */
    function guid()
    {
        $guid = '';
        if (function_exists('com_create_guid')) {
            $guid = com_create_guid();
        } else {
            mt_srand((double)microtime() * 10000);
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hypen = chr(45); //"-"
            $guid = chr(123) //"{"
                . substr($charid, 0, 8) . $hypen
                . substr($charid, 8, 4) . $hypen
                . substr($charid, 12, 4) . $hypen
                . substr($charid, 16, 4) . $hypen
                . substr($charid, 20, 12)
                . chr(125);
        }

        return $guid;
    }


}
if (!function_exists('uuid')) {
    function uuid()
    {
        $guid = guid();
        return trim($guid, '{}');
    }
}


if (!function_exists('checkmobile')) {
    /**
     * 检查电话号码格式正确
     * @param $phone
     * @return int
     */
    function checkMobile($phone)
    {
        return preg_match('/^((\(\d{2,3}\))|(\d{3}\-))?1[3458]\d{9}$/', $phone);
    }
}


if (!function_exists('zh_pathinfo')) {
    /**
     * 处理utf8 解决路径中含中文,pathinfo被忽略
     * @param $filepath
     * @return array
     */
    function zh_pathinfo($filepath)
    {
        $ret = array();
        $tarr = explode('/', $filepath);

        $ret['basename'] = array_pop($tarr);
        $ret['dirname'] = join('/', $tarr);
        $tarr = explode('.', $ret['basename']);
        $ret['extension'] = array_pop($tarr);
        $ret['filename'] = join('.', $tarr);
        unset($tarr);
        return $ret;
    }
}

if (!function_exists('authCode')) {
    /**
     * discuz加密解密
     * @param $string
     * @param string $operation
     * @param string $key
     * @param int $expiry
     * @return string
     */
    function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
    {

        $ckey_length = 4;

        $key = md5($key ? $key : 'authcodekey122');
        $keya = md5(substr($key, 0, 16));
        $keyb = md5(substr($key, 16, 16));
        $keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) : substr(md5(microtime()), -$ckey_length)) : '';

        $cryptkey = $keya . md5($keya . $keyc);
        $key_length = strlen($cryptkey);

        $string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) : sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(md5($string . $keyb), 0, 16) . $string;
        $string_length = strlen($string);

        $result = '';
        $box = range(0, 255);

        $rndkey = array();
        for ($i = 0; $i <= 255; $i++) {
            $rndkey[$i] = ord($cryptkey[$i % $key_length]);
        }

        for ($j = $i = 0; $i < 256; $i++) {
            $j = ($j + $box[$i] + $rndkey[$i]) % 256;
            $tmp = $box[$i];
            $box[$i] = $box[$j];
            $box[$j] = $tmp;
        }

        for ($a = $j = $i = 0; $i < $string_length; $i++) {
            $a = ($a + 1) % 256;
            $j = ($j + $box[$a]) % 256;
            $tmp = $box[$a];
            $box[$a] = $box[$j];
            $box[$j] = $tmp;
            $result .= chr(ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]));
        }

        if ($operation == 'DECODE') {
            if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) && substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)) {
                return substr($result, 26);
            } else {
                return '';
            }
        } else {
            return $keyc . str_replace('=', '', base64_encode($result));
        }
    }
}
if (!function_exists('my_encode')) {

    /**
     * @param array $arr
     * @param string $key
     * @param int $expiry
     * @return string
     */
    function my_encode($arr = array(), $key = '', $expiry = 0)
    {
        return base64_encode(authCode(base64_encode(json_encode($arr)), 'ENCODE', $key, $expiry));
    }

    /**
     * @param string $str
     * @param string $key
     * @param int $expiry
     * @return mixed
     */
    function my_decode($str = '', $key = '', $expiry = 0)
    {
        return json_decode(base64_decode(authCode(base64_decode($str), 'DECODE', $key, $expiry)), true);
    }
}
if (!function_exists('my_json_encode')) {
    function my_json_encode($arr = array())
    {
        return urldecode(json_encode(array_map('urlencode', $arr)));
    }
}