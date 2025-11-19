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
        localStorage.setItem("scrap_approve_upFormButton",'top');
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
        localStorage.setItem("scrap_approve_upFormButton",'bottom');
        form.render();
    });

    var scrap_approve_upFormButton = localStorage.getItem("scrap_approve_upFormButton");
    if(scrap_approve_upFormButton == 'top'){
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
        form.render();
    }

    /*最终提交数据*/
    form.on('submit(submit)', function(data){
        var params = data.field;
        var msg = '确认提交吗？';
        if(params.is_adopt == 1){
            msg = '确认通过审批吗？';
        }else{
            msg = '确认驳回申请吗？';
        }
        layer.confirm(msg,{icon: 3, title:'报废审批提交确认'}, function(index){
            submit(params, url,mobile_name+'/Notin/approve.html');
            layer.close(index);
        });
        return false;
    });

    //进页面优化第三方样式
    $(".companyDiv .layui-card-body:first").css("margin-top",0);
});