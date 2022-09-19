<?php
/**
 * Created by PhpStorm.
 * User: guanghua
 * Date: 2016/4/26
 * Time: 14:41
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}
class PortalarticleModel{
    private $_data;
    private $_db;
    private $_error;

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
        if(isset($this->_data['hash'])) {
            $res = $this->HashCheck();
            if($res) {
               return $this->setError('文章已存在');
            }
        }

        $count = count($this->_data['json']);
        $this->_data['dateline'] = isset($this->_data['dateline']) ? $this->_data['dateline'] : time();
        $portal_title = $this->_getIrtData('title');
        $portal_title['contents'] = $count;
        if($this->_data['aid']){
            $this->_db->update('portal_article_title',$portal_title,array('aid'=>$this->_data['aid']));
            $aid = $this->_data['aid'];
        }else{
            $upid = $this->_db->queryScalar('SELECT `aid` FROM %t ORDER BY `aid` DESC LIMIT 1',array('portal_article_title'));
            $portal_title['preaid'] = $upid;
            $aid = $this->_db->insert('portal_article_title',$portal_title,true);
            $this->_db->query('UPDATE %t SET `nextaid`=%d WHERE `aid`=%d',array('portal_article_title',$aid,$upid));
        }
        $this->_db->query("UPDATE %t SET `articles`=(`articles`+1),`lastpublish`=".time().' WHERE `catid`='.$this->_data['catid'],array('portal_category'));
        $this->_db->insert('portal_article_count',array('aid'=>$aid, 'catid'=>$this->_data['catid'], 'viewnum'=>1));
        for($i = 0;$i < $count;$i++){
            $istdata = array(
                'aid' => $aid,
                'title' => $this->_data['json'][$i]['title'],
                'content' => $this->_data['json'][$i]['text'],
                'pageorder' => $i+1,
                'dateline' => $this->_data['dateline']
            );
            $this->_db->insert('portal_article_content',$istdata);
        }
    }
    private function _dhtml(){
        foreach($this->_data as $k => $v){
            if($k != 'json'){
                $v = str_replace(array('&', '"', '<', '>'), array('&amp;', '&quot;', '&lt;', '&gt;'), $v);
                if(strpos($v, '&amp;#') !== false) {
                    $v = preg_replace('/&amp;((#(\d{3,5}|x[a-fA-F0-9]{4}));)/', '&\\1', $v);
                }
                if(stripos($k,'url') !== false){
                    $v = str_replace('&amp;', '&', $v);
                    $v = substr($v, 0, 7) !== 'http://' ? '' : $v;
                }
                $this->_data[$k] = $v;
            }
        }
    }

    /**
     * @param string $type 插入数据类型：title表示
     */
    private function _getIrtData($type = 'title'){
        $return = array();
        $this->_dhtml();
        if($type == 'title'){
            $style = implode('|',$this->_getdata('highlight_style','string'));
            $return = array(
                'title' => $this->_getdata('title','string'),
                'author' => $this->_getdata('author','string'),
                'from' => $this->_getdata('from','string'),
                'fromurl' => $this->_getdata('fromurl','string'),
                'dateline' => $this->_data['dateline'],
                'url' => $this->_getdata('url','string'),
                'allowcomment' => $this->_getdata('allowcomment'),
                'summary' => $this->_getdata('summary','string'),
                'catid' => $this->_getdata('catid'),
                'tag' => $this->_article_make_tag($this->_getdata('tag','string')),
                'status' => 0,  //是否需要处理，0-已审核 1-需要审核 2-已忽略
                'highlight' => $style,
                'showinnernav' => $this->_getdata('showinnernav'),
                'pic' => $this->_getdata('pic'),
                'thumb' => 0,
                'remote' => 1,
            );
            if(!$this->_data['aid']){
                $htmlname = basename(trim($this->_getdata('htmlname','string')));
                $return['uid'] = 1;
                $return['username'] = 'admin';
                $return['htmlmade'] = $this->_getdata('htmlmade');
                $return['htmldir'] = $this->_getdata('htmldir','string');
                $return['htmlname'] = $htmlname;
            }
        }
        return $return;
    }
    private function _getdata($k,$tp = 'int'){
        return isset($this->_data[$k]) ? $this->_data[$k] : ($tp == 'int' ? 0 : '');
    }
    private function _article_make_tag($tags) {
        $tags = (array)$tags;
        $tag = 0;
        for($i=1; $i<=8; $i++) {
            if(!empty($tags[$i])) {
                $tag += pow(2, $i-1);
            }
        }
        return $tag;
    }


    /**
     * 文章去重复
     * @return bool
     */
    protected function HashCheck()
    {
        $res = $this->_db->queryFirst('SELECT text FROM %t WHERE text = %s',array('appbyme_hash', $this->_data['hash']));
        if($res) {
            return true;
        }
        $this->_db->insert('appbyme_hash',array('text' => $this->_data['hash']));
        return false;
    }

    protected function setError($msg)
    {
        $this->_error = $msg;
        return false;
    }

    public function getError()
    {
        return $this->_error;
    }
}