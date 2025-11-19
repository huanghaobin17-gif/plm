layui.define(function(exports){
    layui.use(['form'], function () {
        var form = layui.form, $ = layui.jquery;
        //监听提交
        form.on('submit(save)', function (data) {
            params = data.field;
            if(params.is_adopt == 1){
                tips = '确认通过审核？';
            }else{
                tips = '确认驳回申请？';
            }
            layer.confirm(tips, {icon: 3, title: $(this).html()}, function (index) {
                submit($, params, params.action);
            });
            return false;
        });
        //下载
        $(document).on('click','.downFile',function () {
            var params={};
            params.path= $(this).data('path');
            params.filename=$(this).data('name');
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data:params,
                method:'POST'
            });
            return false;
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

        //播放停止语音
        var audio = document.getElementById("audio");
        if(audio){
            var btn = document.getElementById("media");
            btn.onclick = function () {
                if (audio.paused) { //判断当前的状态是否为暂停，若是则点击播放，否则暂停
                    audio.play();
                }else{
                    audio.pause();
                }
            };
        }

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
    exports('controller/repair/repair/addApprove', {});
});
