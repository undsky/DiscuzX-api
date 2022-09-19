<?php

/**
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 */
//Mobcent::setErrors();
class UserAdminCpAction extends MobcentAction {

    public function run($uid = '') {
        $this->_adminCp($uid);
    }

    private function _adminCp($uid) {
        global $_G;
        header("Content-Type: text/html; charset=utf-8");
        $member = $this->loadmember($uid);
        include_once libfile('function/member');
        include_once libfile('function/forum');
        include_once libfile('function/misc');

        if (empty($member)) {
            $this->_exitWithHtmlAlert(WebUtils::t('用户信息不存在,请确定UID正确'));
        }
        if (($member['type'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || $member['type'] == 'special') {
            $this->_exitWithHtmlAlert('modcp_member_ban_illegal');
        }
      //  debug($member);
        if ($member && $_POST['bansubmit']=='yes') {
            $setarr = array();
            $reason = WebUtils::t(dhtmlspecialchars(trim($_GET['reason'])));
            if (!$reason && ($_G['group']['reasonpm'] == 1 || $_G['group']['reasonpm'] == 3)) {
                $this->_exitWithHtmlAlert('admin_reason_invalid');
            }

            if ($_GET['bannew'] == 4 || $_GET['bannew'] == 5) {
                if ($_GET['bannew'] == 4 && !$_G['group']['allowbanuser'] || $_GET['bannew'] == 5 && !$_G['group']['allowbanvisituser']) {
                    $this->_exitWithHtmlAlert('admin_nopermission');
                }
                $groupidnew = $_GET['bannew'];
                $banexpirynew = !empty($_GET['banexpirynew']) ? TIMESTAMP + $_GET['banexpirynew'] * 86400 : 0;
                $banexpirynew = $banexpirynew > TIMESTAMP ? $banexpirynew : 0;
                if ($banexpirynew) {
                    $member['groupterms'] = $member['groupterms'] && is_array($member['groupterms']) ? $member['groupterms'] : array();
                    $member['groupterms']['main'] = array('time' => $banexpirynew, 'adminid' => $member['adminid'], 'groupid' => $member['groupid']);
                    $member['groupterms']['ext'][$groupidnew] = $banexpirynew;
                    $setarr['groupexpiry'] = groupexpiry($member['groupterms']);
                } else {
                    $setarr['groupexpiry'] = 0;
                }
                $adminidnew = -1;
                C::t('forum_postcomment')->delete_by_authorid($member['uid'], false, true);
            } elseif ($member['groupid'] == 4 || $member['groupid'] == 5) {
                if (!empty($member['groupterms']['main']['groupid'])) {
                    $groupidnew = $member['groupterms']['main']['groupid'];
                    $adminidnew = $member['groupterms']['main']['adminid'];
                    unset($member['groupterms']['main']);
                    unset($member['groupterms']['ext'][$member['groupid']]);
                    $setarr['groupexpiry'] = groupexpiry($member['groupterms']);
                } else {
                    $usergroup = C::t('common_usergroup')->fetch_by_credits($member['credits']);
                    $groupidnew = $usergroup['groupid'];
                    $adminidnew = 0;
                }
            } else {
                $groupidnew = $member['groupid'];
                $adminidnew = $member['adminid'];
            }

            $setarr['adminid'] = $adminidnew;
            $setarr['groupid'] = $groupidnew;
            C::t('common_member')->update($member['uid'], $setarr);

            if (DB::affected_rows()) {
                savebanlog($member['username'], $member['groupid'], $groupidnew, $banexpirynew, $reason);
            }

            C::t('common_member_field_forum')->update($member['uid'], array('groupterms' => serialize($member['groupterms'])));
            if ($_GET['bannew'] == 4) {
                $notearr = array(
                    'user' => "<a href=\"home.php?mod=space&uid=$_G[uid]\">$_G[username]</a>",
                    'day' => $_GET['banexpirynew'],
                    'reason' => $reason,
                    'from_id' => 0,
                    'from_idtype' => 'banspeak'
                );
                notification_add($member['uid'], 'system', 'member_ban_speak', $notearr, 1);
            }
            if ($_GET['bannew'] == 5) {
                $notearr = array(
                    'user' => "<a href=\"home.php?mod=space&uid=$_G[uid]\">$_G[username]</a>",
                    'day' => $_GET['banexpirynew'],
                    'reason' => $reason,
                    'from_id' => 0,
                    'from_idtype' => 'banvisit'
                );
                notification_add($member['uid'], 'system', 'member_ban_visit', $notearr, 1);
            }

            if ($_GET['bannew'] == 4 || $_GET['bannew'] == 5) {
                crime('recordaction', $member['uid'], ($_GET['bannew'] == 4 ? 'crime_banspeak' : 'crime_banvisit'), $reason);
            }

            $this->_exitWithHtmlAlert('modcp_member_ban_succeed');

        }else{
            $this->getController()->renderPartial('userAdminCp',array('member'=>$member));
        }

    }


    private function _exitWithHtmlAlert($message) {
        $message = WebUtils::u(lang('message', $message));
        $location = WebUtils::createUrl_oldVersion('index/returnmobileview');
        $htmlString = '
            <script>
                alert("' . $message . '");
                location.href = "' . $location . '";
            </script>';
        echo $htmlString;
        exit;
    }

    private function loadmember(&$uid, &$username="", &$error="") {
        global $_G;

        $uid = !empty($_GET['uid']) && is_numeric($_GET['uid']) && $_GET['uid'] > 0 ? $_GET['uid'] : '';
        $username = isset($_GET['username']) && $_GET['username'] != '' ? dhtmlspecialchars(trim($_GET['username'])) : '';

        $member = array();

        if ($uid || $username != '') {

            $member = $uid ? getuserbyuid($uid) : C::t('common_member')->fetch_by_username($username);
            if ($member) {
                $uid = $member['uid'];
                $member = array_merge($member, C::t('common_member_field_forum')->fetch($uid), C::t('common_member_profile')->fetch($uid),
                    C::t('common_usergroup')->fetch($member['groupid']), C::t('common_usergroup_field')->fetch($member['groupid']));
            }
            if (!$member) {
                $error = 2;
            } elseif (($member['grouptype'] == 'system' && in_array($member['groupid'], array(1, 2, 3, 6, 7, 8))) || in_array($member['adminid'], array(1, 2, 3))) {
                $error = 3;
            } else {
                $member['groupterms'] = dunserialize($member['groupterms']);
                $member['banexpiry'] = !empty($member['groupterms']['main']['time']) && ($member['groupid'] == 4 || $member['groupid'] == 5) ? dgmdate($member['groupterms']['main']['time'], 'Y-n-j') : '';
                $error = 0;
            }

        } else {
            $error = 1;
        }
        return $member;
    }

}
