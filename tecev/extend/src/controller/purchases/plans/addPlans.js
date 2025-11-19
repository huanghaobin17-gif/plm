layui.define(function(exports){
    layui.use(['form','laydate','formSelects'], function () {
        var form = layui.form,laydate = layui.laydate,formSelects = layui.formSelects;
        formSelects.render('departid', selectParams(1));
        formSelects.btns('departid',selectParams(2));
        //报修时间元素渲染
        laydate.render({
            elem: '#year' //指定元素
            ,type: 'year'
        });
        laydate.render({
            elem: '#plans_start'
        });
        laydate.render({
            elem: '#plans_end'
        });
        laydate.render({
            elem: '#adddate'
        });
        form.verify({
            project_name: function(value,item){
                if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                    return '项目名称首尾不能出现下划线\'_\'';
                }
                if (/^\d+\d+\d$/.test(value)) {
                    return '项目名称不能全为数字';
                }
            }
        });
        //切换医院后获取相应科室
        form.on('select(hospital_id)', function (data) {
            var hospital_id = parseInt(data.value);
            var params = {};
            params.hospital_id = hospital_id;
            params.action = 'getHospitals';
            //获取对应医院的科室
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: 'addPlans',
                data: params,
                dataType: "json",
                async: true,
                success: function (data) {
                    if (data.status == 1) {
                        $(".department-content").html('');
                        var html = '<select name="departid" id="departid" lay-verify="departid|required" xm-select="departid" xm-select-search="" lay-verify="select1">';
                        var option = '<option value=""></option>';
                        $.each(data.departments,function (index,item) {
                            option += '<option value="'+item.departid+'">'+item.department+'</option>';
                        });
                        html += option;
                        html += '</select>';
                        $('.department-content').html(html);
                        formSelects.render('departid', selectParams(1));
                        formSelects.btns('departid',selectParams(2));
                        form.render();
                    }
                }
            });
        });
        //监听提交
        form.on('submit(savePlans)', function (data) {
            params = data.field;
            //进行比较
            if(params.plans_start > params.plans_end){
                layer.msg('请选择合理的时间范围！', {icon: 2,time:1500});
                return false;
            }
            submit($,params,'addPlans');
            return false;
        });
    });
    exports('controller/purchases/plans/addPlans', {});
});