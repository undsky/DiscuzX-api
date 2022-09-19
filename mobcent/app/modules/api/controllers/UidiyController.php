<?php

/**
 * UiDiy接口
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */
class UidiyController extends ApiController
{

    public    $rootUrl     = '';
    public    $dzRootUrl   = '';
    protected $uidiyModule = null;

    public function init()
    {
        parent::init();
        global $_G;
        $_G['siteurl']     = substr($_G['siteurl'], 0, -16);
        $_G['siteroot']    = substr($_G['siteroot'], 0, -16);
        $this->rootUrl     = Yii::app()->getBaseUrl(true);
        $this->dzRootUrl   = substr($this->rootUrl, 0, -16);
        $id                = intval($_GET['id']);
        $version           = $_GET['version'] ? $_GET['version'] : '1';
        $type              = $_GET['type'] ? $_GET['type'] : 'app';
        $this->uidiyModule = new AppbymeUiDiy($version, $id, $type);
    }

    public function actionGet()
    {
        if ($this->uidiyModule->id > 0) {
            $name = AppbymeUIDiyModel::getConfigNameById($this->uidiyModule->id);
            if (empty($name)) {
                $this->error('多DIYID不存在,请确定ID正确');
            }
        } else {
            $defauft = AppbymeUIDiyModel::getDefaultInfo();
            $name    = isset($defauft['name']) ? $defauft['name'] : '自定义页面';
        }
        $data = $this->uidiyModule->getInfo(true);
        $this->setData(array(
            'id'                 => $this->uidiyModule->id,
            'name'               => $name,
            'navInfo'            => $data['navInfo'],
            'modules'            => $data['modules'],
            'topicTypeSortInfos' => $this->_getTopicTypeSortInfos(),
            'version'            => $this->uidiyModule->version,
            'forumUrl'           => $this->dzRootUrl,
        ));
    }


    public function actionSave($navInfo, $modules, $isTemp = true)
    {
        $navInfo = WebUtils::jsonDecode($navInfo, true);
        $modules = WebUtils::jsonDecode($modules, true);
        $this->uidiyModule->saveDiy($isTemp, $navInfo, $modules);
    }

    public function actionNewsModules()
    {
        $this->setData(array(
            'list' => $this->_getModuleList()
        ));
    }

    public function actionForumList()
    {
        $this->setData(array(
            'list' => $this->getForumList()
        ));
    }

    private function _getTopicTypeSortInfos()
    {
        $infos = array();

        $fields = DbUtils::createDbUtils(true)->queryAll('
            SELECT fid, threadtypes, threadsorts
            FROM %t
            ', array('forum_forumfield')
        );
        foreach ($fields as $field) {
            if (!empty($field)) {
                $info = array('fid' => (int)$field['fid'], 'types' => array(), 'sorts' => array());

                $types = unserialize($field['threadtypes']);
                if (!empty($types['types'])) {
                    foreach ($types['types'] as $key => $value) {
                        $info['types'][] = array(
                            'id'    => $key,
                            'title' => WebUtils::emptyHtml($value),
                        );
                    }
                }
                $sorts = unserialize($field['threadsorts']);
                if (!empty($sorts['types'])) {
                    foreach ($sorts['types'] as $key => $value) {
                        $info['sorts'][] = array(
                            'id'    => $key,
                            'title' => WebUtils::emptyHtml($value),
                        );
                    }
                }

                $infos[$info['fid']] = $info;
            }
        }

        return $infos;
    }


    private function _getModuleList()
    {
        return DbUtils::createDbUtils(true)->queryAll('
            SELECT `mid`,`name`
            FROM %t
            ORDER BY displayorder ASC
            ',
            array('appbyme_portal_module')
        );
    }

    /**
     * 获取所有版块列表
     *
     * @return array
     */
    private function getForumList()
    {
        global $_G;
        loadcache('forums');
        $forums     = array();
        $forumCache = $_G['cache']['forums'];
        foreach ($_G['cache']['forums'] as $fid => $forum) {
            if ($forum['type'] == 'group') {
                $forums[$fid]['id']   = $fid;
                $forums[$fid]['name'] = $forum['name'];
            } elseif ($forum['type'] == 'forum') {
                $forums[$forum['fup']]['sub'][$fid]['id']   = $fid;
                $forums[$forum['fup']]['sub'][$fid]['name'] = $forum['name'];
            } elseif ($forum['type'] == 'sub') {
                $subForum                                                            = $forumCache[$forum['fup']];
                $forums[$subForum['fup']]['sub'][$forum['fup']]['sub'][$fid]['id']   = $fid;
                $forums[$subForum['fup']]['sub'][$forum['fup']]['sub'][$fid]['name'] = $forum['name'];
            }
        }

        return $forums;
    }
}