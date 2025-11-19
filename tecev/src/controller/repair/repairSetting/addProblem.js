layui.define(function(exports){
    layui.use('form', function() {
        var form = layui.form, $ = layui.jquery;

        form.render();

        //监听提交
        form.on('submit(save)', function (data) {
            var params = data.field;
            //故障问题
            var title = data.field['title'].split("\n");
            $.each(title,function(k,v){
                title[k]=$.trim(v);
            });
            //解决办法
            var solve = data.field['solve'].split("\n");
            $.each(solve,function(k,v){
                solve[k]=$.trim(v);
            });
            //备注
            var remark = data.field['remark'].split("\n");
            $.each(remark,function(k,v){
                remark[k]=$.trim(v);
            });
            params.title = title.join(',');
            params.solve = solve.join(',');
            params.remark = remark.join(',');
            submit($,params,params.actionname+'?type=addProblem');
            return false;
        })
    });
    exports('controller/repair/repairSetting/addProblem', {});
});







