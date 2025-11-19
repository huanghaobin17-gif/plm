/**
 * Created by tcdahe on 2018/5/4.
 */
layui.use(['layer', 'form', 'element','table','upload'], function() {
    var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table,upload = layui.upload;
    //先更新页面部分需要提前渲染的控件
    form.render();

    //监听保存按钮
    form.on('submit(save)',function (data) {
        var params = {};
        params.qsid = $('input[name="qsid"]').val();
        params.url = $('input[name="url"]').val();
        params.actionType = 'savepic';
        submit($,params,$(this).attr('data-url'));
        return false;
    });
    var qsid = $('input[name="qsid"]').val();
    //上传文件
    var uploadFile = upload.render({
        elem: '#uploadReportFile'  //绑定元素
        ,url: $(this).attr('data-url') //接口
        ,accept: 'file'
        ,exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
        , method: 'POST'
        , data: {actionType: 'upload'}
        ,choose: function(obj){
            //选择文件后
        }
        ,done: function(res, index, upload){
            layer.closeAll('loading');
            if (res.status == 1) {
                var path = res.path;
                $('input[name="url"]').val(path);
                $('#divpic').show();
                $('#img').attr('src',path);
                layer.msg(res.msg,{icon : 1},1000);
            }else{
                layer.msg(res.msg,{icon : 2},1000);
            }
        }
        ,error: function(index, upload){
            //失败
        }
    });
    //监听保存按钮
    form.on('submit(upload)',function (data) {
        return false;
    });
});
