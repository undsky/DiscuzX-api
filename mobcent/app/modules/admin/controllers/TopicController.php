<?php
/**
 * @author tanguanghua <18725648509@163.com>
 **/
 
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class TopicController extends AdminController{
    /**
     * 话题管理
     */
    public function actionTpcmag($page = 1,$search = '') {
        echo <<<ETO
        <center style="margin-top:100px;">当前功能已迁移,请访问[<a href="http://console.apps.xiaoyun.com" target="_blank" style="color:red;text-decoration: none;">小云控制台</a>]</center>
ETO;
        exit();

        global $_G;
        $where = '';
        $pagesize = 10;
        $str = ($page-1)*$pagesize;
        $db = DbUtils::getDzDbUtils(true);
        if($search){
            $where = ' WHERE (`ti_authorname` LIKE \'%'.WebUtils::t($search).'%\' OR `ti_title` LIKE \'%'.WebUtils::t($search).'%\')';
        }
        $sql = 'SELECT * FROM %t %i ORDER BY `ti_id` DESC LIMIT %d,%d';
        $data = WebUtils::uarr($db->queryAll($sql,array('appbyme_topic_items',$where,$str,$pagesize)));
        $count = $db->queryRow('SELECT COUNT(0) FROM %t %i',array('appbyme_topic_items',$where));
        $finfo = WebUtils::uarr($db->queryAll('SELECT fid,name FROM %t WHERE fup=0 AND status=1',array('forum_forum')));
        $fstainfo = DbUtils::getDzDbUtils(TRUE)->queryAll('SELECT fid,name FROM %t WHERE fup=%d AND status=1',array('forum_forum',$finfo[0]['fid']));
        $nowfname = WebUtils::uarr($db->queryRow('SELECT ff.name FROM %t ac LEFT JOIN %t ff ON ff.fid=ac.cvalue WHERE ac.ckey=%s',array('appbyme_config','forum_forum','topic_bind_fid')));
        $nowverify = WebUtils::uarr($db->queryRow('SELECT `cvalue` FROM %t WHERE `ckey`=%s LIMIT 1',array('appbyme_config','topic_bind_verify')));
        $nowsettitle = $db->queryScalar('SELECT `cvalue` FROM %t WHERE `ckey`=%s LIMIT 1',array('appbyme_config','topic_settitle_hide'));

        $this->render('tcpmag',array(
            'data'=>$data,//要展示的数据
            'count'=>$count['COUNT(0)'],//共多少条数据
            'nowpage'=>$page,//当前页
            'totpage'=>ceil($count['COUNT(0)']/$pagesize),//计算共几页
            'search'=>$search,
            'finfo'=>WebUtils::uarr($finfo),//绑定板块列表
            'fstainfo' => WebUtils::uarr($fstainfo),
            'nowfname'=>$nowfname,//当前绑定板块名字
            'verify' => WebUtils::uarr($_G['setting']['verify']),//认证列表
            'nowverify' => $nowverify,//当前认证 和认证列表做默认选中用
            'nowsettitle' => $nowsettitle,//发表帖子是否显示标题
        ));
    }
    /**
     * 编辑话题
     */
    public function actionEdittpc($tiid = 0){
        $db = DbUtils::getDzDbUtils(true);
        $data = $db->queryRow('SELECT * FROM %t WHERE ti_id=%d',array('appbyme_topic_items',$tiid));
        $this->render('edittpc',array('data'=>$data));
    }
    /**
     * 获得板块分类
     */
    public function actionGetfinfo($fid=0,$case=0){
        $casearr = array('fidtwo','fidthree');
        if(!$fid){
            die('fail');
        }
        $data = DbUtils::getDzDbUtils(TRUE)->queryAll('SELECT fid,name FROM %t WHERE fup=%d AND status=1',array('forum_forum',$fid));
        $data = WebUtils::uarr($data);
        if(!$data){
            die('fail');
        }
        $outstring = '<select class="form-control" id="'.$casearr[$case].'"><option value="0">--未选择--</option>';

        foreach ($data as $v){
            $outstring .= '<option value="'.$v['fid'].'">'.$v['name'].'</option>';
        }
        $outstring .= '</select>';
        die($outstring);
    }
    /**
     * 绑定版块分类
     */
    public function actionBindfid($fid=0){
        $fid = intval($fid);
        if($fid){
            $data = DbUtils::createDbUtils(true)->query('REPLACE INTO %t (ckey,cvalue) VALUES (\'topic_bind_fid\',%d)',array('appbyme_config',$fid));
            if($data){
                echo 'suc';
            }
        }
    }

    /**
     * 绑定认证
     */
    public function actionBindverify($verify = 0){
        if($verify){
            $data = DbUtils::createDbUtils(true)->query('REPLACE INTO %t (ckey,cvalue) VALUES (\'topic_bind_verify\',%d)',array('appbyme_config',$verify));
            if($data){
                echo 'suc';
            }
        }
    }
    /**
     * 设置是否显示标题
     */
    public function actionSettitle($setTitle = 0){
        $data = DbUtils::createDbUtils(true)->query('REPLACE INTO %t (ckey,cvalue) VALUES (\'topic_settitle_hide\',%d)',array('appbyme_config',$setTitle));
        if($data){
            echo 'suc';
        }
    }
    /**
     * 编辑话题提交
     */
    public function actiontpcsub(){
        global $_G;
        $data = array();
        $upres = $this->_uploadAttach(0);
        $data['ti_title'] = WebUtils::t($_POST['title']);
        $data['ti_starttime'] = strtotime($_POST['startTime']);
        $data['ti_endtime'] = strtotime($_POST['endtime']);
        $data['ti_content'] = WebUtils::t($_POST['content']);
        $data['ti_cover'] = $upres['urlName'];
        $data['ti_remote'] = $upres['remote'];
        if($_POST['tiid']){
            $where['ti_id'] = intval($_POST['tiid']);
            DbUtils::getDzDbUtils(true)->update('appbyme_topic_items',$data,$where);

        }else{
            $data['ti_authorid'] = $_G['uid'];
            $username = DbUtils::getDzDbUtils(true)->queryRow('SELECT `username` FROM %t WHERE `uid`=%d',array('common_member',$_G['uid']));
            $data['ti_authorname'] = $username['username'];
            DbUtils::getDzDbUtils(TRUE)->insert('appbyme_topic_items',$data,true,true);
        }
        header("Location:".Yii::app()->createUrl('admin/topic/tpcmag'));
    }
    /**
     * 删除话题
     */
    public function actionDeltpc($tiid){
        if($tiid){
            $re = DbUtils::getDzDbUtils(true)->delete('appbyme_topic_items', array('ti_id'=>$tiid));
            if($re){
                echo 'suc';
            }else{
                echo 'fail';
            }
        }else {
            echo 'fail';
        }
    }
    /**
     * 话题发布者管理
     */
    public function actionPuttpcmag($type='all',$search='',$page=1) {
        $where = '';
        $data = array();
        $pagesize = 10;
        $count = 1;
        $staarr = array('申请','已通过','已冻结');
        $str = ($page-1)*$pagesize;
        $db = DbUtils::getDzDbUtils(true);
        if($type != 'all'){
            $where .= ' AND `uvalue`='.$type;
        }else {
            $where .= ' AND `uvalue` IN(0,1,2)';
        }
        if($search){
            $uids = $db->queryRow('SELECT uid,username FROM %t WHERE `username` LIKE %s LIMIT 1',array('common_member','%'.WebUtils::t($search).'%'));
            $status = $db->queryRow('SELECT `uvalue` FROM %t WHERE `uid`=%d AND `ukey`=%s',array('appbyme_user_setting',$uids['uid'],'issuperman'));
            if(!empty($status)){
                $data = array_merge($uids,$status);
            }else{
                $data = array();
            }        
        }else{
            $count = $db->queryRow('SELECT COUNT(0) FROM %t WHERE `ukey`=%s %i',array('appbyme_user_setting','issuperman',$where));
            $uids = $db->queryAll('SELECT * FROM %t WHERE `ukey`=%s %i LIMIT %d,%d',array('appbyme_user_setting','issuperman',$where,$str,$pagesize));
            foreach ($uids as $k => $v){
                $uinfo = getuserbyuid($v['uid']);
                $v['username'] = $uinfo['username'];
                $data[] = $v;
            }
        }
        $this->render('puttpcmag',array('data'=>$data,'staarr' => $staarr,'count'=>$count['COUNT(0)'],'nowpage'=>$page,'totpage'=>ceil($count['COUNT(0)']/$pagesize),'type'=>$type,'baseurl'=>$this->dzRootUrl));
    }
    /**
     * 话题发布人管理提交处理
     */
    public function actionPuttpcact(){
        $re = false;
        $db = DbUtils::getDzDbUtils(true);
        switch ($_GET['status']){
            case 0:
                $re = $db->update('appbyme_user_setting', array('uvalue'=>1), array('ukey'=>'issuperman','uid'=>$_GET['uid']));
                break;
            case 1:
                $re = $db->update('appbyme_user_setting', array('uvalue'=>2), array('ukey'=>'issuperman','uid'=>$_GET['uid']));
                break;
            case 2:
                $re = $db->update('appbyme_user_setting', array('uvalue'=>1), array('ukey'=>'issuperman','uid'=>$_GET['uid']));
                break;
        }
        if($re){
            echo 'suc';
        }else{
            echo 'fail';
        }
    }
    /**
     * 话题帖子管理
     */
    public function actionTpctmag() {
        $this->render('tpctmag');
    }

    /**
     * @param $uid
     * @param $allowValue
     * @return array
     * @throws CException
     */
    private function _uploadAttach($allowValue) {
        global $_G;
        $extid = 0;
        $type = 'forum';
        $forcename = '';
        $fileExtension = FileUtils::getFileExtension($_FILES['uploadFile']['name'][$allowValue], 'jpg');
        Yii::import('application.components.discuz.source.class.discuz.discuz_upload', true);
        $upload = new Mobcent_upload;
        $attach['extension'] = $fileExtension;
        $attach['attachdir'] = $upload->get_target_dir($type, $extid);
        $filename = $upload->get_target_filename($type, $extid, $forcename).'.'.$attach['extension'];
        $attach['attachment'] = $attach['attachdir'].$filename;
        $attach['target'] = getglobal('setting/attachdir').'./'.$type.'/'.$attach['attachment'];
        $res = array();
        $uploaded = Qiniuup::uptoqiniu(file_get_contents($_FILES['uploadFile']['tmp_name'][$allowValue]),$filename,true,false);
        if(!$uploaded){
            $savePath = getglobal('setting/attachdir').'./'.$type.'/'.$attach['attachdir'];

            if (!is_dir($savePath)) {
                mkdir($savePath, 0777, true);
            }
            $saveName = $savePath.$filename;
            $uploaded = move_uploaded_file($_FILES['uploadFile']['tmp_name'][$allowValue], $saveName);
            $islocalup = $uploaded ? true : false;
        }else{
            $res['urlName'] = $uploaded;
            $res['remote'] = 1;
        }
        if ($islocalup) {
            // 添加水印
            Yii::import('application.components.discuz.source.class.class_image', true);
            $image = new Mobcent_Image;
            if ($image->param['watermarkstatus']['forum'] > 0) {
                $image->makeWatermark($attach['target'], '', 'forum');
            }
            ImageUtils::getThumbImageEx($path_url, 10, false, false, true);
            $res['urlName'] = $attach['attachment'];
            $res['remote'] = 0;
        }
        if($uploaded){
            //七牛上传删掉临时文件
            if(file_exists($_FILES['uploadFile']['tmp_name'][$allowValue])){
                @unlink($_FILES['uploadFile']['tmp_name'][$allowValue]);
            }
        }
        return $res;
    }
}