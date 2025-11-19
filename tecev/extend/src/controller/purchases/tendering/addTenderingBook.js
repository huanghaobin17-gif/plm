layui.define(function(exports){
    layui.use(['form','upload'], function(){
        var form = layui.form,upload = layui.upload;

        //上传附件
        upload.render({
            elem: '#addTenderingBookFile'  //绑定元素
            , url: addTenderingBook //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf|zip' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'uploadFile'}
            , choose: function (obj) {

            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。

            }
            , done: function (res) {
                if (res.status == 1) {
                    if($('.file_list').find('.no-data').length == 1){
                        $('.file_list').find('.no-data').remove();
                    }
                    if($('.file_list').find('.old_list').length == 1){
                        $('.file_list').find('.old_list').remove();
                    }
                    var xuhao = $('.file_list').find('.old_list').length;
                    var tr = '';
                    tr += '<tr class="old_list">\n' +
                        '<td class="xuhao">'+(xuhao+1)+'</td>\n' +
                        '<td class="file_name">'+res.file_name+'</td>\n' +
                        '<td class="file_size">'+res.file_size+'</td>\n' +
                        '<td class="add_user">'+res.add_user+'</td>\n' +
                        '<td class="add_time">'+res.add_time+'</td>\n' +
                        '<td>' +
                        '<input type="hidden" class="file_name" name="file_name" value="'+res.file_name+'"/>' +
                        '<input type="hidden" class="file_size" name="file_size" value="'+res.file_size+'"/>' +
                        '<input type="hidden" class="file_type" name="file_type" value="'+res.file_type+'"/>' +
                        '<input type="hidden" class="file_url" name="file_url" value="'+res.file_url+'"/>' +
                        '<input type="hidden" class="save_name" name="save_name" value="'+res.save_name+'"/>' +
                        '<button type="button" class="layui-btn layui-btn-xs  layui-btn-danger delFile" lay-submit lay-filter="delFile">移除</button>' +
                        '</td>\n' +
                        '</tr>';
                    $('.file_list').append(tr);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });

        $("body").on('click','.delFile',function () {
            $(this).parent().parent().remove();
            var target = $('.file_list').find('.xuhao');
            if(target.length > 0){
                $.each(target,function (index,item) {
                    $(item).html(index+1);
                });
            }else{
                var tr = '<tr class="no-data">\n' +
                    '                        <td colspan="6" style="text-align: center !important;">暂无相关记录</td>\n' +
                    '                    </tr>';
                $('.file_list').append(tr);
            }
        });
        //监听提交
        form.on('submit(saveTender)', function(data){
            var url = addTenderingBook;
            var params = data.field;
            params.action = 'finalSave';
            submit($,params,url);
            return false;
        });

        //提示是否下载/预览
        $(document).on('click', '.downFile', function () {
            var params = {};
            params.path = $(this).data('path');
            params.filename = $(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data: params,
                method: 'POST'
            });
        });

    });
    exports('controller/purchases/tendering/addTenderingBook', {});
});
