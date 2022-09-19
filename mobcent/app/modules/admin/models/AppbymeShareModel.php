<?php
/**
 *  appbyme_share_user Model
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 *
 */

class AppbymeShareModel extends DiscuzAR{

    private $_table;

    public function init(){
        parent::init();
        $this->_table = 'appbyme_share';
    }

	public function tableName() {
		return '{{appbyme_share}}';
	}

	public static function getAllByPage($page,$pageSize){
        return DbUtils::getDzDbUtils(true)->queryAll('
            SELECT *
            FROM %t
            LiMIT %d,%d
            ',
            array('appbyme_share',($page-1)*$pageSize,$pageSize)
        );
    }
    public static function countAll(){
        return DbUtils::getDzDbUtils(true)->queryScalar("SELECT count(*) AS count FROM %t",array('appbyme_share'));
    }
    public static function getInfoByIDCache($id){
        $key = CacheUtils::getShareActivityKey(array('id'=>$id));
        //缓存1小时
        $cache = array('enable' => 1, 'expire' => HOUR_SECONDS * 1);
        if (!$cache['enable'] || ($res = Yii::app()->cache->get($key)) === false) {
            $res =self::getInfoByID($id);
            if ($cache['enable']&&!empty($res)) {
                Yii::app()->cache->set($key, $res, $cache['expire']);
            }
        }
        return $res;
    }

    /**
     * 通过ID获得信息(无缓存)
     * @author NaiXiaoXin<nxx@yytest.cn>
     *
     */
    public static function getInfoByID($id){
        $info =  DbUtils::getDzDbUtils(true)->queryRow('SELECT * FROM %t WHERE id=%d',array('appbyme_share',$id));
        $return  = $info;
        if(empty($return)){
            return array();
        }
        unset($return['credit']);
        unset($return['param']);
        unset($return['type']);
        $return['credit'] = unserialize($info['credit']);
        $return['param'] = unserialize($info['param']);
        $return['type'] = explode(',',$info['type']);
        return $return;
    }

    public static function updateInfo($id,$data){
        $key = CacheUtils::getShareActivityKey(array('id'=>$id));
        Yii::app()->cache->delete($key);
        return DbUtils::getDzDbUtils(true)->update('appbyme_share',$data,array('id'=>$id));
    }

    public static function insertInfo($data){
        return DbUtils::getDzDbUtils(true)->insert('appbyme_share',$data);
    }

    public static function delActivity($id){
        return DbUtils::getDzDbUtils(true)->delete('appbyme_share',array('id'=>$id));

    }
}