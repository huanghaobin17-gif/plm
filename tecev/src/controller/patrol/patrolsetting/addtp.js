layui.define(function(exports){

    layui.use(['form','suggest'], function() {
        var form = layui.form(), $ = layui.jquery,suggest = layui.suggest;

        //初始化搜索建议插件
        suggest.search();

        $("#testNoBtn").bsSuggest({
            url: admin_name+'/PatrolSetting/getAllTemplate',
            /*effectiveFields: ["userName", "shortAccount"],
             searchFields: [ "shortAccount"],*/
            effectiveFields: ["tpid", "name","level"],
            effectiveFieldsAlias:{tpid:"序号",name:"模板名称",level:"保养级别"},
            ignorecase: false,
            showHeader: true,
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            idField: "tpid",
            keyField: "name",
            clearable: false
        }).on('onDataRequestSuccess', function (e, result) {
            //console.log('onDataRequestSuccess: ', result);
        }).on('onSetSelectValue', function (e, keyword, data) {
            //正确
            $("input[name='num']:checkbox").each(function() {
                if ($.inArray($(this).val(),data.num) != -1){
                    $(this).attr('checked','checked');
                    $(this).attr('disabled','disabled');
                    $(this).parent().parent().parent().parent().parent().parent().find('.allchoose').find('input').attr('disabled','disabled');
                    var html = '已选择'+($(this).parent().parent().parent().parent().parent().parent().find('.choose').find('input:checked').length)+'项';
                    $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html)
                }
            });
            var form = layui.form();
            form.render('checkbox');
        }).on('onUnsetSelectValue', function () {
            //不正确
        });

        form.on('checkbox(allChoose)', function(data){
            var child = $(data.elem).parents('table').find('tbody input[type="checkbox"]');
            child.each(function(index, item){
                item.checked = data.elem.checked;
                if (item.checked){
                    var html = '已选择'+($(this).parent().parent().parent().parent().parent().parent().find('.choose').find('input:checked').length)+'项';
                    $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html);
                }else{
                    var html = '已选择0项';
                    $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html);
                }
            });
            form.render('checkbox');
        });
        form.on('checkbox(Choose)', function(data){
            if (data.elem.checked){
                var html = '已选择'+($(this).parent().parent().parent().parent().parent().parent().find('.choose').find('input:checked').length)+'项';
                $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html);
            }else{
                var html = '已选择'+($(this).parent().parent().parent().parent().parent().parent().find('.choose').find('input:checked').length)+'项';
                $(this).parent().parent().parent().parent().parent().parent().find('.many').html(html);
            }
            form.render('checkbox');
        });
        form.verify({
            name: function (value, item) {
                if (value){
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '模板名称首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '模板名称不能全为数字';
                    }
                }else {
                    return '模板名称不能为空';
                }
            },
            level: function (value,item) {
                if(value == ''){
                    return '请选择保养级别';
                }
            }
        });
        //监听提交
        form.on('submit(add)', function (data) {
            var num = "";
            $("input[name='num']:checked").each(function(){
                if(num==''){
                    num = $(this).val();
                }else{
                    num += ","+$(this).val();
                }
            });
            params = data.field;
            params.num = num;
            submit($,params,'addTp');
            return false;
        })
    });

    function submit($,params,url){
        $.ajax({
            timeout: 5000,
            type: "POST",
            url: url,
            data: params,
            dataType: "json",
            success: function (data) {
                if (data) {
                    if (data.status == 1) {
                        CloseWin(data.msg);
                    }else{
                        layer.msg(data.msg,{icon : 2},1000);
                    }
                }else {
                    layer.msg("数据异常！",{icon : 2},1000);
                }
            },
            error: function () {
                layer.msg("网络访问失败",{icon : 2},1000);
            }
        });
    }
//关闭页面
    function CloseWin(msg) {
        var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
        parent.layer.msg(msg,{icon : 1},1000);
        layer.close(index); //再执行关闭
    }

    layui.use('element', function(){
        var element = layui.element;
    });
    exports('controller/patrol/patrolsetting/addtp', {});
});










