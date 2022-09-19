<?php
/**
 * Created by PhpStorm.
 * User: guanghua
 * Date: 2016/4/25
 * Time: 17:25
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class PortalcategoryModel{
    private $_data;
    private $_db;
    public function __construct($data)
    {
        $this->_data = $data;
        $this->_db = DbUtils::createDbUtils(true);
    }

    /**
     * 插入数据
     */
    public function insert()
    {
        return $this->_db->insert('portal_category',$this->_data,true);
    }
    /**
     * 修改数据
     */
    public function update()
    {

    }
    /**
     * 删除数据
     */
    public function del()
    {
        $return = array();
        $catids = explode(',',$this->_data['catids']);
        foreach($catids as $v){
            $return[] = $this->_db->delete('portal_category',array('catid'=>$v));
        }
        return $return;
    }
    /**
     * 获得列表
     */
    public function getlist()
    {
        $where = '1=1';
        $str = ($this->_data['page'] - 1)*$this->_data['pageSize'];
        foreach($this->_data as $k => $v){
            if($v){
                switch($k){
                    case 'catids':
                        $where .= " AND `catid` IN ($v)";
                        break;
                    case 'catname':
                        $where .= " AND `catname` LIKE '$v'";
                        break;
                    case 'type':
                        $v == 'PUB' ? ($where .= ' AND `disallowpublish`=0') : '';
                        break;
                }
            }
        }
        $sql = 'SELECT * FROM %t WHERE '.$where.' ORDER BY `catid` LIMIT '.$str.','.$this->_data['pageSize'];
        return $this->_db->queryAll($sql,array('portal_category'));
    }
}