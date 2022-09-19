<?php
/**
 * Created by PhpStorm.
 * User: guanghua
 * Date: 2016/4/25
 * Time: 16:02
 */
class ArticleController extends ApiController{
    /**
     * 文章栏目添加
     */
    public function actionAdditem()
    {
        $data = array();
        $category = new PortalcategoryModel($data);
        $id = $category->insert();
        if(!$id) {
            $this->error($category->getError());
        }
        $this->setData(array('id'=>$id));
    }

    /**
     * 删除文章栏目
     * @param $catids 多个文章列表值用英文,连接
     */
    public function actionDelitem()
    {
        $category = new PortalcategoryModel($this->data);
        $re = $category->del();
        $this->setData(array('re'=>$re));
    }

    /**
     * 获得文章栏目列表
     * @param int $page 当前页数
     * @param int $pageSize 每页大小
     * @param string $type 类型 ALL---表示所有的，PUB---表示可以发表的
     * @param string $catids 通过id筛选栏目
     * @param string $catname 通过标题筛选栏目
     */
    public function actionItemlist()
    {
        $category = new PortalcategoryModel($this->data);
        $list = $category->getlist();
        $this->setData(array('list' => $list));
    }
    /**
     * 插入文章
     */
    public function actionAddar()
    {
        $disallowpublish = $this->db->queryScalar('SELECT `disallowpublish` FROM %t WHERE `catid`=%d',array('portal_category',$this->data['catid']));
        if($disallowpublish){
            $this->error('该栏目禁止发布文章');
        }
        $category = new PortalarticleModel($this->data);
        $res = $category->insert();
        if(!$res) {
            $this->error($category->getError());
        }
        $this->_clearCache();
    }
    public function actionDelar()
    {
        echo 'Delar';
    }
    public function actionArlist()
    {
        echo 'Arlist';
    }
    protected function setRules()
    {
        return array(
            'delitem' => array(
                'catids' => 'string'
            ),
        );
    }
    private function _clearCache(){
        Yii::app()->cache->flush();
    }
}