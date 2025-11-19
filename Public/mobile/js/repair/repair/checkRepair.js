layui.use('form', function() {
    var form = layui.form;

    //初始化下方导航栏菜单
    menuListSpread();

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

    /*确认并提交数据*/
    form.on('submit(save)', function(data){
        var params = data.field;
        var msg = '确认验收该设备吗？';
        if(params.repaired == 1){
            msg = '确认该设备已修复并验收？';
        }else{
            msg = '确认该设备未修复？';
        }
        layer.confirm(msg,{icon: 3, title:'维修验收提交确认'}, function(index){
            submit(params, url,mobile_name+'/Repair/examine.html');
            layer.close(index);
        });
        return false;
    });
});