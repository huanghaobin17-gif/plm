layui.use(['form'], function () {
    var form = layui.form;

    //初始化下方导航栏菜单
    menuListSpread();

    $("#showImages").click(function(){
        photos.open();
    });

    //监听通知-接单步骤
    form.on('submit(confirm)', function (data) {
        if (parseInt(data.field['expect_arrive']) > parseInt(data.field['uptime'])) {
            $.toptip('预计到场时间最大不能超过' + data.field['uptime'] + '分钟', 'error');
        }else if(!data.field['expect_arrive']){
            $.toptip('请填写预计到场时间', 'error');
        } else {
            var params = data.field;
            params.action='accept';
            submit(params, acceptUrl,mobile_name+'/Repair/ordersLists.html');
        }
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
        form.render("select");
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
        form.render("select");
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
    }
    /**/

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
});