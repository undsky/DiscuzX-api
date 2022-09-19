<?php
/**
 * @property PortalModels $model
 * User: 蒙奇·D·jie
 * Date: 16/11/29
 * Time: 下午4:03
 * Email: mqdjie@gmail.com
 */

class PortalController extends ApiController
{
    private $model;

    /**
     * 门户模块列表
     */
    public function actionGetModule()
    {
        $this->model = new PortalModels($this->data);
        $module      = $this->model->getModules();
        $this->setData(array('list' => $module));
    }

    /**
     * 模块内容
     * type = 2 幻灯片  type = 1 模块内容
     */
    public function actionGetSource()
    {
        $this->model = new PortalModels($this->data);
        $source      = $this->model->getSources();
        $this->setData(array('list' => $source));
    }
    /**
     * 模块参数设置
     */
    public function actionGetParams()
    {
        $this->model = new PortalModels($this->data);
        $params      = $this->model->getParams();
        if(!$params) {
            $this->error('此项仅适用于在模块内容设置里的内容来源类别里面设置了版块id或者文章栏目id');
        }
        $this->setData(array('list' => $params));
    }

    /**
     * 创建新模块
     */
    public function actionAddModule()
    {
        if (empty($this->data['name'])) {
            $this->error('参数有误');
        }
        $this->model = new PortalModels($this->data);
        if (!$this->model->insertModule()) {
            $this->error('模块创建失败');
        }
    }

    /**
     * 更新模块信息
     */
    public function actionUpdateModule()
    {
        $this->model = new PortalModels($this->data);
        $this->model->updateModule();
    }

    /**
     * 删除模块信息
     */
    public function actionDelModule()
    {
        $this->model = new PortalModels($this->data);
        if(!$this->model->delModules()) {
            $this->error('删除失败');
        }
        $this->setData($this->data['mids']);
    }

    /**
     * 存储模块内容,幻灯片,模块参数设置
     */
    public function actionSaveModule()
    {
        $this->model = new PortalModels($this->data);
        //增加模块内容设置
        if(!empty($this->data['new_soure_ids'])) {
            $res['source'] = $this->model->insertSource();
        }
        //增加幻灯片信息
        if(isset($this->data['new_id'])) {
            $res['slider'] = $this->model->insertSlider();
        }
        //更新模块参数设置
        $res['params'] = $this->model->updateParams();
        $this->setData($res);
    }

    /**
     * 删除模块,幻灯片.更新模块参数
     */
    public function actionDelModuleParam()
    {
        $this->model = new PortalModels($this->data);
        //删除模块内容
        if(!empty($this->data['del_source_sids'])) {
            $res['source'] = $this->model->delSource() ? $this->data['del_source_sids'] : false;
        }
        //删除幻灯片信息
        if(!empty($this->data['del_slider_sids'])) {
            $res['slider'] = $this->model->delSlider() ? $this->data['del_slider_sids'] : false;
        }
        $this->setData($res);
    }

    /**
     * 更新模块,幻灯片.更新模块参数
     */
    public function actionUpModuleParam()
    {
        $this->model = new PortalModels($this->data);
        //更新模块内容
        if(!empty($this->data['new_source_ids'])) {
            $res['source'] = $this->model->updateSource() ? $this->data['new_source_ids'] : false;
        }
        //很想幻灯片信息
        if(!empty($this->data['new_ids'])) {
            $res['slider'] = $this->model->updateSlider() ? $this->data['new_ids'] : false;
        }
        //更新模块参数设置
        $res['params'] = $this->model->updateParams();
        $this->setData($res);
    }
}