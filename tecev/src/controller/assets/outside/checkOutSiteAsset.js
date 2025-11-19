layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'laydate', 'upload','tipsType'], function () {
        var layer = layui.layer,
            form = layui.form,
            laydate = layui.laydate,
            upload = layui.upload,
            table = layui.table,
            tipsType = layui.tipsType,
            $ = layui.jquery;
        //先更新页面部分需要提前渲染的控件
        form.render();
        tipsType.choose();

        //日期初始化
        laydate.render({
            elem: '#check_date',
            calendar: true,
            min: '1'
        });



        //监听提交
        form.on('submit(checkOutSite)', function (data) {
            console.log(submit);
            var params = data.field;
            if(!params.check_date){
                layer.msg('请补充验收时间', {icon: 2});
                return false;
            }
            if(params.check_phone){
                if(!checkTel(params.check_phone)){
                    layer.msg('请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符', {icon: 2});
                    return false;
                }
            }
            //获取上传文件信息
            var fileDataTr = $('.fileDataTr');
            if (fileDataTr.length > 0) {
                params.file_name = '';
                params.save_name = '';
                params.file_type = '';
                params.file_size = '';
                params.file_url = '';
                $.each(fileDataTr, function (key, value) {
                    params.file_name += $(value).find('input[name="file_name"]').val() + '|';
                    params.save_name += $(value).find('input[name="save_name"]').val() + '|';
                    params.file_type += $(value).find('input[name="file_type"]').val() + '|';
                    params.file_size += $(value).find('input[name="file_size"]').val() + '|';
                    params.file_url += $(value).find('input[name="file_url"]').val() + '|';
                });
            }
            submit($, params, checkOutSiteAssetUrl);
            return false;
        });

        console.log(1);
        //上传文件
        uploadFile = upload.render({
            elem: '#outSideCheckFile'  //绑定元素
            , url: checkOutSiteAssetUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var notFileDataTr = $('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = $('.addFileTbody');
                    var input = '<input type="hidden" name="file_name" value="' + res.formerly + '">';
                    input += '<input type="hidden" name="save_name" value="' + res.title + '">';
                    input += '<input type="hidden" name="file_type" value="' + res.ext + '">';
                    input += '<input type="hidden" name="file_size" value="' + res.size + '">';
                    input += '<input type="hidden" name="file_url" value="' + res.path + '">';
                    var html = '<tr class="fileDataTr">';
                    html += '<td class="fileName">' + input + res.formerly + '</td>';
                    html += '<td class="addFileuser">' + res.adduser + '</td>';
                    html += '<td class="addFileTime">' + res.thisTime + '</td>';
                    html += '<td><div class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</div></td>';
                    addFileTbody.append(html);
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });

        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = $('.addFileTbody');
            thisTr.remove();
            if (addFileTbody.find('tr').length === 0) {
                addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
            }
            layer.msg('移除成功', {icon: 1}, 1000);
        });



    });
    exports('controller/assets/outside/checkOutSiteAsset', {});
});
