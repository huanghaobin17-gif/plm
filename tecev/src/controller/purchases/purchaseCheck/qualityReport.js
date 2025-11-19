layui.define(function(exports){
    layui.use(['form','upload'], function(){
        var $ = layui.$
            ,upload = layui.upload
            ,form = layui.form;
        form.render();
        //监听提交
        form.on('submit(saveQualityReport)', function(data){
            var params = data.field;
            params.action = 'finalSave';
            submit($,params,'qualityReport');
            return false;
        });
        $("#addY").on('click',function () {
            return false;
        });
        $("#addM").on('click',function () {
            return false;
        });
        $("#addH").on('click',function () {
            return false;
        });
        $("#addC").on('click',function () {
            return false;
        });
        //上传附件
        upload.render({
            elem: '#addY'  //绑定元素
            , url: admin_name+'/PurchaseCheck/qualityReport' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf|zip' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'uploadFile',wid:$('input[name="wid"]').val(),oid:$('input[name="oid"]').val(),type_code:'Y'}
            , choose: function (obj) {

            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。

            }
            , done: function (res) {
                if (res.status == 1) {
                    location.reload();
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });
        upload.render({
            elem: '#addM'  //绑定元素
            , url: admin_name+'/PurchaseCheck/qualityReport' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf|zip' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'uploadFile',wid:$('input[name="wid"]').val(),oid:$('input[name="oid"]').val(),type_code:'M'}
            , choose: function (obj) {

            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。

            }
            , done: function (res) {
                if (res.status == 1) {
                    location.reload();
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });
        upload.render({
            elem: '#addH'  //绑定元素
            , url: admin_name+'/PurchaseCheck/qualityReport' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf|zip' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'uploadFile',wid:$('input[name="wid"]').val(),oid:$('input[name="oid"]').val(),type_code:'H'}
            , choose: function (obj) {

            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。

            }
            , done: function (res) {
                if (res.status == 1) {
                    location.reload();
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });
        upload.render({
            elem: '#addC'  //绑定元素
            , url: admin_name+'/PurchaseCheck/qualityReport' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf|zip' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'uploadFile',wid:$('input[name="wid"]').val(),oid:$('input[name="oid"]').val(),type_code:'C'}
            , choose: function (obj) {

            }
            ,before: function(obj){ //obj参数包含的信息，跟 choose回调完全一致，可参见上文。

            }
            , done: function (res) {
                if (res.status == 1) {
                    location.reload();
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
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
                area: ['70%', '100%'],
                closeBtn: 1,
                content: [url +'?path=' + path + '&filename=' + name]
            });
            return false;
        });
    });
    exports('controller/purchases/purchaseCheck/qualityReport', {});
});
