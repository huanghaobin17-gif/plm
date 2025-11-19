layui.define(function(exports){
    layui.use(['form','suggest'], function(){
        var form = layui.form,suggest = layui.suggest;

        //初始化搜索建议插件
        suggest.search();

        form.verify({
            approvename: function(value,item){
                if (/^\d+\d$/.test(value)) {
                    return '审批名称不能全为数字';
                }
                if(value.length < 2){
                    return '审批名称至少2个字符';
                }
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '审批名称首尾不能出现下划线\'_\'';
                }
            },
            listorder: function(value,item){
                var reg = /^[0-9]*[1-9][0-9]*$/;
                if (!reg.test(value)) {
                    return '请输入正整数排序';
                }
            }
        });
        //监听提交
        form.on('submit(addDetail)', function(data){
            var params = data.field;
            var username = $("input[name='username']").val();
            if(!username){
                layer.msg("请选择审批人",{icon : 2},1000);
                return false;
            }

            $.ajax({
                type:"POST",
                url:params.action,
                data:params,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                beforeSend:function(){
                    layer.load(1);
                },
                //成功返回之后调用的函数
                success:function(data){
                    layer.closeAll('loading');
                    if(data.status == 1){
                        CloseWin(data.msg,1,1);
                    }else{
                        layer.msg(data.msg,{icon : 2},2000);
                    }
                },
                //调用出错执行的函数
                error: function(){
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 2});
                }
            });
            return false;
        });


        var url = $('input[name="action"]').val();
        /*
         /选择用户
         */
        $("#testNoBtn").bsSuggest({
            url: url+'?type=getUserList',
            /*effectiveFields: ["userName", "shortAccount"],
             searchFields: [ "shortAccount"],*/
            effectiveFieldsAlias:{usernum: "工号",username:"用户名",telephone:"电话"},
            ignorecase: false,
            showHeader: true,
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            idField: "usernum",
            keyField: "username",
            clearable: false
        }).on('onDataRequestSuccess', function (e, result) {
            //console.log('onDataRequestSuccess: ', result);
        }).on('onSetSelectValue', function (e, keyword, data) {
            $("input[name='username']").val(data.username);
        }).on('onUnsetSelectValue', function () {
            //不选择用户时的操作
            $("input[name='username']").val('');
        });
    });
//关闭页面
    function CloseWin(msg,num,close) {
        parent.layer.msg(msg,{icon : num,time:2000},function(){
            if(close){
                var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                parent.layer.close(index); //再执行关闭
                parent.location.reload(); // 父页面刷新
            }
        });
    }
    exports('controller/basesetting/approveSetting/addDetail', {});
});

