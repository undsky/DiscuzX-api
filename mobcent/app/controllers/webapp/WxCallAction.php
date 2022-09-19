<?php
/**
 * WebApp微信回调
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */



if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class WxCallAction extends MobcentAction{

    public  $state = '';
    public  $from = '';
    public function run($code,$state=''){
        $this->state = $state;
        $this->from = reset(explode('_',$state)) ;
        $return = $this->_wxCall($code);
        $this->toSuccess($return['openId'],$return['accessToken']);
    }

    /**
     * 用Code获取openId和accessToken
     * @param $code OAuth2授权获得的Code
     * @return array openId:微信openId accessToken:授权Token
     */
    private function _wxCall($code){
        if(empty($code)){
            $this->toError('Code不存在');
        }
        $webAppConfig = AppbymeConfig::getWebAppInfo();
        $appId = $webAppConfig['wxappid'];
        $appSecret = $webAppConfig['wxappsecret'];
        if(empty($appId)||empty($appSecret)){
            $this->toError('WebApp设置有误');
        }
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$appId.'&secret='.$appSecret.'&code='.$code.'&grant_type=authorization_code';
        $result = WebUtils::jsonDecode(WebUtils::httpRequest($url));
        if(!empty($result['errcode'])||empty($result['openid'])){
            $this->toError($result['errmsg']);
        }
        return array('openId'=>$result['openid'],'accessToken'=>$result['access_token']);
    }

    /**
     * 出错跳转
     */
    private function toError($err){
        if($this->from!='wxshare'){
            $url = $this->getController()->dzRootUrl.'/mobcent/app/web/index.php?r=webapp/index#/?error='.$err.'&state='.$this->state;
        }else{
            $url = Yii::app()->createAbsoluteUrl('webapp/sharelogin',array('act'=>'error','error'=>$err,'state'=>$this->state));
        }
        header("Location:" . $url );
        exit();
    }

    private function toSuccess($openId,$oauthToken){
        $wxInfo = AppbymeConnection::getUserInfoFromWeiXin($openId,$oauthToken);
        if(empty($wxInfo['unionid'])){
            $this->toError('无法获取到unionid,请确认您已将公众号绑定到开发者平台');
        }
        $wxLogin = AppbymeConnection::getMobcentWxinfoByUnionId($wxInfo['unionid']);
        $member = getuserbyuid($wxLogin['uid'], 1);
        if ($wxLogin && empty($member)) {
            DB::delete('appbyme_connection', array('uid' => $wxLogin['uid'], 'type' => 1));
            $wxLogin = $member = array();
        }
        if($wxLogin){
            //登录成功..需要处理跳转并传临时Token
            $code =random('10');
            DB::insert('appbyme_tempcode',array('code'=>$code,'uid'=>$member['uid'],'time'=>time()));
            if($this->from!='wxshare'){
                $url = $this->getController()->dzRootUrl.'/mobcent/app/web/index.php?r=webapp/index#/?token='.$code.'&state='.$this->state;
            }else{
                $url = Yii::app()->createAbsoluteUrl('webapp/sharelogin',array('act'=>'wxlogin','state'=>$this->state,'token'=>$code));
            }
            header("Location:" . $url);
            exit();
        }else{
            if($this->from!='wxshare') {
                $url = $this->getController()->dzRootUrl . '/mobcent/app/web/index.php?r=webapp/index#/?openId=' . $openId . '&oauthToken=' . $oauthToken . '&state=' . $this->state;
            }else{
                $url = Yii::app()->createAbsoluteUrl('webapp/sharelogin',array('act'=>'wxlogin','state'=>$this->state,'openId'=>$openId,'oauthToken'=>$oauthToken));
            }
            header("Location:" . $url);
            exit();
        }

    }

}


