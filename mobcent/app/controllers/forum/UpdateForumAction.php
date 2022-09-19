<?php
/**
 * User: 蒙奇·D·jie
 * Date: 16/10/13
 * Time: 下午4:29
 * Email: mqdjie@gmail.com
 * @property DbUtils::getDzDbUtils(true) $db
 */

if (!defined('IN_DISCUZ') || !defined('IN_APPBYME')) {
    exit('Access Denied');
}

class UpdateForumAction extends MobcentAction
{
    private $db;
    public function run($topicId, $postId, $position, $type = 'add')
    {
        $this->db = DbUtils::getDzDbUtils(true);
        $res = $this->updatePostStick(array('topicId'=>$topicId, 'postId' => $postId, 'position' => $position, 'type' => $type));
        WebUtils::outputWebApi($res, '', true);
    }


    protected function updatePostStick($data)
    {
        $res = $this->initWebApiArray();
        global $_G;
        if(!$_G['group']['allowstickreply']) {
            return $this->makeErrorInfo($res, 'mobcent_error_auths');
        }
        switch($data['type']) {
            case 'add' :
                $res = $this->add($res, $data);
                break;
            case 'del' :
                $res = $this->del($res, $data);
                break;
            default :
                return $this->makeErrorInfo($res, 'mobcent_error_parameters');
                break;
        }


        return $res;
    }

    /**
     * @param $res
     * @param $data
     * @return mixed
     * 回帖置顶增加
     */
    protected function add($res, $data)
    {
        $result = $this->db->insert('forum_poststick', array(
            'tid'       => $data['topicId'],
            'pid'       => $data['postId'],
            'position'  => $data['position'],
            'dateline'      => time()
        ), true, true);
        if($result) {
            C::t('forum_thread')->update($data['topicId'], array('stickreply'=>1));
            return $res;
        }
        return $this->makeErrorInfo($res, 'mobcent_error_addfail');
    }

    /**
     * @param $res
     * @param $data
     * @return mixed
     * 删除回帖置顶
     */
    protected function del($res, $data)
    {
        $result = $this->db->delete('forum_poststick', array(
            'tid'       => $data['topicId']
        ));
        if($result) {
            return $res;
        }
        return $this->makeErrorInfo($res, 'mobcent_error_delfail');
    }
}