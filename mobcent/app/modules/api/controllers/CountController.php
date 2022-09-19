<?php
/**
 * 获得统计数据
 * @author tanguanghua <18725648509@163.com>
 **/
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class CountController extends ApiController {
    public function actionIndex() {
        $sql = 'SELECT COUNT(0) FROM %t WHERE regdate>%d';
        $sqlone = 'SELECT COUNT(0) FROM %t';
        $sqltwo = 'SELECT COUNT(0) FROM %t WHERE dateline>%d AND `first`=0';
        $data['totalposts'] = 0;
        $data['todayposts'] = 0;
        $form_data = $this->db->queryAll('SELECT threads,posts,todayposts FROM %t',array('forum_forum'));
        $todaymem = $this->db->queryRow($sql,array('common_member',TIMESTAMP - 86400));
        $totalmem = $this->db->queryRow($sqlone,array('common_member'));
        $todaypost = $this->db->queryRow($sqltwo,array('forum_post',TIMESTAMP - 86400));
        $data['todaymem'] = $todaymem['COUNT(0)'];
        $data['totalmem'] = $totalmem['COUNT(0)'];
        $data['todayhf'] = $todaypost['COUNT(0)'];
        foreach ($form_data as $v){
            $data['totalposts'] += $v['posts'];
            $data['todayposts'] += $v['todayposts'];
        }
        $this->out_arr['body'] = $data;
    }
    public function actionTest(){
        $data = $this->db->queryAll('SELECT * FROM %t LIMIT 10',array('forum_post'));
        $this->out_arr['body'] = $data;
    }
    protected function setRules()
    {
        return array(
            'test' => array(
                'tantan' => 'phone'
            ),
        );
    }

}