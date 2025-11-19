layui.define(function(exports){
layui.use(['form','suggest'], function() {
    var form = layui.form, $ = layui.jquery,suggest = layui.suggest;

    //初始化搜索建议插件
    suggest.search();

    //渲染元素
    form.render();

    form.verify({
        type: function(value){
            if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                return '故障类型首尾不能出现下划线\'_\'';
            }
            if (/^\d+\d+\d$/.test(value)) {
                return '故障类型不能全为数字';
            }
        },
        problem: function(value){
            if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                return '故障问题首尾不能出现下划线\'_\'';
            }
            if (/^\d+\d+\d$/.test(value)) {
                return '故障问题不能全为数字';
            }
        }
    });
    //监听提交
    form.on('submit(add)', function (data) {
        params = data.field;
        params.action='addTypeAndProblem';
        params.url = admin_name+'/Repair/accept.html';
        submit($,params);
        return false;
    });
    //故障类型
    $("#testNoBtn").bsSuggest(
        {
            url: admin_name+'/Repair/accept?action=getAllType',
            /*effectiveFields: ["userName", "shortAccount"],
             searchFields: [ "shortAccount"],*/
            effectiveFields: ["title"],
            searchFields: [ "title"],
            effectiveFieldsAlias: {title: "故障类型名称"},
            ignorecase: false,
            showHeader: true,
            listStyle: {
                "max-height": "375px", "max-width": "280px",
                "overflow": "auto", "width": "190px", "text-align": "center"
            },
            listAlign: 'left',
            showBtn: false,     //不显示下拉按钮
            delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
            idField: "title",
            keyField: "title",
            clearable: false
        }
    )
});
function submit($,params){
    $.ajax({
        timeout: 5000,
        type: "POST",
        url: params.url,
        data: params,
        dataType: "json",
        success: function (data) {
            if (data.status == 1) {
                layer.msg(data.msg,{icon : 1,time:2000},function(){
                    var index = parent.layer.getFrameIndex(window.name); //先得到当前iframe层的索引
                    parent.layer.close(index); //再执行关闭
                });
            }else{
                layer.msg(data.msg,{icon : 2},1000);
            }
        },
        error: function () {
            layer.msg("网络访问错误",{icon : 2},1000);
        }
    });
}
    exports('controller/repair/repair/addTypeAndProblem', {});
});





