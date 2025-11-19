layui.define(function(exports){
    layui.use(['layer', 'form','tipsType'], function () {
        var form = layui.form, $ = layui.jquery, layer = layui.layer,tipsType = layui.tipsType;
        tipsType.choose();
        form.verify({
            s_price: function(value,item){
                var reg = /(^[1-9]([0-9]+)?(\.[0-9]{1,2})?$)|(^(0){1}$)|(^[0-9]\.[0-9]([0-9])?$)/;
                if (!reg.test(value)) {
                    return "请输入合理的金额";
                }
            }
        });
        //获取审批人
        form.on('select(pri)',function (data) {
            var params  = {};
            params.name = data.value;
            params.hospital_id = $('input[name="hospital_id"]').val();
            params.action = 'getRoleUsers';
            if(params.name == '关联部门'){
                html = getHtml();
                $('.changeOption').html('');
                $('.changeOption').append(html);
            }else{
                html = getHtml2();
                $('.changeOption').html('');
                $('.changeOption').append(html);
                //获取角色用户
                $.ajax({
                    type:"POST",
                    url:admin_name+'/ApproveSetting/editProcess.html',
                    data:params,
                    //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                    //成功返回之后调用的函数
                    success:function(data){
                        if(data){
                            var html = '<option value="">直接选择或搜索选择</option>';
                            $('.users').html('');
                            $.each(data,function (index,item) {
                                html += '<option value="'+item.username+'">'+item.username+'</option>';
                            });
                            $('.users').append(html);
                            form.render();
                        }
                    },
                    //调用出错执行的函数
                    error: function(){
                        //请求出错处理
                        layer.msg('服务器繁忙', {icon: 2});
                    }
                });
            }
        });
        //监听提交
        form.on('submit(editProcess)', function (data) {
            params = data.field;
            if(params.min > parseFloat(params.s_price)){
                layer.msg('审核金额不能少于'+params.min, {icon: 2,time:2000});
                return false;
            }
            var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
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
                        layer.msg(data.msg,{icon : 1,time:2000},function () {
                            parent.layer.close(index); //再执行关闭
                        });
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
        })
    });
    function getHtml() {
        var html = '<div class="layui-form-item">\n' +
            '                    <div class="layui-inline">\n' +
            '                        <label class="layui-form-label"><span class="rquireCoin"> * </span>审核人</label>\n' +
            '                        <div class="layui-input-inline" >\n' +
            '                            <input type="text" name="app_user" value="部门负责人" readonly placeholder="" lay-verify="required" autocomplete="off" class="layui-input">\n' +
            '                        </div>\n' +
            '                        <div class="layui-form-mid layui-word-aux">审核权属为关联部门的，审核人默认为关联部门的负责人</div>\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '                <div class="layui-form-item">\n' +
            '                    <div class="layui-inline">\n' +
            '                        <label class="layui-form-label"><span class="rquireCoin"> * </span>审核金额</label>\n' +
            '                        <div class="layui-input-inline">\n' +
            '                            <input type="text" name="s_price" value="0" readonly placeholder="￥" autocomplete="off" class="layui-input">\n' +
            '                        </div>\n' +
            '                        <div class="layui-form-mid layui-word-aux">审核权属为关联部门的，审核金额默认设置为0（即大于等于该金额的业务需走流程审批）</div>\n' +
            '                    </div>\n' +
            '                </div>';
        return html;
    }
    function getHtml2() {
        var max = $('input[name="min"]').val();
        var html = '<div class="layui-form-item">\n' +
            '                <div class="layui-inline">\n' +
            '                    <label class="layui-form-label"><span class="rquireCoin"> * </span>审核人</label>\n' +
            '                    <div class="layui-input-inline">\n' +
            '                        <select name="app_user" class="users" lay-verify="required" lay-search="">\n' +
            '                            <option value="">直接选择或搜索选择</option>\n' +
            '                        </select>\n' +
            '                    </div>\n' +
            '                </div>\n' +
            '            </div>\n' +
            '            <div class="layui-form-item">\n' +
            '                <div class="layui-inline">\n' +
            '                    <label class="layui-form-label"><span class="rquireCoin"> * </span>审核金额</label>\n' +
            '                    <div class="layui-input-inline">\n' +
            '                        <input type="text" name="s_price" value="" lay-verify="required|s_price" placeholder="￥" autocomplete="off" class="layui-input">\n' +
            '                    </div>\n' +
            '                    <div class="layui-form-mid layui-word-aux">（即大于等于该金额的业务需走流程审批）审核金额应大于或等于上一级审核中已设置的金额。前值：'+max+'</div>\n' +
            '                </div>\n' +
            '            </div>';
        return html;
    }
    exports('controller/basesetting/approveSetting/editProcess', {});
});

