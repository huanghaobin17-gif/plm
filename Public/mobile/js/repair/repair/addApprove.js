layui.use(['form'], function () {
    var form = layui.form,bodyObj = $("body");

    //初始化下方导航栏菜单
    menuListSpread();


    //表单置顶
    bodyObj.on("click", ".upFormButton", function () {
        var formDivObj = $(".formDiv"), emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
        /*动画区域*/
        var moveAnimatedDivObj = $(".moveAnimatedDiv");
        moveAnimatedDivObj.removeClass("animated fadeIn");
        setTimeout(function () {
            moveAnimatedDivObj.addClass("animated fadeIn");
        }, 100);
        emptyDivObj.addClass("animated slideInUp");
        $('html, body').animate({scrollTop: '0px'},100);
        localStorage.setItem("addApprove_upFormButton",'top');
        form.render();
    });
    //表单置底
    bodyObj.on("click", ".downFormButton", function () {
        var formDivObj = $(".formDiv"), emptyDivObj = $(".emptyDiv");
        formDivObj.append(emptyDivObj.html());
        formDivObj.show();
        emptyDivObj.html("");
        emptyDivObj.hide();
        $(".downFormButton").hide();
        $(".upFormButton").show();
        /*动画区域*/
        var moveAnimatedDivObj = $(".moveAnimatedDiv");
        moveAnimatedDivObj.removeClass("animated fadeIn");
        setTimeout(function () {
            moveAnimatedDivObj.addClass("animated fadeIn");
        }, 100);
        formDivObj.addClass("animated slideInDown");

        $('html, body').animate({scrollTop: $(document).height()},100);
        localStorage.setItem("addApprove_upFormButton",'bottom');
        form.render();
    });

    var addApprove_upFormButton = localStorage.getItem("addApprove_upFormButton");
    if(addApprove_upFormButton == 'top'){
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
        form.render();
    }

    //语音播放
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

    $(".gray").click(function () {
        var result = [];
        $(this).parent().find(".imageUrl").each(function (k, v) {
            result.push($(v).val());
        });

        var thisPhotos = $.photoBrowser({items: result});
        thisPhotos.open();
    });
    /*最终提交数据*/
    form.on('submit(submit)', function(data){
        var params = data.field;
        var msg = '确认提交吗？';
        if(params.is_adopt == 1){
            msg = '确认通过审批吗？';
        }else{
            msg = '确认驳回申请吗？';
        }
        layer.confirm(msg,{icon: 3, title:'维修审批提交确认'}, function(index){
            submit(params, addApproveUrl,mobile_name+'/Notin/approve.html');
            layer.close(index);
        });
        return false;
    });

    //进页面优化第三方样式
    $(".companyDiv .layui-card-body:first").css("margin-top",0);
});