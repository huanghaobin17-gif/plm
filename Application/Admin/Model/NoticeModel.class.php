<?php

namespace Admin\Model;

use Think\Model;
use Think\Model\RelationModel;

class NoticeModel extends CommonModel
{
    protected $len = 100;
    protected $tablePrefix = 'sb_';
    protected $tableName = 'notice';

    /*
     * 系统公告列表的数据
     * return array
     */
    public function getNoticeData()
    {
        $limit = I('post.limit') ? I('post.limit') : 10;
        $page = I('post.page') ? I('post.page') : 1;
        $where['hospital_id'] = session('current_hospitalid');
        $offset = ($page - 1) * $limit;
        $total = $this->DB_get_count('notice', $where);
        $noticeinfo = $this->DB_get_all('notice', '', $where, '', 'top desc,notid desc', $offset . "," . $limit);
        //查询当前用户是否有权限进行编辑公告
        $editNotice = get_menu('BaseSetting', 'Notice', 'editNotice');
        //查询当前用户是否有权限进行删除公告
        $deleteNotice = get_menu('BaseSetting', 'Notice', 'deleteNotice');
        //查询所属医院
        $hospitals = $this->DB_get_all('hospital', '*', array('is_delete' => 0));
        $hoskeyvalue = array();
        foreach ($hospitals as $k => $v) {
            $hoskeyvalue[$v['hospital_id']] = $v['hospital_name'];
        }
        foreach ($noticeinfo as $k => $v) {
            $noticeinfo[$k]['hospital_name'] = $hoskeyvalue[$v['hospital_id']];
            $html = '<div class="layui-btn-group">';
            if ($v['top'] == 1) {
                $noticeinfo[$k]['top_name'] = '<span style="color:#FF5722">是</span>';
            }else{
                $noticeinfo[$k]['top_name'] = '<span>否</span>';
            }
            $html .= $this->returnButtonLink('查看', C('ADMIN_NAME').'/Notice/getNoticeList', 'layui-btn layui-btn-xs layui-btn-normal', '', 'lay-event = showNotice');
            if ($editNotice) {
                $html .= $this->returnButtonLink('修改', $editNotice['actionurl'], 'layui-btn layui-btn-xs', '', 'lay-event = editNotice');
            }
            if ($deleteNotice) {
                $html .= $this->returnButtonLink('删除', $deleteNotice['actionurl'], 'layui-btn layui-btn-xs layui-btn-danger', '', 'lay-event = deleteNotice');
            }
            $html .= '</div>';
            $noticeinfo[$k]['operation'] = $html;
        }
        $result['total'] = $total;
        $result["offset"] = $offset;
        $result["limit"] = $limit;
        $result["code"] = 200;
        $result['rows'] = $noticeinfo;
        if (!$result['rows']) {
            $result['msg'] = '暂无相关数据';
            $result['code'] = 400;
        }
        return $result;
    }

    public function getNoticeFile($id)
    {
        //查找相应文件
        $files = $this->DB_get_all('notice_file','*',array('notid'=>$id,'is_delete'=>0));
        foreach ($files as $k=>$v){
            switch($v['file_type']){
                case 'pdf':
                    $pic = '<img src="/Public/images/pdf.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
                case 'doc':
                case 'docx':
                    $pic = '<img src="/Public/images/word.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
                case 'xls':
                case 'xlsx':
                case 'csv':
                    $pic = '<img src="/Public/images/excel.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
                case 'txt':
                    $pic = '<img src="/Public/images/text.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
                default:
                    $pic = '<img src="/Public/images/word.png" style="width: 20px;padding-bottom:3px;margin-right:5px;"/>';
                    break;
            }
            $files[$k]['img'] = $pic;
        }
        return $files;
    }
}