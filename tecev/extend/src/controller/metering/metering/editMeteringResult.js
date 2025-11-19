layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'upload'], function () {
        var layer = layui.layer, form = layui.form, laydate = layui.laydate, upload = layui.upload, table = layui.table,$ = layui.jquery;
        //先更新页面部分需要提前渲染的控件
        form.render();
        laydate.render({
            elem: '#nowDate',
            festival: true
        });
        //监听提交
        form.on('submit(doResult)', function (data) {
            var params = data.field;

            if(params.report_num===''){
                layer.msg('请补充证书编号', {icon: 2});
                return false;
            }
            var formerly = '', fileName = '';
            $('.file_url tr').each(function () {
                formerly += $(this).find('.formerly').html() + '|';
                fileName += $(this).find('.fileName').find('input[name="path"]').val() + '|';
            });
            params.formerly = formerly;
            params.fileName = fileName;
            params.type = 'editMeteringResult';
            var url= admin_name+'/Metering/setMeteringResult.html';
            submit($, params, url);
            return false;
        });
        //上传文件
        uploadFile = upload.render({
            elem: '#file_url'  //绑定元素
            , url: admin_name+'/Metering/setMeteringResult' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {type: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res, index, upload) {
                layer.closeAll('loading');
                if (res.status == 1) {
                    $(".nofile").remove();
                    $(".file_url").append('<tr><td class="formerly">' + res.formerly + '</td><td>' + res.adduser + '</td><td>' + res.thisTime + '</td><td class="fileName"><input type="hidden" name="path" class="path" value="' + res.path + '"><a style="color: red;text-decoration: none;cursor: pointer;"  onclick="delDoRenewaFile(this)">  删除  </a></td></tr>');
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });
    });
    //预览
    $(document).on('click','.showFile',function () {
        var path= $(this).data('path');
        var name=$(this).data('name');
        var url=admin_name+'/Tool/showFile';
        top.layer.open({
            type: 2,
            title: name + '相关文件查看',
            scrollbar: false,
            offset: 'r',//弹窗位置固定在右边
            anim: 2, //动画风格
            area: ['75%', '100%'],
            closeBtn: 1,
            content: [url +'?path=' + path + '&filename=' + name]
        });
        return false;
    });
    $(document).on('click','.delFile',function () {
        $(this).parent().parent().parent().remove();
        layer.msg('删除成功', {icon: 1}, 1000);
        return false;
    });
    exports('controller/metering/metering/editMeteringResult', {});
});
