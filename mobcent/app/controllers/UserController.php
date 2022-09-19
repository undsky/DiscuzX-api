<?php

/**
 * @author  谢建平 <jianping_xie@aliyun.com>  
 * @copyright 2012-2014 Appbyme
 */
if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class UserController extends MobcentController {

    public function actions() {
        return array(
            'setting' => 'application.controllers.user.SettingAction',
            'qqlogin' => 'application.controllers.user.QQLoginAction',
            'getsetting' => 'application.controllers.user.GetSettingAction',
            'uploadavatar' => 'application.controllers.user.UploadAvatarAction',
            'saveavatar' => 'application.controllers.user.SaveAvatarAction',
            'userinfo' => 'application.controllers.user.UserInfoAction',
            'topiclist' => 'application.controllers.user.TopicListAction',
            'sign' => 'application.controllers.user.SignAction',
            'searchuser' => 'application.controllers.user.SearchuserAction',
            'userlist' => 'application.controllers.user.UserListAction',
            'albumlist' => 'application.controllers.user.AlbumListAction',
            'photolist' => 'application.controllers.user.PhotoListAction',
            'useradmin' => 'application.controllers.user.UserAdminAction',
            'userfavorite' => 'application.controllers.user.UserFavoriteAction',
            'login' => 'application.controllers.user.LoginAction',
            'register' => 'application.controllers.user.RegisterAction',
            'updateuserinfo' => 'application.controllers.user.UpdateUserInfoAction',
            'report' => 'application.controllers.user.ReportAction',
            'switch' => 'application.controllers.user.SwitchAction',
            'location' => 'application.controllers.user.LocationAction',
            'qqinfo' => 'application.controllers.user.QQInfoAction',
            'saveqqinfo' => 'application.controllers.user.SaveQQInfoAction',
            'useradminview' => 'application.controllers.user.UserAdminViewAction',
            'uploadavatarex' => 'application.controllers.user.UploadAvatarExAction',
            'savealbum' => 'application.controllers.user.SaveAlbumAction',
            'userinfoadminview' => 'application.controllers.user.UserInfoAdminViewAction',
            'platforminfo' => 'application.controllers.user.PlatFormInfoAction',
            'saveplatforminfo' => 'application.controllers.user.SavePlatFormInfoAction',
            'getpwd' => 'application.controllers.user.GetPwdAction',
            'updateusersign' => 'application.controllers.user.UpdateUserSignAction',
            'checkregister' => 'application.controllers.user.CheckRegisterAction',
            'getprofilegroup' => 'application.controllers.user.GetProfileGroupAction',
            'share' => 'application.controllers.user.ShareAction',
            'useradmincp' => 'application.controllers.user.UserAdminCpAction',
            'getverify' => 'application.controllers.user.GetVerifyAction',
            'updateverify'=>'application.controllers.user.UpdateVerifyAction',
            'wxlogin' => 'application.controllers.user.WxLoginAction',
            'sendimage'=>'application.controllers.user.SendimageAction',
            'qqfastlogin' => 'application.controllers.user.QQFastLoginAction',
            'friend'=>'application.controllers.user.FriendAction',
            'getfavoriteinfo'=>'application.controllers.user.GetFavoriteInfoAction',
            'getpwdex'=>'application.controllers.user.GetPwdExAction',
            'updatepwd'=>'application.controllers.user.UpdatePwdAction',
        );
    }

    protected function mobcentAccessRules() {
        return array(
            'setting' => true,
            'qqlogin' => false,
            'getsetting' => false,
            'uploadavatar' => true,
            'saveavatar' => true,
            'userinfo' => true,
            'topiclist' => true,
            'sign' => true,
            'searchuser' => true,
            'userlist' => false,
            'albumlist' => true,
            'photolist' => true,
            'useradmin' => true,
            'userfavorite' => true,
            'login' => false,
            'register' => false,
            'updateuserinfo' => true,
            'report' => true,
            'switch' => true,
            'location' => true,
            'qqinfo' => false,
            'saveqqinfo' => false,
            'useradminview' => true,
            'uploadavatarex' => true,
            'savealbum' => true,
            'userinfoadminview' => true,
            'platforminfo' => false,
            'saveplatforminfo' => false,
            'getpwd' => false,
            'updateusersign' => true,
			'getprofilegroup' => false,
            'share' => false,
            'checkregister'=>false,
            'getverify'=>true,
            'wxlogin' => false,
            'sendimage'=>true,
            'qqfastlogin' => false,
            'friend'=>true,
            'getfavoriteinfo' => true,
            'getpwdex' => false,
            'updatepwd'=>true
        );
    }

}
