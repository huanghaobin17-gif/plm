layui.define(function(exports){
    layui.use(['form','table','laydate','formSelects','suggest'], function(){
        var $ = layui.$
            , laydate = layui.laydate
            , formSelects = layui.formSelects
            ,suggest = layui.suggest
            ,form = layui.form;
        form.render();
        suggest.search();
        //管理科室 多选框初始配置
        formSelects.render('presence', selectParams(1));
        formSelects.btns('presence',selectParams(2));
        laydate.render({
            elem: '#trainStartDate' //指定元素
            ,min: nowday
        });
        laydate.render({
            elem: '#trainEendDate' //指定元素
            ,min: nowday
        });
        //监听提交
        form.on('submit(saveTrain)', function(data){
            var params = data.field;
            params.action = 'saveTrain';
            if(params.train_start_date > params.train_end_date){
                top.layer.msg('培训时间设置不合理！', {icon: 2});
                return false;
            }
            submit($,params,'trainPlans');
            return false;
        });
    });
    exports('controller/purchases/purchaseCheck/trainPlans', {});
});