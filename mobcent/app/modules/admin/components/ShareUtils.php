<?php

/**
 *
 *
 * @author NaiXiaoXin<nxx@yytest.cn>
 * @copyright 2003-2016 Goyoo Inc.
 *
 */
class ShareUtils {
    /**
     * 获得分享平台总和
     */
    public static function getCountShareType($array) {
        $info = explode(',', $array);
        $count = (int)count($info);
        $str = '';
        if ($count == '6') {
            $str = '全平台';
        } else {
            foreach ($info as $k => $v) {
                switch ($v) {
                    case '1':
                        $str .= 'QQ,';
                        break;
                    case '2':
                        $str .= 'QQ空间,';
                        break;
                    case '3':
                        $str .= '微信好友,';
                        break;
                    case '4':
                        $str .= '朋友圈,';
                        break;
                    case '5':
                        $str .= '新浪微博,';
                        break;
                    case '6':
                        $str .= 'Facebook,';
                        break;
                }
            }
            $str = substr($str, 0, -1);
        }
        return $str;
    }

    /**
     * 分享到哪里列表 比如门户,帖子,APP
     */
    public static function getShowType($info) {
        $array = unserialize($info);
        $str = '';
        foreach ($array as $k => $v) {
            switch ($k) {
                case 'portal':
                    $str .= '门户,';
                    break;
                case 'topic':
                    $str .= '帖子,';
                    break;
                case 'app':
                    $str .= 'APP分享,';
                    break;
                case 'live':
                    $str .= '直播,';
                    break;
                default:
                    break;
            }
        }
        $str = substr($str, 0, -1);
        return $str;

    }

    //弹窗
    public static function showMsg($msg, $url) {
        if ($url) {
            echo ' <script>alert(\'' . $msg . '\');var url1 = "' . $url . '";setTimeout(function () {location.href = url1}, 1)</script>';
            exit();
        }
        echo ' <script>alert(\'' . $msg . '\');setTimeout(function () {history.go(-1);}, 1)</script>';
        exit();
    }


    /**
     * 搜索函数
     * @param type $model 表名称
     * @param array $where 条件 array(字段名称=>条件)
     * @param string $order 排序
     * @param string $url URL用于做分页HTML
     * @param init $page 当前页数
     * @param init $pageSize 一页多少行
     * @return type
     */
    public static function search($model = '', $where = array(), $order = null, $url = null, $page, $pageSize) {
        $countArr = array($model);
        $countSql = 'SELECT count(*) AS count FROM %t WHERE 1';

        foreach ($where as $k => $v) {
            $countSql .= ' AND ' . $k . '=%s';
            $countArr[] = $v;
        }
        $count = DbUtils::getDzDbUtils(true)->queryScalar($countSql, $countArr);

        $offset = ($page - 1) * $pageSize;
        $searchArr = array($model);
        $sql = 'SELECT * FROM %t WHERE 1';

        foreach ($where as $k => $v) {
            $sql .= ' AND ' . $k . '=%s';
            $searchArr[] = $v;
        }
        if (!empty($order)) {
            $sql .= ' ORDER BY ' . $order;
        }
        $sql .= ' LIMIT %d,%d';
        $searchArr[] = $offset;
        $searchArr[] = $pageSize;
        $list = DbUtils::getDzDbUtils(true)->queryAll($sql, $searchArr);
        $multi = multi($count, $pageSize, $page, $url);
        return array('searchList' => $list, 'multi' => $multi);
    }

    /**
     * select返回的数组进行整数映射转换
     *
     * @param array $map 映射关系二维数组  array(
     *                                          '字段名1'=>array(映射关系数组),
     *                                          '字段名2'=>array(映射关系数组),
     *                                           ......
     *                                       )
     * @return array
     */
    public static function int_to_string(&$data, $map) {
        if ($data === false || $data === null) {
            return $data;
        }
        $data = (array)$data;
        foreach ($data as $key => $row) {
            foreach ($map as $col => $pair) {
                if (isset($row[$col]) && isset($pair[$row[$col]])) {
                    $data[$key][$col . '_text'] = $pair[$row[$col]];
                }
            }
        }
        return $data;
    }

    public static function getShowFxMsg($info, $form) {
        $array = unserialize($info);
        global $_G;
        $return = '';
        switch ($form) {
            case 'app';
                $return = '无';
                break;
            case 'live';
                $return = '无';
                break;
            case 'topic';
                $return = '<a href="' . $_G['siteurl'] . '/forum.php?mod=viewthread&tid=' . $array['tid'] . '" target="_blank">帖子ID:' . $array['tid'] . '</a>';
                break;
            case 'portal';
                $return = '<a href="' . $_G['siteurl'] . '/portal.php?mod=view&aid=' . $array['aid'] . '" target="_blank">文章ID:' . $array['aid'] . '</a>';
                break;
            default;
                $return = '未识别平台';
        }
        return $return;
    }

    public static function endWebApi($res, $errMsg, $errCode) {
        $tmpRes = WebUtils::initWebApiResult();
        $tmpRes = WebUtils::makeWebApiResult($tmpRes, $errCode, $errMsg);
        $tmpRes = array_merge($res, $tmpRes);
        WebUtils::outputWebApi($tmpRes);
    }
}