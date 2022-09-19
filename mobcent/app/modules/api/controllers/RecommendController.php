<?php
/**
 *
 *
 * @author 耐小心<i@naixiaoxin.com>
 * @copyright 2003-2017 耐小心
 */


//Mobcent::setErrors();
class RecommendController extends ApiController{

    /**
     * 获得推荐列表
     */
    public function actionRecommendList()
    {
        $page = $this->data['page'];
        $pageSize = $this->data['pageSize'];
        $str = ($page - 1) * $pageSize;
        $sql = 'SELECT * FROM %t WHERE `type`=%s ORDER BY `id` DESC LIMIT %d,%d';
        $data['items'] = $this->db->queryAll($sql, array('appbyme_recommend_list', $this->data['type'],$str, $pageSize));
        foreach ($data['items'] as $key=>$item)
        {
            $data['items'][$key]['ext'] =  unserialize($item['ext']);
            if($item['type']=='user'&&$data['items'][$key]['ext']['type']=='custom'){
                $data['items'][$key]['userInfo'] = array();
                if(!empty($data['items'][$key]['ext']['userId'])){
                    $userId = explode(',',$data['items'][$key]['ext']['userId']);
                    foreach ($userId as $user)
                    {
                        $data['items'][$key]['userInfo'][] = array(
                            'uid'=>$user,
                            'userName'=> UserUtils::getUserName($user),
                            'avatar'=>UserUtils::getUserAvatar($user)
                        );
                    }
                }
            }
        }
        $data['count'] = intval($this->db->queryFirst('SELECT COUNT(0) FROM %t WHERE `type`=%s ', array('appbyme_recommend_list',$this->data['type'])));
        $data['now_page'] = intval($this->data['page']);
        $data['total_pages'] = ceil($data['count'] / $pageSize);//计算共几页
        $this->setData($data);
    }

    /**
     * 获得推荐信息
     */
    public function actionRecommend()
    {
        if($_POST){
            $data['title'] = $this->data['title'];
            $data['type'] = $this->data['type'];
//            debug($this->data);
            $data['ext'] = serialize($this->data['ext']);
            if($this->data['id']){
                $id = $this->data['id'];
                $this->db->update('appbyme_recommend_list',$data,array('id'=>$id));
            }else{
               $id =  $this->db->insert('appbyme_recommend_list',$data,true);
            }
        }else{
            $id = intval($this->data['id']);
            if(empty($id)){
                $this->error('ID不存在');
            }
        }
        $data = $this->db->queryRow('SELECT * FROM %t WHERE id=%d', array('appbyme_recommend_list', $id));
        $data['ext'] = unserialize($data['ext']);
        $this->setData($data);
    }


    public function actionModuleList()
    {
        $page = $this->data['page'];
        $pageSize = $this->data['pageSize'];
        $str = ($page - 1) * $pageSize;
        $sql = 'SELECT * FROM %t  ORDER BY `id` DESC LIMIT %d,%d';
        $item = $this->db->queryAll($sql, array('appbyme_recommend_module', $str, $pageSize));
        foreach ($item as $key=>$value)
        {
            $moduels = $this->db->queryAll("SELECT * FROM %t where `module`=%d",array('appbyme_recommend_bind',$value['id']));
            $listInfo = $this->db->queryRow("SELECT * FROM %t where `id`=%d",array('appbyme_recommend_list',$value['listId']));

            $forums = array();
            $portal = array();
            foreach ($moduels as $moduel)
            {
                switch ($moduel['type']){
                    case  'forum':
                        $forums[] = $moduel['moduleId'];
                        break;
                    case 'portal':
                        $portal[] = $moduel['moduleId'];
                        break;
                }
            }
            $item[$key]['forums'] = implode(',',$forums);
            $item[$key]['portal'] = implode(',',$portal);
            $item[$key]['listInfo'] = $listInfo;
        }
        $data['item'] = $item;
        $data['count'] = $this->db->queryFirst('SELECT COUNT(0) FROM %t', array('appbyme_recommend_module'));
        $data['total_pages'] = ceil($data['count'] / $pageSize);//计算共几页
        $data['now_page'] = intval($this->data['page']);
        $this->setData($data);
    }

    public function actionModule()
    {
        if($_POST){
            $data['title'] = $this->data['title'];
            $data['listId'] = $this->data['listId'];
            $data['position'] = $this->data['position'];
            if($this->data['id']){
                $id = $this->data['id'];
                $this->db->delete('appbyme_recommend_bind',array('module'=>$id));
                $this->db->update('appbyme_recommend_module',$data,array('id'=>$id));
            }else{
                $id =  $this->db->insert('appbyme_recommend_module',$data,true);
            }
            if(!empty($this->data['forums'])){
                foreach (explode(',',$this->data['forums']) as $forum)
                {
                    $this->db->insert('appbyme_recommend_bind',array('module'=>$id,'type'=>'forum','moduleid'=>$forum));
                }
            }
            if(!empty($this->data['portals'])){
                foreach (explode(',',$this->data['portals']) as $forum)
                {
                    $this->db->insert('appbyme_recommend_bind',array('module'=>$id,'type'=>'portal','moduleid'=>$forum));
                }
            }

        }else{
            $id = intval($this->data['id']);
            if(empty($id)){
                $this->error('ID不存在');
            }
        }
        $data = $this->db->queryRow('SELECT * FROM %t WHERE id=%d', array('appbyme_recommend_module', $id));
        $moduels = $this->db->queryAll("SELECT * FROM %t where `module`=%d",array('appbyme_recommend_bind',$id));
        $forums = array();
        $portal = array();
        foreach ($moduels as $moduel)
        {
            switch ($moduel['type']){
                case  'forum':
                    $forums[] = $moduel['moduleId'];
                    break;
                case 'portal':
                    $portal[] = $moduel['moduleId'];
                    break;
            }
        }
        $data['forums'] = implode(',',$forums);
        $data['portal'] = implode(',',$portal);
        $listInfo = $this->db->queryAll("SELECT * FROM %t where `id`=%d",array('appbyme_recommend_list',$data['listId']));
        $data['type'] = $listInfo['type'];
        $this->setData($data);
    }

    public function actionDeleteModule()
    {
        $this->db->delete('appbyme_recommend_bind',array('module'=>$this->data['id']));
        $this->db->delete('appbyme_recommend_module',array('id'=>$this->data['id']));
    }

    public function actionDeleteRecommend()
    {
        $modules = $this->db->queryAll("select * from %t where listid=%d",array('appbyme_recommend_module',$this->data['id']));
        foreach ($modules as $module)
        {
            $this->db->delete('appbyme_recommend_bind',array('module'=>$module['id']));
            $this->db->delete('appbyme_recommend_module',array('id'=>$module['id']));
        }
        $this->db->delete('appbyme_recommend_list',array('id'=>$this->data['id']));

    }




}