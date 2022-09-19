<?php

/**
 * api控制器基类
 * @author tanguanghua <18725648509@163.com>
 */
class ApiController extends CController
{
    //输出数据
    protected $out_arr = array();
    //get,post数据
    protected $data = array();
    /**
     * @var $db DiscuzDbUtils
     */
    protected $db = null;
    //验证规则
    private $_rules = array();

    //构造函数
    public function init()
    {
        $this->out_arr = $this->out_arr();
        $this->db      = DbUtils::createDbUtils(true);
        $this->checksign();
        $this->initdata();
    }

    //验证签名
    private function checksign()
    {
        $gettime = intval($_GET['t']);
        $time    = TIMESTAMP - $gettime;
        if ($time > 5 * 60) {
            $this->error('时间校验失败，请联系管理员或者刷新重试');
        }
        $forumKey  = $_GET['forumKey'];
        $secretKey = $this->db->queryScalar('SELECT cvalue FROM %t WHERE `ckey`=%s', array('appbyme_config', 'ForumKey_SecretKey'));
        $Keys      = unserialize($secretKey);
        $in        = array_key_exists($forumKey, $Keys);
        if ($in) {
            $secretKey = $Keys[$forumKey];
        } else {
            $secretKey = $this->db->queryScalar('SELECT cvalue FROM %t WHERE `ckey`=%s', array('appbyme_config', 'secretKey'));
        }
        if (!$secretKey) {
            $this->error('签名校验失败，请联系管理员或者刷新重试');
        }
        $signStr = md5('t=' . $gettime . '&secretKey=' . $secretKey);

        if ($_GET['sign'] != $signStr) {
            $this->error('签名校验失败，请联系管理员或者刷新重试');
        }
    }

    /**
     * 输出数据
     * @param array $data 要输出的数据
     */
    protected function out_json($data = array())
    {
        header("Content-Type: application/json; charset=utf-8");
        if (empty($data))
            $data = $this->out_arr;
        echo json_encode($data);
        exit();
    }

    protected function out_arr()
    {
        return array(
            'rs'      => 1,
            'errcode' => 0,
            'head'    => array(
                'errInfo' => '成功'
            ),
            'body'    => array()
        );
    }

    protected function beforeAction($action)
    {
        $this->_rules = $this->setRules();
        if (isset($this->_rules[$action->id])) {
            Apivalidate::main($this, $action->id);
        }
        $error = Apivalidate::geterr();
        if (!empty($error)) {
            $this->error($error);
        }
        $this->data = Apivalidate::getdata();
        if (isset($this->data['pageSize']) && $this->data['pageSize'] > 50) {
            $this->error('pageSize不能大于50');
        }
        $this->data['page']     = isset($this->data['page']) ? $this->data['page'] : 1;
        $this->data['pageSize'] = isset($this->data['pageSize']) ? $this->data['pageSize'] : 10;
        return true;
    }

    protected function afterAction($action)
    {
        $this->_outeonv($this->out_arr['body']);
        $this->out_json();
    }

    public function getRules($action)
    {
        return $this->_rules[$action];
    }

    protected function setData($data)
    {
        foreach ($data as $k => $v) {
            $this->out_arr['body'][$k] = $v;
        }
    }

    protected function error($msg)
    {
        $this->out_arr['rs']              = 0;
        $this->out_arr['head']['errInfo'] = $msg;
        $this->out_json();
    }

    protected function setRules()
    {
        return array();
    }

    private function _outeonv(&$data)
    {
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                if (is_array($v)) {
                    $this->_outeonv($data[$k]);
                }
                if (is_string($v)) {
                    $data[$k] = WebUtils::u($v);
                }
            }
        } else {
            if (is_string($data)) {
                $data = WebUtils::u($data);
            }
        }
    }

    //初始化data
    protected function initdata()
    {
        global $_G;
        $_G['siteurl']  = substr($_G['siteurl'], 0, -16);
        $_G['siteroot'] = substr($_G['siteroot'], 0, -16);
    }

}
