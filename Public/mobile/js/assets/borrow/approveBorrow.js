layui.use(['form','formSelects'], function () {
    var form = layui.form, formSelects = layui.formSelects;

    //初始化下方导航栏菜单
    menuListSpread();

    $("#showImages").click(function(){
        photos.open();
    });

    //监听通知-接单步骤
    form.on('submit(submit)', function (data) {
        var params = data.field;
        var msg = '确认提交吗？';
        if(params.is_adopt == 1){
            msg = '确认通过审批吗？';
        }else{
            msg = '确认驳回申请吗？';
        }
        layer.confirm(msg,{icon: 3, title:'借调审批提交确认'}, function(index){
            submit(params, approveBorrowUrl,mobile_name+'/Notin/approve.html');
            layer.close(index);
        });
        return false;
    });



    /*表单移动按钮*/
    var bodyObj = $("body");
    bodyObj.on("click",".upFormButton", function() {
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
        /*动画区域*/
        var moveAnimatedDivObj = $(".moveAnimatedDiv");
        moveAnimatedDivObj.removeClass("animated fadeIn");
        setTimeout(function(){
            moveAnimatedDivObj.addClass("animated fadeIn");
        },100);
        emptyDivObj.addClass("animated slideInUp");
        $('html, body').animate({scrollTop: '0px'},100);
        localStorage.setItem("accept_upFormButton",'top');
        form.render();
    });
    bodyObj.on("click",".downFormButton", function() {
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        formDivObj.append(emptyDivObj.html());
        formDivObj.show();
        emptyDivObj.html("");
        emptyDivObj.hide();
        $(".downFormButton").hide();
        $(".upFormButton").show();
        /*动画区域*/
        var moveAnimatedDivObj = $(".moveAnimatedDiv");
        moveAnimatedDivObj.removeClass("animated fadeIn");
        setTimeout(function(){
            moveAnimatedDivObj.addClass("animated fadeIn");
        },100);
        formDivObj.addClass("animated slideInDown");
        $('html, body').animate({scrollTop: $(document).height()},100);
        localStorage.setItem("accept_upFormButton",'bottom');
        form.render();
    });

    var accept_upFormButton = localStorage.getItem("accept_upFormButton");
    if(accept_upFormButton == 'top'){
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
        form.render();
    }
    /**/

});