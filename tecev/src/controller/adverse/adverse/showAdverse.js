layui.define(function(exports){
    layui.use('form', function(){
        var form = layui.form;

        //先更新页面部分需要提前渲染的控件
        form.render();

    });

//查看上传文件
    $(document).on('click', '.showUpload', function (){
        var fileNameObj = $(".fileName");
        var suffix = fileNameObj.attr('data-type');
        var path = fileNameObj.attr('data-path');
        var id = $("input[name='id']").val();
        var name = fileNameObj.attr('data-name');
        if (suffix == 'doc'){
            window.location.href=admin_name+'/Adverse/getAdverseList?path='+path+'&name='+name+'&type=downFile';
            return false;
        }else if(suffix == 'docx'){
            window.location.href=admin_name+'/Adverse/getAdverseList?path='+path+'&name='+name+'&type=downFile';
            return false;
        }else {
            top.layer.open({
                id: 'showFiles',
                type: 2,
                title: '附件查看',
                scrollbar: false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['75%', '100%'],
                closeBtn: 1,
                content: [admin_name+'/Adverse/getAdverseList?id='+id+'&type=showUpload']
            });
            return false;
        }
    });
    exports('controller/adverse/adverse/showAdverse', {});
});
