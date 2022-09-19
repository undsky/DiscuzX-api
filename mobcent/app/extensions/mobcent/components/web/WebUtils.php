<?php

/**
 * 网络工具类 
 *
 * @author NaiXiaoXin
 * @author  谢建平 <jianping_xie@aliyun.com>  
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class WebUtils {

    const USER_AGENT = 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_9_1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.63 Safari/537.36';

    public static function initWebApiArray() {
        return array(
            'head' => array(
                'errCode' => '00000000',
                'errInfo' => self::t('调用成功,没有任何错误'),
                'version' => MOBCENT_VERSION,
                'alert' => 0,
            ),
            'body' => array(
                'externInfo' => array('padding' => ''),
            ),
        );
    }

    public static function initWebApiArray_oldVersion() {
        $res = WebUtils::initWebApiArray();
        $res = array_merge(array('rs' => 1, 'errcode' => ''), $res);
        return $res;
    }

    public static function initWebApiResult() {
        return array(
            'errCode' => 0,
            'errMsg' => '',
            'version' => MOBCENT_VERSION,
            'data' => self::getWebApiResPadding(false),
        );
    }

    public static function makeWebApiResult($res, $code = 0, $msg = '') {
        $res['errCode'] = $code;
        $res['errMsg'] = $msg;
        return $res;
    }

    public static function makeErrorInfo($res, $message, $params = array()) {
        $errInfo = explode(':', Yii::t('mobcent', $message, $params), 2);
        if (count($errInfo) == 1) {
            $errInfo[1] = $errInfo[0];
            $errInfo[0] = isset($params['noError']) && $params['noError'] > 0 ? MOBCENT_ERROR_NONE : MOBCENT_ERROR_DEFAULT;
        }
        $res['head']['errCode'] = !empty($errInfo[0]) ? $errInfo[0] : '';
        $res['head']['errInfo'] = !empty($errInfo[1]) ? WebUtils::emptyHtml($errInfo[1]) : '';
        $res['head']['alert'] = isset($params['alert']) ? (int) $params['alert'] : 1;

        return $res;
    }

    public static function getWebApiResPadding($isNull = true, $key = 'padding', $value = '') {
        return $isNull ? null : array($key => $value);
    }

    public static function checkError($res) {
        return $res['head']['errCode'] !== MOBCENT_ERROR_NONE;
    }

    public static function makeErrorInfo_oldVersion($res, $message, $params = array()) {
        $res = WebUtils::makeErrorInfo($res, $message, $params);
        $res['rs'] = isset($params['noError']) && $params['noError'] > 0 ? 1 : 0;
        $res['errcode'] = $res['head']['errInfo'];
        return $res;
    }

    public static function endAppWithErrorInfo($res, $message, $params = array()) {
        $tmpRes = WebUtils::initWebApiArray_oldVersion();
        $tmpRes = WebUtils::makeErrorInfo_oldVersion($tmpRes, $message, $params);
        $tmpRes = array_merge($tmpRes, $res);
        WebUtils::outputWebApi($tmpRes);
    }

    public static function outputWebApi($res, $charset = '', $exit = true) {
        $res = WebUtils::jsonEncode($res, $charset);
        $res = html_entity_decode($res, ENT_NOQUOTES | ENT_HTML401, 'utf-8');
        $res = (string) str_replace('&quot;', '\"', $res);
        if (!$exit) {
            return $res;
        } else {
            echo $res;
            Yii::app()->end();
        }
    }

    public static function t($string, $charset = '') {
        $charset == '' && $charset = Yii::app()->charset;
        $charset = strtoupper($charset);
        if(mb_detect_encoding($string,'UTF-8,ASCII,GB2312,GBK',true) == 'UTF-8'){
            $string = iconv('UTF-8', $charset.($charset == 'UTF-8' ? '' : '//IGNORE'), $string);
        }
        return $string;
    }
    public static function tarr(&$data)
    {
        if(is_array($data))
        {
            foreach($data as $k => $v)
            {
                if(is_array($v))
                {
                    self::tarr($data[$k]);
                }
                if(is_string($v))
                {
                    $data[$k] = self::t($v);
                }
            }
        }else
        {
            if(is_string($data))
            {
                $data = self::t($data);
            }
        }
        return $data;
    }
    public static function detect_encoding($file) {
        $list = array('GBK', 'UTF-8', 'UTF-16LE', 'UTF-16BE', 'ISO-8859-1');
        $str = $file;
        foreach ($list as $item) {
            $tmp = mb_convert_encoding($str, $item, $item);
            if (md5($tmp) == md5($str)) {
                return $item;
            }
        }
        return null;
    }
    public static function u($string, $charset = '') {
        $charset == '' && $charset = Yii::app()->charset;
        if(!mb_detect_encoding($string,'UTF-8',true)){
            $string = iconv(strtoupper($charset), 'UTF-8', $string);
        }
        return $string;
    }
    public static function uarr(&$data)
    {
        if(is_array($data))
        {
            foreach($data as $k => $v)
            {
                if(is_array($v))
                {
                    self::uarr($data[$k]);
                }
                if(is_string($v))
                {
                    $data[$k] = self::u($v);
                }
            }
        }else
        {
            if(is_string($data))
            {
                $data = self::u($data);
            }
        }
        return $data;
    }

    /**
     * 进行国际化（页面使用）
     */
    public static function lp() {
        $varArray = func_get_args();
        $message = "";
        $i = 0;
        $params = array();
        foreach ($varArray as $var) {
            if ($i == 0) {
                $message = $var;
            } else if ($i % 2 == 0) {
                $params["{" . $varArray[$i - 1] . "}"] = WebUtils::u($varArray[$i]);
            }
            $i++;
        }
        return Yii::t('mobcentPage', $message, $params);
    }

    public static function getWebApiArrayWithPage($res, $page, $pageSize, $count) {
        $res['body']['hasNext'] = ($count > $page * $pageSize) && $page > 0 ? 1 : 0;
        $res['body']['count'] = (int) $count;
        return $res;
    }

    /**
     * 返回兼容老版本的分页格式
     */
    public static function getWebApiArrayWithPage_oldVersion($page, $pageSize, $count, $res = array()) {
        $res['page'] = (int) $page;
        $res['has_next'] = $count > $page * $pageSize ? 1 : 0;
        $res['total_num'] = (int) $count;

        return $res;
    }

    public static function createUrl_oldVersion($route, $params = array()) {
        $params = array_merge(array(
            'sdkVersion' => MOBCENT_VERSION,
            'accessToken' => isset($_GET['accessToken']) ? $_GET['accessToken'] : '',
            'accessSecret' => isset($_GET['accessSecret']) ? $_GET['accessSecret'] : '',
            'apphash' => isset($_GET['apphash']) ? $_GET['apphash'] : '',
                ), $params
        );
        return Yii::app()->createAbsoluteUrl($route, $params);
    }

    public static function jsonEncode($var, $charset = '') {
        $oldCharset = Yii::app()->charset;
        if ($charset != '') {
            Yii::app()->charset = $charset;
        }

        $res = CJSON::encode($var);

        Yii::app()->charset = $oldCharset;

        return $res;
    }

    public static function jsonDecode($str, $useArray = true) {
        return CJSON::decode($str, $useArray);
    }

    public static function subString($str, $start, $length = 100, $charset = '') {
        $charset != '' or $charset = Yii::app()->charset;
        return mb_substr($str, $start, $length, $charset);
    }

    public static function replaceLineMark($str) {
        $str = str_replace("\r", '\\r', $str);
        $str = str_replace("\n", '\\n', $str);
        return $str;
    }

    public static function emptyReturnLine($str, $replace = '') {
        $str = str_replace("\r", $replace, $str);
        $str = str_replace("\n", $replace, $str);
        return $str;
    }

    public static function emptyHtml($str, $charset = '', $transBr = false) {
        // $charset != '' or $charset = Yii::app()->charset;
        if ($transBr) {
            $str = str_replace('<br>', "\r\n", $str);
            $str = str_replace('<br />', "\r\n", $str);
        }
        $str = preg_replace('/<.*?>/', '', $str);
        $str = str_replace('&nbsp;', ' ', $str);
        return $str;
    }

    public static function parseXmlToArray($xmlString) {
        $res = array();
        if (($xml = simplexml_load_string($xmlString)) !== false) {
            $res = self::_transSimpleXMLElementToArray($xml);
        }
        return $res;
    }

    /**
     * 把相对url转换成绝对地址
     * 
     * @param string $url
     * @return string
     */
    public static function getHttpFileName($url) {
        if (substr($url, 0, 5) == 'http:') {
            strpos($url, 'www.') === 0 && $url = 'http://' . $url;
            return strpos($url, 'http') === 0 || $url == '' ? $url : Yii::app()->getController()->dzRootUrl . '/' . $url;
        } elseif (substr($url, 0, 5) == 'https') {
            strpos($url, 'www.') === 0 && $url = 'https://' . $url;
            return strpos($url, 'https') === 0 || $url == '' ? $url : Yii::app()->getController()->dzRootUrl . '/' . $url;
        } else {
            strpos($url, 'www.') === 0 && $url = 'http://' . $url;
            return strpos($url, 'http') === 0 || $url == '' ? $url : Yii::app()->getController()->dzRootUrl . '/' . $url;
        }
    }

    public static function getResByCurlWithPost($url, $postData) {
        $res = false;
        if (($ch = curl_init()) !== false) {
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => $postData,
                CURLOPT_USERAGENT => self::USER_AGENT,
            ));
            $res = curl_exec($ch);
            curl_close($ch);
        }
        return $res;
    }

    public static function httpRequest($url, $timeout = 20, $postData = array()) {
        return self::httpRequestByDiscuzApi($url, $postData, 'URLENCODE', array(), $timeout);
        // if (function_exists('curl_init')) {
        //     return self::getContentByCurl($url, $timeout);
        // } else {
        //     return self::getContentByFileGetContents($url, $timeout);
        // }
    }

    /**
     * 网络请求接口
     * 
     * @param string $url url 地址
     * @param array $postData post的数据, $fileData不为空时,$postData填入$fileData相对应key的文件内容
     * @param string $encodeType url编码, $fileData不为空时请改为''
     * @param int $timeout 超时时间, 0为无限制
     *
     * @return string 
     */
    public static function httpRequestByDiscuzApi($url, $postData = array(), $encodeType = 'URLENCODE', $fileData = array(), $timeout = 20) {
        Mobcent::import(MOBCENT_APP_ROOT . '/components/discuz/source/function/function_filesock.php');
        return mobcent_dfsockopen($url, $postData, $encodeType, $fileData, $timeout);
    }

    public static function httpRequestAppAPI($route, $params = array(), $timeout = 20) {
        $url = WebUtils::createUrl_oldVersion($route, $params);
        return WebUtils::httpRequest($url, $timeout);
    }

    public static function getContentByFileGetContents($url, $timeout = 20) {
        $context = stream_context_create(array(
            'http' => array(
                'method' => 'GET',
                'user_agent' => self::USER_AGENT,
                'follow_location' => 1,
                'timeout' => $timeout,
            ),
        ));
        return file_get_contents($url, false, $context);
    }

    public static function getContentByCurl($url, $timeout = 15) {
        $res = false;
        if (($ch = curl_init()) !== false) {
            curl_setopt_array($ch, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                // CURLOPT_HEADER => false,
                CURLOPT_TIMEOUT => $timeout,
                CURLOPT_FRESH_CONNECT => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_USERAGENT => self::USER_AGENT,
            ));
            $res = curl_exec($ch);
            curl_close($ch);
        }
        return $res;
    }

    public static function doAppAPNsHelper($uid, $payload, $timeout = 10, $charset = '') {
        $res = false;

        $deviceToken = AppbymeUserSetting::getUserDeviceToken($uid);
        $passphrase = AppbymeConfig::getAPNsCertfilePassword();
        $config = WebUtils::getMobcentConfig('misc');
        $certfile = $config['apnsCertfilePath'] . '/' . $config['apnsCertfileName'];

        if (file_exists($certfile) && $uid && $deviceToken && $passphrase && $payload) {
            $res = WebUtils::doAPNs($certfile, $passphrase, $deviceToken, $payload, $timeout, $charset);
        }

        return $res;
    }


    public static function doAPNs($localCertFile, $passphrase, $deviceToken, $payload, $timeout = 10, $charset = '') {
        // https://developer.apple.com/library/ios/documentation/NetworkingInternet/Conceptual/RemoteNotificationsPG/Chapters/ApplePushService.html#//apple_ref/doc/uid/TP40008194-CH100-SW9
        $res = false;

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', $localCertFile);
        stream_context_set_option($ctx, 'ssl', 'passphrase', $passphrase);

        $url = 'ssl://gateway.push.apple.com:2195';
        // $url = 'ssl://gateway.sandbox.push.apple.com:2195'; // test

        if ($fp = stream_socket_client($url, $err, $errstr, $timeout, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx)) {
            stream_set_timeout($fp, $timeout);

            $payload = WebUtils::jsonEncode($payload, $charset);
            $msg = chr(0) . pack('n', 32) . pack('H*', $deviceToken) . pack('n', strlen($payload)) . $payload;
            fwrite($fp, $msg, strlen($msg)) && $res = true;

            fclose($fp);
        }

        return $res;
    }

    public static function getDzPluginAppbymeAppConfig($key = '') {
        global $_G;
        loadcache('plugin');
        $cacheConfig = $_G['cache'][MOBCENT_DZ_PLUGIN_ID];
        $config = isset($_G['cache']['plugin'][MOBCENT_DZ_PLUGIN_ID]) ? $_G['cache']['plugin'][MOBCENT_DZ_PLUGIN_ID] : array();
        is_array($config) && is_array($cacheConfig) && $config = array_merge($cacheConfig, $config);

        if ($key == '') {
            return $config;
        } else {
            return isset($config[$key]) ? $config[$key] : false;
        }
    }


    public static function getMobcentConfig($key = '') {
        return $key == '' ? Yii::app()->params['mobcent'] : Yii::app()->params['mobcent'][$key];
    }

    public static function getMobcentPhizMaps() {
        $phizMaps = array();
        foreach (self::getMobcentConfig('phiz') as $key => $value) {
            $phizMaps[WebUtils::t($key)] = $value;
        }
        return $phizMaps;
    }

    public static function transMobcentPhiz($string, $prefixTag = '[img]', $suffixTag = '[/img]') {
        global $tempPhizs;
        $tempPhizs = array(WebUtils::getMobcentPhizMaps(), $prefixTag, $suffixTag);
        $string = preg_replace_callback(
                '/\[.*?\]/', create_function('$matches', '
                global $tempPhizs;
                list($phizMaps, $prefixTag, $suffixTag) = $tempPhizs;
                $phiz = $matches[0];
                if (!empty($phizMaps[$phiz])) {
                    $phiz = $prefixTag.WebUtils::getHttpFileName("mobcent/app/data/phiz/default/".$phizMaps[$phiz]).$suffixTag;
                }
                return $phiz;
            '), $string
        );
        return $string;
    }

    private static function _transSimpleXMLElementToArray($element) {
        if ($element instanceof SimpleXMLElement) {
            $arr = (array) $element;
            foreach ($arr as $key => $value) {
                $arr[$key] = self::_transSimpleXMLElementToArray($value);
            }
            return $arr;
        } else {
            return $element;
        }
    }

    /**
     * 移除Emoji标签 ByNxx Data:20151104
     * Copy Form http://stackoverflow.com/questions/12807176/php-writing-a-simple-removeemoji-function#
     */
    public static function removeEmoji($text) {
        $clean_text = "";
        // Match Emoticons
        $regexEmoticons = '/[\x{1F600}-\x{1F64F}]/u';
        $clean_text = preg_replace($regexEmoticons, '', $text);
        // Match Miscellaneous Symbols and Pictographs
        $regexSymbols = '/[\x{1F300}-\x{1F5FF}]/u';
        $clean_text = preg_replace($regexSymbols, '', $clean_text);
        // Match Transport And Map Symbols
        $regexTransport = '/[\x{1F680}-\x{1F6FF}]/u';
        $clean_text = preg_replace($regexTransport, '', $clean_text);
        // Match Miscellaneous Symbols
        $regexMisc = '/[\x{2600}-\x{26FF}]/u';
        $clean_text = preg_replace($regexMisc, '', $clean_text);
        // Match Dingbats
        $regexDingbats = '/[\x{2700}-\x{27BF}]/u';
        $clean_text = preg_replace($regexDingbats, '', $clean_text);
        return $clean_text;
    }

    public static function getLength($string, $charset = '')
    {
        $charset == '' && $charset = Yii::app()->charset;
        return mb_strlen($string, $charset);
    }

    public static function getSubstr($string, $begin = 0, $length = 1, $charset = '')
    {
        $charset == '' && $charset = Yii::app()->charset;
        return mb_substr($string, $begin, $length, $charset);
    }

    /**
     * 生成图片
     * @param $vcodes
     */
    public static function getImg($vcodes)
    {
        Header("Content-type: image/PNG");
        $im = imagecreate(44, 18);
        $back = ImageColorAllocate($im, 245, 245, 245);

        imagefill($im, 0, 0, $back); //背景
        srand((double) microtime() * 1000000);
        for ($i = 0; $i < 4; $i++) {
            $font = ImageColorAllocate($im, rand(100, 255), rand(0, 100), rand(100, 255));
            imagestring($im, 5, 2 + $i * 10, 1, $vcodes[$i], $font);
        }
        for ($i = 0; $i < 100; $i++) { //加入干扰象素
            $randcolor = ImageColorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
            imagesetpixel($im, rand(0), rand(255), $randcolor);
        }
        ImagePNG($im);
        ImageDestroy($im);
        exit();
    }
}
