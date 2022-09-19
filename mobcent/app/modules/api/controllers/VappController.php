<?php
/**
 * 微社区应用
 * Created by PhpStorm.
 * User: onmylifejie
 * Date: 2016/6/23
 * Time: 12:41
 **/

//Mobcent::setErrors();
class VappController extends ApiController {

    private $fid = 73;

    public function actionOperate() {
        $icon = $this->data['icon'];
        $title = $this->data['title'];
        //修改logo
        if(isset($icon)) {
            //$fid = $this->data['fid'];
            $fid = $this->fid;//按照多肉的模板，先写死
            $data = DbUtils::createDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE fid = %d
            ', array('forum_forumfield', $fid)
            );

            if (empty($data)) {
                $this->error('该板块不存在');
            }
           DbUtils::createDbUtils(true)->update('forum_forumfield', array('icon'=>$icon), array('fid' => $fid));
            $res['logo'] = 'logo上传成功';
        }
        //修改论坛名称
        if(isset($title)) {
            $version = '2';
            $id = 0;
            $uidiyModule = new AppbymeUiDiy($version, $id);
            $data = $uidiyModule->getInfo(true);
            $moduleId = $data['navInfo']['navItemList'][0]['moduleId'];
            foreach($data['modules'] as $k => $v) {
                if($v['id'] == $moduleId) {
                    $data['modules'][$k]['title'] = $title;
                }
            }
            $uidiyModule->saveDiy(false,$data['navInfo'],$data['modules']);
            DbUtils::createDbUtils(true)->update('forum_forum', array('name'=>$title), array('fid' => $this->fid));
            $res['title'] = '论坛名称修改成功';
            //更新缓存
            Yii::app()->cache->flush();

        }
        $this->setData($res);
    }
    /**
     * 获得置顶帖子
     */
    public function actionTopicList(){
        $service  = new TopicService();
        $this->setData($service->getList());
    }

    public function actionTopicDel(){
        $service  = new TopicService();
        $service->setData('invisible','-1');
        $service->setData('displayorder','-1');
        if(!$service->update()){
            $this->error($service->getError());
        }
    }

    public function actionTopicAdd() {
        $service = new TopicService();
        $tid = $service->insert();
        if(!$tid){
            $this->error($service->getError());
        }
        $this->setData(array('tid'=>$tid));
    }


    public function actionTopicEdit(){
        $service  = new TopicService();
        if(!$service->update()){
            $this->error($service->getError());
        }
    }
}

