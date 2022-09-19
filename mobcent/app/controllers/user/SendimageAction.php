<?php

/**
 *
 *
 * @author NaiXiaoXin<wangsiqi@goyoo.com>
 * @copyright 2003-2016 Goyoo Inc.
 */
class SendimageAction extends MobcentAction
{

    public function run($fieldid)
    {
        $res = $this->initWebApiArray();
        $res = $this->_uploadFile($res, $fieldid);
        WebUtils::outputWebApi($res);

    }

    private function _uploadFile($res, $fieldid)
    {
        global $_G;
        $file = $_FILES['file'];
        if (!$file)
        {
            return $this->makeErrorInfo($res, '请上传图片');
        }
        ProfileUtils::init();
        $info = $_G['cache']['profilesetting'][$fieldid];
        if ($info['size'] && $info['size'] * 1024 < $file['size'])
        {
            return $this->makeErrorInfo($res, '图片太大!!');
        }
        $extid         = 0;
        $type          = 'profile';
        $forcename     = '';
        $fileExtension = FileUtils::getFileExtension($file['name'], 'jpg');
        Yii::import('application.components.discuz.source.class.discuz.discuz_upload', true);
        $upload               = new Mobcent_upload;
        $attach['extension']  = $fileExtension;
        $attach['attachdir']  = $upload->get_target_dir($type, $extid);
        $filename             = $upload->get_target_filename($type, $extid, $forcename) . '.' . $attach['extension'];
        $attach['attachment'] = $attach['attachdir'] . $filename;
        $attach['target']     = getglobal('setting/attachdir') . './' . $type . '/' . $attach['attachment'];
       $uploaded = Qiniuup::uptoqiniu(file_get_contents($file['tmp_name']), $filename, true);
        if (!$uploaded)
        {
            $savePath = getglobal('setting/attachdir') . './' . $type . '/' . $attach['attachdir'];
            if (!is_dir($savePath))
            {
                mkdir($savePath, 0777, true);
            }
            $saveName = $savePath . $filename;
            move_uploaded_file($file['tmp_name'], $saveName);
            $setting     = $_G['setting'];
            $attachUrl   = WebUtils::getHttpFileName($setting['attachurl']);
            $uploaded    = $attachUrl . 'profile/' . $attach['attachdir'] . $filename;
        }
        $res['img']     = $uploaded;
        $res['fieldid'] = $fieldid;
        return $res;
    }

}
