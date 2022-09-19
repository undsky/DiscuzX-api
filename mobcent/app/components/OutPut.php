<?php
/**
 * Created by PhpStorm.
 * User: tantan
 * Date: 16/6/16
 * Time: 11:29
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')){
    exit('Access Denied');
}
class OutPut
{
    /**
     * @var array echo data
     */
    private static $_outdata = array();

    /**
     * echo json data
     */
    public static function EchoJson()
    {
        $TmpOutData = self::TmpData();
        self::_outeonv(self::$_outdata);
        $TmpOutData['body'] = self::$_outdata;
        echo json_encode($TmpOutData);
        exit;
    }

    /**
     * @param $data 传入数据
     */
    public static function SetData($data)
    {
        foreach ($data as $k => $v) {
            self::$_outdata[$k] = $v;
        }
    }

    /**
     * @param $erid 错误id
     */
    public static function Error($erid)
    {
        $TmpOutData = self::TmpData();
        if (ctype_digit($erid)) {
            $TmpOutData['errcode'] = $erid;
            $TmpOutData['head']['errInfo'] = Yii::t('error',$erid);
        } else {
            $TmpOutData['errcode'] = 1;
            $TmpOutData['head']['errInfo'] = $erid;
        }
        echo json_encode($TmpOutData);
        exit;
    }

    public static function TmpData()
    {
        return array(
            'rs' => 1,
            'errcode' => 0,
            'head' => array(
                'errInfo' => '成功'
            ),
            'body' => array()
        );
    }
    private function _outeonv(&$data)
    {
        if(is_array($data))
        {
            foreach($data as $k => $v)
            {
                if(is_array($v))
                {
                    $this->_outeonv($data[$k]);
                }
                if(is_string($v))
                {
                    $data[$k] = WebUtils::u($v);
                }
            }
        }else
        {
            if(is_string($data))
            {
                $data = WebUtils::u($data);
            }
        }
    }
}