layui.define(function (exports) {
    layui.use(['form', 'layedit'], function () {
        var form = layui.form, layedit = layui.layedit;

        layedit.set({
            uploadImage: {
                url: admin_name+'/Tool/addLayerImg' //接口url
            }
        });
        index = layedit.build('editor'); //建立编辑器

        //监听提交
        form.on('submit(save)', function (data) {
            var params = data.field;
            params.action = 'editBasis';
            params.content = layedit.getContent(index);
            if (!params.content) {
                layer.msg('请填写检测依据内容', {icon: 2}, 1000);
                return false;
            } else {
                submit($, params, url);
                return false;
            }
        });
    });
    exports('controller/qualities/quality/editBasis', {});
});