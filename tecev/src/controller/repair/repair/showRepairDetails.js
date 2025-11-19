layui.define(function(exports){
    layui.use(['layer'], function () {
        var layer = layui.layer;
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
    });

    exports('controller/repair/repair/showRepairDetails', {});
});
