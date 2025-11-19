layui.use('form', function() {
    var form = layui.form;

    //初始化下方导航栏菜单
    menuListSpread();

    //跟进时间选择器

    $("#partsOutWareApplyAddtime").calendar();

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
        if(params.expect_time){
            var reg=/\//g;
            params.expect_time=params.expect_time.replace(reg,'-');
        }
        var parts_tr = $('.parts_tr');
        if (parts_tr.length === 0) {
            $.toptip('异常申请单', 'error');
            return false;
        }

        var error = false;
        var msg = '';
        var parts = '', parts_model = '', sum = '', apply_sum = '';
        $.each(parts_tr, function (key, value) {
            var parts_val = $(this).find('input[name="parts"]').val();
            var parts_model_val = $(this).find('input[name="parts_model"]').val();
            var apply_sum_val = $(this).find('input[name="apply_sum"]').val();
            var sum_val = $(this).find('input[name="sum"]').val();
            if (!check_num(sum_val)) {
                error = true;
                msg = '配件：' + parts_val + ' 请输入合理的出库数量';
                return false;
            } else {
                if (parseInt(sum_val) < parseInt(apply_sum_val)) {
                    error = true;
                    msg = '出库配件：' + parts_val + ' 数量不能少于申请的数量' + apply_sum_val;
                    return false;
                }
            }
            parts += parts_val + '|';
            parts_model += parts_model_val + '|';
            sum += sum_val + '|';
            apply_sum += apply_sum_val + '|';
        });
        if (error) {
            $.toptip(msg, 'error');
            return false;
        }
        params.parts = parts;
        params.parts_model = parts_model;
        params.sum = sum;
        params.apply_sum = apply_sum;
        submit(params, partsOutWareUrl,mobile_name+'/RepairParts/partStockList.html');
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
        localStorage.setItem("partsOutWare_upFormButton",'top');
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
        localStorage.setItem("partsOutWare_upFormButton",'bottom');
        form.render("select");
    });

    var partsOutWare_upFormButton = localStorage.getItem("partsOutWare_upFormButton");
    if(partsOutWare_upFormButton == 'top'){
        var formDivObj= $(".formDiv"),emptyDivObj = $(".emptyDiv");
        emptyDivObj.append(formDivObj.html());
        emptyDivObj.show();
        formDivObj.html("");
        formDivObj.hide();
        $(".upFormButton").hide();
        $(".downFormButton").show();
    }

});