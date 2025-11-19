layui.define(function(exports){
    layui.use(['form', 'table', 'tablePlug','laydate'], function () {
        var form = layui.form, table = layui.table, tablePlug = layui.tablePlug,laydate = layui.laydate;
        form.render();
        layer.config(layerParmas());
        var rel_date = laydate.render({
            elem: '#rel_date'
            ,min: min_date
            ,max: max_date
            ,ready: function(){
                rel_date.hint('发布日期可选值设定在 <br>'+min_date+' 到 '+max_date);
            }
        });
        var gloabOptions = {};
        table.render({
            elem: '#showPlansLists'
            , limits: [5, 10, 20, 50, 100]
            , loading: true
            ,title: '巡查保养查询列表'
            ,data:assets
            , where: {
                sort: 'assid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'assid' //排序字段，对应 cols 设定的各字段名
                , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , request: {
                pageName: 'page' //页码的参数名称，默认：page
                , limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                //layout: ['limit', 'count', 'prev', 'page', 'next', 'skip'] //自定义分页布局
                //,curr: 5 //设定初始在第 5 页
                theme: '#428bca', //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , cols: [[ //表头
                {field: 'assid',title: '序号',width: 60,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                ,{field: 'assnum',fixed: 'left',title: '设备编号',width: 160,align: 'center'}
                ,{field: 'assorignum',title: '设备原编号',width: 160,align: 'center'}
                ,{field: 'assets',title: '设备名称',width: 160,align: 'center'}
                ,{field: 'model',title: '规格型号',width: 120,align: 'center'}
                ,{field: 'department',title: '使用科室',width: 140,align: 'center'}
                ,{field: 'pre_date',title: pre_date_name,width: 130,align: 'center'}
                ,{field: 'details_num',fixed: 'right',title: detail_name,width: 110,align: 'center'}
                ,{field: 'template_name',fixed: 'right',title: template_name,width: 180,align: 'center'}
            ]]
        });
        //点击模板名称弹窗
        $('.show_template').on('click',function () {
            top.layer.open({
                type: 2,
                title:'【'+$(this).html()+'】模板明细项',
                area: ['800px', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [admin_name+'/Patrol/showPlans?id='+$(this).attr('data-id')+'&action=showTemplate']
            });
        });

        //确定发布
        $('#release').bind('click',function(){
            var pid = $("input[name='pid']").val();
            var releasePatrol_code = $("input[name='releasePatrol_code']").val();
            var rel_date = $("input[name='rel_date']").val();
            var remark = $("textarea[name='rel_remark']").val();
            var url = admin_name+'/Patrol/releasePatrol';
            var param = {};
            param['pid'] = pid;
            param['releasePatrol_code'] = releasePatrol_code;
            param['rel_date'] = rel_date;
            param['remark'] = remark;
            $.ajax({
                timeout: 5000,
                dataType: "json",
                type:"POST",
                url:url,
                data:param,
                async:false,
                beforeSend: function () {
                    $('#release').attr('class','layui-btn layui-btn-disabled');
                    $("input[name='releasePatrol_code']").val('');
                },
                //成功返回之后调用的函数
                success:function(data){
                    if(data.status == 1){
                        CloseWin(data.msg);
                    }else{
                        layer.msg(data.msg,{icon : 2,time:3000});
                    }
                },
                //调用出错执行的函数
                error: function(){
                    //请求出错处理
                    layer.msg('服务器繁忙', {icon: 2});
                },
                complete:function () {
                    layer.closeAll('loading');
                }
            });
        });
    });
    exports('controller/patrol/patrol/releasePatrol', {});
});
