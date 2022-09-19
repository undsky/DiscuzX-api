<?php
/**
 * User: 蒙奇·D·jie
 * Date: 16/9/26
 * Time: 下午6:05
 * Email: mqdjie@gmail.com
 */

class RepairService
{
    private    $db;
    private    $id;
    private  $data;
    private $error;
    private $diyId;

    public function __construct()
    {
        $this->db    = DbUtils::createDbUtils(true);
        $this->data  = WebUtils::tarr($_GET);
        $this->id    = $this->data['id'];
        $this->diyId = $this->data['id'];
    }


    public function Repair()
    {
        $arr = array();
        while($this->id >= 1) {
            $diyId            = $this->id - 1;
            $arr['Nav']       = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_nav_info_0_'.$diyId));
            $arr['Module']    = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_modules_0_'.$diyId));
            $arr['tmpNav']    = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_nav_info_temp_0_'.$diyId));
            $arr['tmpModule'] = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_modules_temp_0_'.$diyId));

            if(!((empty($arr['Nav']) && empty($arr['tmpNav'])) || (empty($arr['Module']) && empty($arr['tmpModule'])))) {
                foreach($arr as  $k => $v) {
                    switch($k) {
                        case 'Nav':
                            if(empty($v)) {
                                $arr[$k] = $arr['tmpNav'];
                            }
                            $arr[$k]['ckey'] = 'app_uidiy_nav_info_0_'.$this->diyId;
                            break;
                        case 'tmpNav':
                            if(empty($v)) {
                                $arr[$k] = $arr['Nav'];
                            }
                            $arr[$k]['ckey'] = 'app_uidiy_nav_info_temp_0_'.$this->diyId;
                            break;
                        case 'Module':
                            if(empty($v)) {
                                $arr[$k] = $arr['tmpModule'];
                            }
                            $arr[$k]['ckey'] = 'app_uidiy_modules_0_'.$this->diyId;
                            break;
                        case 'tmpModule':
                            if(empty($v)) {
                                $arr[$k] = $arr['Module'];
                            }
                            $arr[$k]['ckey'] = 'app_uidiy_modules_temp_0_'.$this->diyId;
                            break;
                        default :
                            break;
                    }
                    $this->db->insert('appbyme_config',$arr[$k], false, true);
                }
                return true;
            }
            $this->id--;
        }
        return $this->setError('数据有误');
    }

    private function setError($msg)
    {
        $this->error = $msg;
        return false;
    }
    public function getError()
    {
        return $this->error;
    }

    private function SaveOld()
    {
        $arr['Nav']       = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_nav_info_0_'.$this->diyId));
        $arr['Module']    = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_modules_0_'.$this->diyId));
        $arr['tmpNav']    = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_nav_info_temp_0_'.$this->diyId));
        $arr['tmpModule'] = $this->db->queryRow('SELECT * FROM %t WHERE ckey = %s', array('appbyme_config', 'app_uidiy_modules_temp_0_'.$this->diyId));
        $res['ckey']      = 'RepairOld';
        $res['cvalue']    = serialize($arr);
        $this->db->insert('appbyme_config',$res, false, true);
    }
}