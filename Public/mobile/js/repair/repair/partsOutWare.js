layui.use(['form'], function () {
    var form = layui.form,bodyObj = $("body");

    form.on('submit(a)', function(data){
        var params = data.field;
        return false;
    });

    form.on('submit(b)', function(data){
        var params = data.field;
        return false;
    });


    //表单置顶
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
    });
    //表单置底
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
    });
});