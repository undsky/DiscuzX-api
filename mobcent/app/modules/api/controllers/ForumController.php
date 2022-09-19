<?php
/**
 * 导入帖子
 * @property ForumService $model
 * Created by PhpStorm.
 * User: congjie
 * Date: 16/7/18
 * Time: 下午3:35
 */
class ForumController extends ApiController
{
    private  $model;
    public function init()
    {
        parent::init();
        $this->data = Apivalidate::getdata();
        $this->model = new ForumService($this->data);
    }

    public function actionAddUser()
    {
        if($this->model->type) {
            $res = $this->model->addLiveUser();
        } else {
            $res = $this->model->addUser();
        }
        if(!$res) {
            $this->error(WebUtils::u($this->model->getError()));
        }
        $this->setData($this->model->getUserInfo());
    }

    //获取版块列表
    public function actionGetForumList()
    {
        $res = $this->model->getForumList();
        $this->setData($res);
    }

    public function actionUserList()
    {
        $this->setData($this->model->UserList());
    }

    public function actionInsert()
    {
        $tid = $this->model->insertForum();
        if(!$tid) {
            $this->error(WebUtils::u($this->model->getError()));
        }
        $this->setData(array('tid' => $tid));
    }
}