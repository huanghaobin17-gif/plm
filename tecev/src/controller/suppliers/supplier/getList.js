layui.define(function(exports){
    layui.define(function(exports){
        layui.use(['layer', 'form', 'element', 'table', 'tablePlug'], function () {
            var layer = layui.layer, form = layui.form, table = layui.table, tablePlug = layui.tablePlug;

        //先更新页面部分需要提前渲染的控件
        form.render();
        layer.config(layerParmas());

        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#suppliersList'
            ,size:'sm'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: admin_name+'/Metering/getMeteringList.html' //数据接口
            , where: {
                is_metering:1,
                type:'getMeteringList',
                sort: 'mpid',
                order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'mpid' //排序字段，对应 cols 设定的各字段名
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
                //,theme: '#428bca' //当前页码背景色
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            //,page: true //开启分页
            , cols:[ [ //表头
                {type: 'checkbox',fixed: 'left',rowspan:2},
                {field: 'mpid', title: '序号', width: 50, fixed: 'left', align: 'center', type: 'space',rowspan:2,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: '', title: '公司位置', fixed: 'left', width: 80, align: 'center',rowspan:2,
                    templet: function (d) {
                        return '广州市';
                    }
                },
                {field: '', fixed: 'left', title: '公司名称', width: 260, align: 'center',rowspan:2,
                    templet:function (d) {
                        return '广州天成医疗技术股份有限公司';
                    }
                },
                {field: '', title: '公司类型', fixed: 'left', width: 120, align: 'center',rowspan:2},
                {field: '', title: '合作情况', fixed: 'left', width: 120, align: 'center',rowspan:2},
                {field: '', title: '评价排名', fixed: 'left',sort: true, width: 100, align: 'center',rowspan:2},
                // {field: '', title: '', fixed: '', width: 0, align: 'center',rowspan:2},
                /*{field: '', title: '营业执照', width: 90, align: 'center',
                    templet: function (d) {
                        return '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
                    }
                },
                {field: '', title: '医疗器械经营许可证', width: 160, align: 'center', style:'background-color: #F0FFFF;',
                    templet: function (d) {
                        return '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
                    }
                },
                {field: '', title: '有限期至', width: 110, align: 'center' ,style:'background-color: #F0FFFF;',
                    templet: function (d) {
                        return '2018-06-28';
                    }
                },
                {field: '', title: '第二类医疗器械经营备案凭证', width: 200, align: 'center', style:'background-color: #F0F8FF;',
                    templet: function (d) {
                        return '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
                    }
                },
                {field: '', title: '有限期至', width: 110, align: 'center', style:'background-color: #F0F8FF;',
                    templet: function (d) {
                        return '2018-06-28';
                    }
                },
                {field: '', title: '医疗器械生产许可证', width: 160, align: 'center', style:'background-color: #F0FFFF;',
                    templet: function (d) {
                        return '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
                    }
                },
                {field: '', title: '有限期至', width: 110, align: 'center', style:'background-color: #F0FFFF;',
                    templet: function (d) {
                        return '2018-06-28';
                    }
                },
                {field: '', title: '第一类医疗器械生产备案凭证', width: 200, align: 'center', style:'background-color: #F0F8FF;',
                    templet: function (d) {
                        return '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
                    }
                },
                {field: '', title: '有限期至', width: 110, align: 'center', style:'background-color: #F0F8FF;',
                    templet: function (d) {
                        return '2018-06-28';
                    }
                }*/
                /*,
                {field: '', title: '备注', width: 100, align: 'center'},
                {field: '', fixed: 'right', title: '提前提醒天数', width:120, align: 'center'},
                {field: '', fixed: 'right', title: '计划状态', width:80, align: 'center'},
                {field: '', fixed: 'right',title: '操作', width: 140, align: 'center'}*/
                {field: '', title: '公司资质', fixed: '', align: 'center', colspan: 3},
                {field: '', title: '产品数量', fixed: '', width: 80, align: 'center',rowspan:2},
                {field: '', title: '<span lay-tips="已采购验收入库设备数量">入库设备数量</span>', fixed: '', width: 120, align: 'center',rowspan:2},
                {field: '', title: '平台注册日期', width: 120, align: 'center',rowspan:2},
                {field: '', title: '联系人', width: 100, align: 'center',rowspan:2},
                {field: '', title: '联系电话', width: 120, align: 'center',rowspan:2},
                {field: '', title: '操作', fixed: 'right', width: 170, align: 'center',rowspan:2,
                    templet: function (d) {
                        return '<div class="layui-btn-group"><button class="layui-btn layui-btn-xs layui-btn-normal">详情</button> ' +
                            '<button class="layui-btn layui-btn-xs layui-btn-danger">待审核</button> ' +
                            '<button class="layui-btn layui-btn-xs">已审核</button></div>';
                    }}
            ], [
                {field: 'province', title: '证照名称', align: 'center', width: 200,
                    templet: function (d) {
                        return '第一类医疗器械生产备案凭证';
                    }}
                ,{field: 'city', title: '已提交', align: 'center', width: 80,
                    templet: function (d) {
                        return '<i class="layui-icon layui-icon-zzcheck" style="color: #5FB878"></i>';
                    }}
                ,{field: 'county', title: '有效期至', align: 'center', width: 110,
                    templet: function (d) {
                        return '2018-06-28';
                    }}
            ]]
        });


        table.on('tool(supplierList)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent){
                case 'colTips':

                    break;
            }
        });

        $('#supplierDetailInfo').on('click',function() {
            var flag = 1;
            var url = $(this).attr('data-url');
            top.layer.open({
                type: 2,
                anim: 2, //动画风格
                title: '<i class="layui-icon layui-icon-zzaddress-card"></i> 广州天成医疗技术股份有限公司-详情',
                scrollbar:false,
                offset: 'r',//弹窗位置固定在右边
                area: ['100%', '100%'],
                closeBtn: 1,
                content: [url],
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });


    });
    exports('suppliers/supplier/getSuppliersList', {});
});
    exports('suppliers/supplier/getList', {});
});