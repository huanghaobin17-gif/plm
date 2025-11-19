layui.define(function(exports){
    layui.use(['upload', 'tipsType', 'layer'], function () {
        var upload = layui.upload, tipsType = layui.tipsType, layer = layui.layer;
        //初始化tips的选择功能
        tipsType.choose();

        var thisbody = $('#LAY-Repair-RepairSearch-showRepair');

        $("#showImages").click(function () {
            var result = {};
            result.start = 0;
            result.data = [];
            $(".imageUrl").each(function (k, v) {
                var imageUrlObj = {};
                imageUrlObj.src = $(v).val();
                imageUrlObj.thumb = $(v).val();
                result.data.push(imageUrlObj);
            });
            //显示相册层
            layer.photos({
                photos: result
                , anim: 5
                , maxmin: false
            });
        });
        //上传附件
        var fileData = {};
        uploadFile = upload.render({
            elem: '#showRepairFile'  //绑定元素
            , url: showRepairUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: fileData
            , choose: function (obj) {
                //选择文件后
                fileData.action = 'upload';
                fileData.repid = thisbody.find('input[name="repid"]').val();
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var notFileDataTr = thisbody.find('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = thisbody.find('.addFileTbody');
                    var html = '<tr class="fileDataTr isAddFile">';
                    html += '<td class="file_name">' + res.formerly + '</td>';
                    html += '<td class="add_user">' + res.add_user + '</td>';
                    html += '<td class="add_time">' + res.add_time + '</td>';
                    html += '<div class="layui-btn-group">';

                    var button = '<button class="layui-btn layui-btn-xs downFile" lay-event="" style="" data-url="" data-path="' + res.path + '" data-name="' + res.formerly + '">下载</button>';
                    if (res.file_type !== 'doc' || res.file_type !== 'docx') {
                        button += '<button class="layui-btn layui-btn-xs layui-btn-normal showFile" lay-event="" style="" data-url="" data-path="' + res.path + '" data-name="' + res.formerly + '">预览</button>';
                    }
                    html += '<td><div class="layui-btn-group">' + button + '</div></td>';
                    html += '</div>';
                    addFileTbody.append(html);
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function () {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });

        //下载
        $(document).on('click', '.downFile', function () {
            var params = {};
            params.path = $(this).data('path');
            params.filename = $(this).data('name');
            postDownLoadFile({
                url: admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
            return false;
        });

        var reportData = {};
        uploadFile = upload.render({
            elem: '#uploadReport'  //绑定元素
            , url: showRepairUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: reportData
            , choose: function (obj) {
                //选择文件后
                reportData.idName = 'rep';
                reportData.action = 'uploadReport';
                reportData.id = repid;
            }
            , done: function (res) {
                if(res.status == 1){
                    location.reload();//刷新
                }
            }
            , error: function () {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });

        var processData = {};
        uploadFile = upload.render({
            elem: '#uploadProcess'  //绑定元素
            , url: showRepairUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: processData
            , choose: function (obj) {
                //选择文件后
                processData.action = 'uploadStartRepairFile';
                processData.id = repid;
            }
            , done: function (res) {
                if(res.status == 1){
                    location.reload();//刷新
                }
            }
            , error: function () {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });


        //预览
        $(document).on('click', '.showFile', function () {
            var path = $(this).data('path');
            var name = $(this).data('name');
            var url = admin_name+'/Tool/showFile';
            top.layer.open({
                type: 2,
                title: name + '相关文件查看',
                scrollbar: false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [url + '?path=' + path + '&filename=' + name]
            });
            return false;
        });


        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = thisbody.find('.addFileTbody');
            var params = {};
            params.action = 'deleteFile';
            params.file_id = $(this).siblings('input[name="file_id"]').val();

            if (parseInt(params.file_id) > 0) {
                delfileid.push(params.file_id);
            }
            thisTr.remove();
            if (addFileTbody.find('tr').length === 0) {
                addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
            }
            return false;
        });


        $(".layui-timeline-content").first().append('<span class="newTips">最新</span>');
        $(".newTips").siblings('.layui-timeline-title').css('color', '#0af');
    });
    exports('controller/repair/search/showRepair', {});
});
