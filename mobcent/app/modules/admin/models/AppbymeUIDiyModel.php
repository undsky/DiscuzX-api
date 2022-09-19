<?php

/**
 * UI Diy model类
 *
 * @author 谢建平 <jianping_xie@aliyun.com>
 * @author 耐小心
 * @copyright 2012-2015 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class AppbymeUIDiyModel extends DiscuzAR {

    const CONFIG_VERSION = '1.0';
    // navigator
    const NAV_KEY = 'app_uidiy_nav_info';
    const NAV_KEY_TEMP = 'app_uidiy_nav_info_temp';
    const NAV_TYPE_BOTTOM = 'bottom';
    const NAV_ITEM_ICON = 'mc_forum_main_bar_button';
    // module
    const MODULE_KEY = 'app_uidiy_modules';
    const MODULE_KEY_TEMP = 'app_uidiy_modules_temp';
    const MODULE_ID_DISCOVER = 1;
    const MODULE_ID_FASTPOST = 2;
    const MODULE_TYPE_FULL = 'full';
    const MODULE_TYPE_SUBNAV = 'subnav';
    const MODULE_TYPE_NEWS = 'news';
    const MODULE_TYPE_FASTPOST = 'fastpost';
    const MODULE_TYPE_CUSTOM = 'custom';
    const MODULE_TYPE_NEWCUSTOM = 'customSubnav';
    const MODULE_STYLE_CARD = 'card';
    const MODULE_STYLE_FLAT = 'flat';
    // component
    const COMPONENT_TYPE_EMPTY = 'empty';
    const COMPONENT_TYPE_DEFAULT = 'empty';
    const COMPONENT_TYPE_DISCOVER = 'discover';
    const COMPONENT_TYPE_FASTTEXT = 'fasttext';
    const COMPONENT_TYPE_FASTIMAGE = 'fastimage';
    const COMPONENT_TYPE_FASTCAMERA = 'fastcamera';
    const COMPONENT_TYPE_FASTAUDIO = 'fastaudio';
    const COMPONENT_TYPE_WEATHER = 'weather';
    const COMPONENT_TYPE_SEARCH = 'search';
    const COMPONENT_TYPE_FORUMLIST = 'forumlist';
    const COMPONENT_TYPE_NEWSLIST = 'newslist';
    const COMPONENT_TYPE_TOPICLIST = 'topiclist';
    const COMPONENT_TYPE_TOPICLIST_SIMPLE = 'topiclistSimple';
    const COMPONENT_TYPE_POSTLIST = 'postlist';
    const COMPONENT_TYPE_NEWSVIEW = 'newsview';
    const COMPONENT_TYPE_SIGN = 'sign';
    const COMPONENT_TYPE_MESSAGELIST = 'messagelist';
    const COMPONENT_TYPE_SETTING = 'setting';
    const COMPONENT_TYPE_ABOUT = 'about';
    const COMPONENT_TYPE_USERINFO = 'userinfo';
    const COMPONENT_TYPE_USERLIST = 'userlist';
    const COMPONENT_TYPE_MODULEREF = 'moduleRef';
    const COMPONENT_TYPE_WEBAPP = 'webapp';
    const COMPONENT_TYPE_LAYOUT = 'layout';
    const COMPONENT_TYPE_SURROUDING_POSTLIST = 'surroudingPostlist';
    const COMPONENT_TYPE_SCAN = 'scan';
    const COMPONENT_TYPE_LIVELIST = 'livelist';
    const COMPONENT_TYPE_CLASSIFICATIONLIST = 'classificationlist';
    const COMPONENT_TYPE_CONFIG_SWITCH = 'configSwitch';
    //add component start
    const COMPONENT_TYPE_TALK = 'talk';
    const COMPONENT_TYPE_TOPICLIST_COMPLEX = 'topiclistComplex';
    const COMPONENT_TYPE_TALKPOSTLIST = 'talkPostList';
    const COMPONENT_TYPE_FASTTALK = 'fasttalk'; //fasttalk
    const COMPONENT_TYPE_CONTACTS = 'contacts';
    const COMPONENT_TYPE_SUBNAVFLAT = 'layoutSubnavFlat'; //SubnavFlat
    const COMPONENT_TYPE_TRANSPARENT = 'layoutTransparent';
    const COMPONENT_TYPE_SEPARATOR = 'layoutSeparator';
    //add component end
    const COMPONENT_ICON_STYLE_TEXT = 'text';
    const COMPONENT_ICON_STYLE_IMAGE = 'image';
    const COMPONENT_ICON_STYLE_TEXT_IMAGE = 'textImage';
    const COMPONENT_ICON_STYLE_TEXT_OVERLAP_UP = 'textOverlapUp';
    const COMPONENT_ICON_STYLE_TEXT_OVERLAP_DOWN = 'textOverlapDown';
    const COMPONENT_ICON_STYLE_CIRCLE = 'circle';
    const COMPONENT_ICON_STYLE_NEWS = 'news';
    const COMPONENT_ICON_STYLE_TEXT_OVERLAP_UP_VIDEO = 'textOverlapUp_Video';
    const COMPONENT_ICON_STYLE_TEXT_OVERLAP_DOWN_VIDEO = 'textOverlapDown_Video';
    const COMPONENT_STYLE_FLAT = 'flat';
    const COMPONENT_STYLE_CARD = 'card';
    const COMPONENT_STYLE_TIEBA = 'tieba';
    const COMPONENT_STYLE_HEADLINES = 'headlines';
    const COMPONENT_STYLE_NETEASE_NEWS = 'neteaseNews';
    const COMPONENT_STYLE_IMAGE = 'image';
    const COMPONENT_STYLE_IMAGE_2 = 'image2';
    const COMPONENT_STYLE_IMAGE_BIG = 'imageBig';
    const COMPONENT_STYLE_IMAGE_SUDOKU = 'imageSudoku';
    const COMPONENT_STYLE_1 = 'style1';
    const COMPONENT_STYLE_2 = 'style2';
    const COMPONENT_STYLE_SUBNAV_TOPBAR = 'subnavTopbar';
    const COMPONENT_STYLE_BOARD_SPLIT = 'boardSplit';
    const COMPONENT_STYLE_CIRCLE = 'circle';
    const COMPONENT_STYLE_LAYOUT_DEFAULT = 'layoutDefault';
    const COMPONENT_STYLE_LAYOUT_IMAGE = 'layoutImage';
    const COMPONENT_STYLE_LAYOUT_SLIDER_HIGH = 'layoutSlider';
    const COMPONENT_STYLE_LAYOUT_SLIDER_MID = 'layoutSlider_Mid';
    const COMPONENT_STYLE_LAYOUT_SLIDER_LOW = 'layoutSlider_Low';
    const COMPONENT_STYLE_LAYOUT_LINE = 'layoutLine';
    const COMPONENT_STYLE_LAYOUT_NEWS_AUTO = 'layoutNewsAuto';
    const COMPONENT_STYLE_LAYOUT_NEWS_MANUAL = 'layoutNewsManual';
    const COMPONENT_STYLE_LAYOUT_ONE_COL = 'layoutOneCol';
    const COMPONENT_STYLE_LAYOUT_ONE_COL_HIGH = 'layoutOneCol_High';
    const COMPONENT_STYLE_LAYOUT_ONE_COL_MID = 'layoutOneCol_Low';
    const COMPONENT_STYLE_LAYOUT_ONE_COL_LOW = 'layoutOneCol_Low_Fixed';
    const COMPONENT_STYLE_LAYOUT_ONE_COL_SUPER_LOW = 'layoutOneCol_Super_Low';
    const COMPONENT_STYLE_LAYOUT_TWO_COL = 'layoutTwoCol';
    const COMPONENT_STYLE_LAYOUT_TWO_COL_TEXT = 'layoutTwoColText';
    const COMPONENT_STYLE_LAYOUT_TWO_COL_HIGH = 'layoutTwoCol_High';
    const COMPONENT_STYLE_LAYOUT_TWO_COL_MID = 'layoutTwoCol_Mid';
    const COMPONENT_STYLE_LAYOUT_TWO_COL_LOW = 'layoutTwoCol_Low';
    const COMPONENT_STYLE_LAYOUT_TWO_COL_SUPER_LOW = 'layoutTwoCol_Super_Low';
    const COMPONENT_STYLE_LAYOUT_THREE_COL = 'layoutThreeCol';
    const COMPONENT_STYLE_LAYOUT_THREE_COL_TEXT = 'layoutThreeColText';
    const COMPONENT_STYLE_LAYOUT_THREE_COL_HIGH = 'layoutThreeCol_High';
    const COMPONENT_STYLE_LAYOUT_THREE_COL_MID = 'layoutThreeCol_Mid';
    const COMPONENT_STYLE_LAYOUT_THREE_COL_LOW = 'layoutThreeCol_Low';
    const COMPONENT_STYLE_LAYOUT_THREE_COL_SUPER_LOW = 'layoutThreeCol_Super_Low';
    const COMPONENT_STYLE_LAYOUT_FOUR_COL = 'layoutFourCol';
    const COMPONENT_STYLE_LAYOUT_FOUR_COL_HIGH = 'layoutFourCol_High';
    const COMPONENT_STYLE_LAYOUT_FOUR_COL_MID = 'layoutFourCol_Mid';
    const COMPONENT_STYLE_LAYOUT_FOUR_COL_LOW = 'layoutFourCol_Low';
    const COMPONENT_STYLE_LAYOUT_FOUR_COL_SUPER_LOW = 'layoutFourCol_Super_Low';
    const COMPONENT_STYLE_LAYOUT_ONE_COL_ONE_ROW = 'layoutOneColOneRow';
    const COMPONENT_STYLE_LAYOUT_ONE_COL_TWO_ROW = 'layoutOneColTwoRow';
    const COMPONENT_STYLE_LAYOUT_ONE_COL_THREE_ROW = 'layoutOneColThreeRow';
    const COMPONENT_STYLE_LAYOUT_ONE_ROW_ONE_COL = 'layoutOneRowOneCol';
    const COMPONENT_STYLE_LAYOUT_TWO_ROW_ONE_COL = 'layoutTwoRowOneCol';
    const COMPONENT_STYLE_LAYOUT_THREE_ROW_ONE_COL = 'layoutThreeRowOneCol';
    const COMPONENT_STYLE_DISCOVER_DEFAULT = 'discoverDefault';
    const COMPONENT_STYLE_DISCOVER_CUSTOM = 'discoverCustom';
    const COMPONENT_STYLE_DISCOVER_SLIDER = 'discoverSlider';
    const COMPONENT_TITLE_POSITION_LEFT = 'left';
    const COMPONENT_TITLE_POSITION_CENTER = 'center';
    const COMPONENT_TITLE_POSITION_RIGHT = 'right';
    const COMPONENT_ICON_FASTPOST = 'mc_forum_ico';
    const COMPONENT_ICON_DISCOVER_DEFAULT = 'mc_forum_squre_icon';
    const COMPONENT_ICON_TOPBAR = 'mc_forum_top_bar_button';
    const USERLIST_FILTER_ALL = 'all';
    const USERLIST_FILTER_FRIEND = 'friend';
    const USERLIST_FILTER_FOLLOW = 'follow';
    const USERLIST_FILTER_FOLLOWED = 'followed';
    const USERLIST_FILTER_RECOMMEND = 'recommend';
    const USERLIST_ORDERBY_DATELINE = 'dateline';
    const USERLIST_ORDERBY_REGISTER = 'register';
    const USERLIST_ORDERBY_LOGIN = 'login';
    const USERLIST_ORDERBY_FOLLOWED = 'followed';
    const USERLIST_ORDERBY_DISTANCE = 'distance';
    const IMAGE_POSITION_NONE = 0;
    const IMAGE_POSITION_LEFT = 1;
    const IMAGE_POSITION_RIGHT = 2;
    const BOARD_NAME_GONE = 0;
    const BOARD_NAME_VISIBLE = 1; 

    public static function initNavigation() {
        return array(
            'type' => self::NAV_TYPE_BOTTOM,
            'navItemList' => array(
                self::initNavItem(array(
                    'moduleId' => 3,
                    'title' => '首页',
                    'icon' => self::NAV_ITEM_ICON . '1',
                )),
                self::initNavItem(array(
                    'moduleId' => 4,
                    'title' => '社区',
                    'icon' => self::NAV_ITEM_ICON . '2',
                )),
                self::initNavItemFastpost(),
                self::initNavItem(array(
                    'moduleId' => 5,
                    'title' => '消息',
                    'icon' => self::NAV_ITEM_ICON . '4',
                )),
                self::initNavItemDiscover(),
            ),
        );
    }

    public static function initNavItem($params = array()) {
        return array_merge(array(
            'moduleId' => 0,
            'title' => '',
            'icon' => self::NAV_ITEM_ICON . '1',
                ), $params);
    }

    public static function initNavItemDiscover() {
        return self::initNavItem(array(
                    'moduleId' => self::MODULE_ID_DISCOVER,
                    'title' => '我的',
                    'icon' => self::NAV_ITEM_ICON . '5',
        ));
    }

    public static function initNavItemFastpost() {
        return self::initNavItem(array(
                    'moduleId' => self::MODULE_ID_FASTPOST,
                    'title' => '快速发表',
                    'icon' => self::NAV_ITEM_ICON . '17',
        ));
    }

    public static function getNavigationInfo($isTemp = false,$version = '') {
        $data = DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ', array('appbyme_config', $isTemp ? self::NAV_KEY_TEMP.$version : self::NAV_KEY.$version)
        );
        return $data ? (array) unserialize(WebUtils::u($data)) : array(
            'type' => self::NAV_TYPE_BOTTOM,
            'navItemList' => array()
        );
    }

    public static function saveNavigationInfo($navInfo, $isTemp = false,$version = '') {
        $key = $isTemp ? self::NAV_KEY_TEMP.$version : self::NAV_KEY.$version;
        $appUIDiyNavInfo = array(
            'ckey' => $key,
            'cvalue' => WebUtils::t(serialize($navInfo)),
        );
        $config = DbUtils::createDbUtils(true)->queryRow('
            SELECT * 
            FROM %t 
            WHERE ckey=%s
            ', array('appbyme_config', $key)
        );
        if (empty($config)) {
            DbUtils::createDbUtils(true)->insert('appbyme_config', $appUIDiyNavInfo);
        } else {
            DbUtils::createDbUtils(true)->update('appbyme_config', $appUIDiyNavInfo, array('ckey' => $key));
        }
        return true;
    }

    public static function initModules() {
        return array(
            self::initDiscoverModule(),
            self::initFastpostModule(),
            self::initModule(array(
                'id' => 3,
                'title' => '首页',
                'type' => self::MODULE_TYPE_SUBNAV,
                'rightTopbars' => array(
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_USERINFO,
                        'icon' => self::COMPONENT_ICON_TOPBAR . '6',
                    )),
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_EMPTY,
                    )),
                ),
                'componentList' => array(
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_TOPICLIST_SIMPLE,
                        'title' => '最新',
                        'style' => self::COMPONENT_STYLE_TIEBA,
                        'extParams' => array(
                            'forumId' => 0,
                            'fastpostForumIds' => array(),
                            'filter' => 'typeid',
                            'filterId' => 0,
                            'orderby' => 'new',
                            'listTitleLength' => 40,
                            'listSummaryLength' => 40,
                            'listImagePosition' => self::IMAGE_POSITION_RIGHT,
                            'subDetailViewStyle' => self::COMPONENT_STYLE_CARD,
                        ),
                    )),
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_TOPICLIST_SIMPLE,
                        'title' => '精华',
                        'style' => self::COMPONENT_STYLE_NETEASE_NEWS,
                        'extParams' => array(
                            'forumId' => 0,
                            'fastpostForumIds' => array(),
                            'filter' => 'typeid',
                            'filterId' => 0,
                            'orderby' => 'marrow',
                            'listTitleLength' => 40,
                            'listSummaryLength' => 0,
                            'listImagePosition' => self::IMAGE_POSITION_RIGHT,
                            'subDetailViewStyle' => self::COMPONENT_STYLE_FLAT,
                        ),
                    )),
                    self::initComponent(),
                    self::initComponent(),
                ),
            )),
            self::initModule(array(
                'id' => 4,
                'title' => '社区',
                'type' => self::MODULE_TYPE_FULL,
                'componentList' => array(
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_FORUMLIST,
                        'title' => '版块',
                    )),
                ),
                'rightTopbars' => array(
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_SEARCH,
                        'icon' => self::COMPONENT_ICON_TOPBAR . '10',
                    )),
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_USERINFO,
                        'icon' => self::COMPONENT_ICON_TOPBAR . '6',
                    )),
                ),
            )),
            self::initModule(array(
                'id' => 5,
                'title' => '消息',
                'type' => self::MODULE_TYPE_FULL,
                'componentList' => array(
                    self::initComponent(array(
                        'type' => self::COMPONENT_TYPE_MESSAGELIST,
                    )),
                ),
            )),
        );
    }

    public static function initModule($params = array()) {
        return array_merge(array(
            'id' => 0,
            'type' => self::MODULE_TYPE_FULL,
            'style' => self::MODULE_STYLE_FLAT,
            'title' => '',
            'icon' => Yii::app()->getController()->rootUrl . '/images/admin/module-default.png',
            'leftTopbars' => array(
                self::initComponent(array(
                    'type' => self::COMPONENT_TYPE_EMPTY,
                )),
            ),
            'rightTopbars' => array(
                self::initComponent(array(
                    'type' => self::COMPONENT_TYPE_EMPTY,
                )),
                self::initComponent(array(
                    'type' => self::COMPONENT_TYPE_EMPTY,
                )),
            ),
            'componentList' => array(),
            'extParams' => array('padding' => '',),
                ), $params);
    }

    public static function initDiscoverModule() {
        return self::initModule(array(
                    'id' => self::MODULE_ID_DISCOVER,
                    'title' => '我的',
                    'componentList' => array(self::initComponentDiscover()),
        ));
    }

    public static function initFastpostModule() {
        return self::initModule(array(
                    'id' => self::MODULE_ID_FASTPOST,
                    'title' => '快速发表',
                    'type' => self::MODULE_TYPE_FASTPOST,
                    'componentList' => array(
                        self::initComponent(array(
                            'type' => self::COMPONENT_TYPE_FASTTEXT,
                            'title' => '文字',
                            'icon' => self::COMPONENT_ICON_FASTPOST . '27',
                        )),
                        self::initComponent(array(
                            'type' => self::COMPONENT_TYPE_FASTIMAGE,
                            'title' => '图片',
                            'icon' => self::COMPONENT_ICON_FASTPOST . '28',
                        )),
                        self::initComponent(array(
                            'type' => self::COMPONENT_TYPE_FASTCAMERA,
                            'title' => '拍照',
                            'icon' => self::COMPONENT_ICON_FASTPOST . '29',
                        )),
                        self::initComponent(array(
                            'type' => self::COMPONENT_TYPE_FASTAUDIO,
                            'title' => '语音',
                            'icon' => self::COMPONENT_ICON_FASTPOST . '45',
                        )),
                    // self::initComponent(array(
                    //     'type' => self::COMPONENT_TYPE_SIGN,
                    //     'title' => '签到',
                    //     'icon' => self::COMPONENT_ICON_FASTPOST . '30',
                    // )),
                    ),
        ));
    }

    public static function getModules($isTemp = false) {
        $data = DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ', array('appbyme_config', $isTemp ? self::MODULE_KEY_TEMP : self::MODULE_KEY)
        );
        return $data ? (array) unserialize(WebUtils::u($data)) : array();
    }

    public static function saveModules($modules, $isTemp = false) {
        $key = $isTemp ? self::MODULE_KEY_TEMP : self::MODULE_KEY;
        $appUIDiyModules = array(
            'ckey' => $key,
            'cvalue' => WebUtils::t(serialize($modules)),
        );
        $config = DbUtils::createDbUtils(true)->queryRow('
            SELECT * 
            FROM %t 
            WHERE ckey=%s
            ', array('appbyme_config', $key)
        );
        if (empty($config)) {
            DbUtils::createDbUtils(true)->insert('appbyme_config', $appUIDiyModules);
        } else {
            DbUtils::createDbUtils(true)->update('appbyme_config', $appUIDiyModules, array('ckey' => $key));
        }
        return true;
    }

    public static function deleteNavInfo($isTemp = false) {
        return DbUtils::createDbUtils(true)->delete('appbyme_config', array(
                    'where' => 'ckey = %s',
                    'arg' => array($isTemp ? self::NAV_KEY_TEMP : self::NAV_KEY),
        ));
    }

    public static function deleteModules($isTemp = false) {
        return DbUtils::createDbUtils(true)->delete('appbyme_config', array(
                    'where' => 'ckey = %s',
                    'arg' => array($isTemp ? self::MODULE_KEY_TEMP : self::MODULE_KEY),
        ));
    }

    public static function initComponent($params = array()) {
        return array_merge(array(
            'px'=>0,
            'id' => '',
            'type' => self::COMPONENT_TYPE_DEFAULT,
            'style' => self::COMPONENT_STYLE_FLAT,
            'title' => '',
            'desc' => '',
            // 'icon' => Yii::app()->getController()->rootUrl.'/images/admin/module-default.png',
            'icon' => '',
            'iconStyle' => self::COMPONENT_ICON_STYLE_IMAGE,
            'componentList' => array(),
            'extParams' => array(
                'dataId' => 0,
                'titlePosition' => self::COMPONENT_TITLE_POSITION_LEFT,
                // 'isShowForumIcon' => 1,
                // 'isShowForumTwoCols' => 1,
                'pageTitle' => '',
                'newsModuleId' => 0,
                'forumId' => 0,
                'moduleId' => 0,
                'topicId' => 0,
                'articleId' => 0,
                'fastpostForumIds' => array(),
                'isShowTopicTitle' => 1,
                // 'isShowTopicSort' => 0,
                'isShowMessagelist' => 0,
                'filter' => '',
                'filterId' => 0,
                'order' => 0,
                'orderby' => '',
                'redirect' => '',
                'listTitleLength' => 40,
                'listSummaryLength' => 0,
                'listImagePosition' => self::IMAGE_POSITION_RIGHT,
                'subListStyle' => self::COMPONENT_STYLE_FLAT,
                'talkId' => 0,
                'subDetailViewStyle' => self::COMPONENT_STYLE_FLAT,
            ),
                ), $params);
    }

    public static function initComponentDiscover() {
        return self::initComponent(array(
                    'type' => self::COMPONENT_TYPE_DISCOVER,
                    'componentList' => array(
                        self::initLayout(array(
                            'style' => self::COMPONENT_STYLE_DISCOVER_SLIDER,
                            'componentList' => array(
                            ),
                        )),
                        self::initLayout(array(
                            'style' => self::COMPONENT_STYLE_DISCOVER_DEFAULT,
                            'componentList' => array(
                                self::initComponent(array(
                                    'title' => '个人中心',
                                    'type' => self::COMPONENT_TYPE_USERINFO,
                                    'icon' => self::COMPONENT_ICON_DISCOVER_DEFAULT . '9',
                                )),
                                self::initComponent(array(
                                    'title' => '周边用户',
                                    'type' => self::COMPONENT_TYPE_USERLIST,
                                    'icon' => self::COMPONENT_ICON_DISCOVER_DEFAULT . '5',
                                    'extParams' => array(
                                        'filter' => self::USERLIST_FILTER_ALL,
                                        'orderby' => self::USERLIST_ORDERBY_DISTANCE,
                                    ),
                                )),
                                self::initComponent(array(
                                    'title' => '周边帖子',
                                    'type' => self::COMPONENT_TYPE_SURROUDING_POSTLIST,
                                    'icon' => self::COMPONENT_ICON_DISCOVER_DEFAULT . '4',
                                )),
                                self::initComponent(array(
                                    'title' => '推荐用户',
                                    'type' => self::COMPONENT_TYPE_USERLIST,
                                    'icon' => self::COMPONENT_ICON_DISCOVER_DEFAULT . '6',
                                    'extParams' => array(
                                        'filter' => self::USERLIST_FILTER_RECOMMEND,
                                        'orderby' => self::USERLIST_ORDERBY_DATELINE,
                                    )
                                )),
                                self::initComponent(array(
                                    'title' => '设置',
                                    'type' => self::COMPONENT_TYPE_SETTING,
                                    'icon' => self::COMPONENT_ICON_DISCOVER_DEFAULT . '7',
                                )),
                            // self::initComponent(array(
                            //     'title' => '关于',
                            //     'type' => self::COMPONENT_TYPE_ABOAT,
                            // )),
                            ),
                        )),
                        self::initLayout(array(
                            'style' => self::COMPONENT_STYLE_DISCOVER_CUSTOM,
                            'componentList' => array(
                            ),
                        )),
                    ),
        ));
    }

    public static function initLayout($params = array()) {
        return self::initComponent(array_merge(array(
                    'type' => self::COMPONENT_TYPE_LAYOUT,
                    'style' => self::COMPONENT_STYLE_LAYOUT_DEFAULT,
                                ), $params));
    }

    public function getLeftMenu() {
        $sql = $return = array();
        $sql = DbUtils::createDbUtils(true)->queryAll('
            SELECT `id`,`name`
            FROM %t
            ', array('appbyme_uidiyconfig')
        );
        foreach ($sql as $k => $s) {
            $temp = array();
            $temp['name'] = WebUtils::u($s['name']) . ' ID:' . $s['id'];
            $temp['url'] = Yii::app()->createAbsoluteUrl('admin/uidiy/index', array('id' => $s['id']));
            $return[] = $temp;
        }
        return $return;
    }

    //多DIY 修改Start ByNxx
    //获得DIY配置全部
    public function getConfig() {
        return $data = DbUtils::createDbUtils(true)->queryAll('
            SELECT *
            FROM %t
            ', array('appbyme_uidiyconfig')
        );
    }

    public function getDefaultInfo() {
        $return = AppbymeUIDiyModel::getByKey('app_uidiy_info');
        return unserialize($return);
    }

    //获得配置信息通过ID
    public function getConfigByID($id) {
        return DbUtils::createDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE `id` = %s
            ', array('appbyme_uidiyconfig', $id)
        );
    }

    //获得DIY配置名字
    public function getConfigNameById($id) {
        $data = DbUtils::createDbUtils(true)->queryRow('
            SELECT *
            FROM %t
            WHERE id = %s
            ', array('appbyme_uidiyconfig', $id)
        );
        return $data['name'];
    }

    //多DIY保存导航 ByNxx
    public static function nxxSaveNavigationInfo($id, $navInfo, $isTemp = false) {
        $id = (int) $id;
        $config = self::getConfigByID($id);
        if (empty($config)) {
            echo 'error';
            exit();
        }
        $navInfo = WebUtils::t(serialize($navInfo));
        $key = $isTemp ? self::NAV_KEY_TEMP : self::NAV_KEY;
        DbUtils::createDbUtils(true)->update('appbyme_uidiyconfig', array($key => $navInfo), array('id' => $id));
        return true;
    }

    //多DIY 模块保存 ByNxx
    public static function nxxSaveModules($id, $modules, $isTemp = false) {
        $id = (int) $id;
        $config = self::getConfigByID($id);
        if (empty($config)) {
            echo 'error';
            exit();
        }
        $modules = WebUtils::t(serialize($modules));
        $key = $isTemp ? self::MODULE_KEY_TEMP : self::MODULE_KEY;
        DbUtils::createDbUtils(true)->update('appbyme_uidiyconfig', array($key => $modules), array('id' => $id));
        return true;
    }

    //多DIY模块获取 ByNxx
    public static function nxxGetModules($id, $isTemp = false) {
        $data = self::getConfigByID($id);
        $key = $isTemp ? self::MODULE_KEY_TEMP : self::MODULE_KEY;
        // debug($data);;
        return $data[$key] ? (array) unserialize(WebUtils::u($data[$key])) : array();
    }

    //多DIY获得导航 ByNxx
    public static function nxxGetNavigationInfo($id, $isTemp = false) {
        $data = self::getConfigByID($id);
        $key = $isTemp ? self::NAV_KEY_TEMP : self::NAV_KEY;
        return $data[$key] ? (array) unserialize(WebUtils::u($data[$key])) : array(
            'type' => self::NAV_TYPE_BOTTOM,
            'navItemList' => array()
        );
    }

    public function deleteConfig($id = '') {
        $res = WebUtils::initWebApiResult();
        $res['errCode'] = '0';
        if (empty($id)) {
            $res['errCode'] = '-1';
            $return['errMsg'] = 'ID为空！';
            WebUtils::outputWebApi($res, 'utf-8');
        }
        DbUtils::createDbUtils(true)->delete('appbyme_uidiyconfig', array('id' => $id));
        WebUtils::outputWebApi($res, 'utf-8');
    }

    //获得默认模块信息
    public function getByKey($key) {
        return DbUtils::createDbUtils(true)->queryScalar('
            SELECT cvalue
            FROM %t
            WHERE ckey = %s
            ', array('appbyme_config', $key)
        );
    }

    public function addConfig($name, $icon, $status) {
        $modtemp = self::getByKey(self::MODULE_KEY_TEMP);
        $mod = self::getByKey(self::MODULE_KEY);
        $navtemp = self::getByKey(self::NAV_KEY_TEMP);
        $nav = self::getByKey(self::NAV_KEY);

        $add = array('app_uidiy_nav_info_temp' => $navtemp,
            'app_uidiy_nav_info' => $nav,
            'app_uidiy_modules' => $mod,
            'app_uidiy_modules_temp' => $modtemp,
            'name' => WebUtils::t($name),
            'icon' => $icon,
            'status' => $status,
        );
        $return = DbUtils::createDbUtils(true)->insert('appbyme_uidiyconfig', $add, true);
        return $return;
    }

    //弹窗
    public function showMsg($msg, $url) {
        if ($url) {
            echo ' <script>alert(\'' . $msg . '\');var url1 = "' . $url . '";setTimeout(function () {location.href = url1}, 1)</script>';
            exit();
        }
        echo ' <script>alert(\'' . $msg . '\');setTimeout(function () {history.go(-1);}, 1)</script>';
        exit();
    }

    //修改配置
    public function editConfig($id, $name, $icon, $status) {
        DbUtils::createDbUtils(true)->update('appbyme_uidiyconfig', array('name' => $name, 'icon' => $icon, 'status' => $status), array('id' => $id));
        return true;
    }

    public function editDefauftConfig($name, $icon) {
        $array['name'] = $name;
        $array['icon'] = $icon;
        $key = 'app_uidiy_info';
        $appUIDiyModules = array(
            'ckey' => $key,
            'cvalue' => serialize($array),
        );
        $config = DbUtils::createDbUtils(true)->queryRow('
            SELECT * 
            FROM %t 
            WHERE ckey=%s
            ', array('appbyme_config', $key)
        );
        if (empty($config)) {
            DbUtils::createDbUtils(true)->insert('appbyme_config', $appUIDiyModules);
        } else {
            DbUtils::createDbUtils(true)->update('appbyme_config', $appUIDiyModules, array('ckey' => $key));
        }
        return true;
    }

    //获得已开启的UIDIY配置
    public function getOpenUIDiyConfig() {
        return $data = DbUtils::createDbUtils(true)->queryAll('
            SELECT *
            FROM %t
            WHERE status=%s
            ', array('appbyme_uidiyconfig', '1')
        );
    }
    public static function getVersionConfig(){
        return array(
            '1' => '版本1.0',
            '2' => '版本2.0'
        );
    }
}
