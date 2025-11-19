layui.define(function(exports){
    layui.use(['table', 'element', 'carousel', 'tablePlug'], function () {
        var table = layui.table, element = layui.element, carousel = layui.carousel, tablePlug = layui.tablePlug;
        layer.config(layerParmas());

        var uploadAction = $('input[name="uploadAction"]').val();
        var assid = $('input[name="assid"]').val();


        //提示是否下载/预览
        $(document).on('click', '.operationFile', function () {
            var path = $(this).data('path');
            var name = $(this).data('name');
            var showFile = $(this).data('showfile');
            var btn = [];
            if (showFile == true) {
                btn = ['下载', '预览'];
            } else {
                btn = ['下载'];
            }
            layer.open({
                title: $(this).html(),
                area: ['300px', '130px']
                , btnAlign: 'c'
                ,shade:0.3
                , btn: btn
                , yes: function (index) {
                    var params = {};
                    params.path = path;
                    params.filename = name;
                    postDownLoadFile({
                        url:admin_name+'/Tool/downFile',
                        data: params,
                        method: 'POST'
                    });
                    return false;
                }
                , btn2: function (index) {
                    var  url=admin_name+'/Tool/showFile';
                    top.layer.open({
                        type: 2,
                        title: name + '相关文件查看',
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [url +'?path=' + path + '&filename=' + name]
                    });
                    return false;
                }
                , cancel: function () {
                    //右上角关闭回调
                }
            });
        });
        //列排序
        table.on('sort(increment)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('increment', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //状态变更记录
        table.render({
            elem: '#Status'
            , size: 'sm'//小尺寸的表格
            , initSort: {
                field: 'id' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            },
            response: {
                statusName: 'code' //数据状态的字段名称，默认：code
                , statusCode: 200 //成功的状态码，默认：0
                //,msgName: 'hint' //状态信息的字段名称，默认：msg
                , countName: 'total' //数据总数的字段名称，默认：count
                , dataName: 'rows' //数据列表的字段名称，默认：data
            }
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , where: {
                sort: 'id'
                , order: 'asc'
                , assid: assid
                , action: 'getStatusChange'
            } //如果无需传递额外参数，可不加该参数
            , page: true //开启分页
            , cols: [[ //表头
                {
                    field: 'id',
                    title: '序号',
                    align: 'center',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'changeTime', title: '变更时间', width: 300, align: 'center'}
                , {field: 'remark', title: '说明', align: 'center'}
            ]]
        });
        //列排序
        table.on('sort(statusData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('Status', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        //设备转科记录
        table.render({
            elem: '#transfer'
            , size: 'sm' //小尺寸的表格
            , method: 'post',
            initSort: {
                field: 'atid' //排序字段，对应 cols 设定的各字段名
                , type: 'desc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            },
            where: {
                assid: assid
                , action: 'getTransfer'
            },
            response: {
                statusName: 'code' //数据状态的字段名称，默认：code
                , statusCode: 200 //成功的状态码，默认：0
                //,msgName: 'hint' //状态信息的字段名称，默认：msg
                , countName: 'total' //数据总数的字段名称，默认：count
                , dataName: 'rows' //数据列表的字段名称，默认：data
            }
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , page: true //开启分页
            , cols: [[ //表头
                {
                    field: 'atid',
                    title: '序号',
                    align: 'center',
                    width: 65,
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'applicant_time', title: '申请时间', align: 'center', width: 145},
                {field: 'tranout_department', title: '转出部门', align: 'center', width: 140},
                {field: 'tranin_department', title: '转入部门', align: 'center', width: 140},
                {
                    field: 'examine_status',
                    title: '审核状态',
                    width: 80,
                    align: 'center',
                    templet: function (d) {
                        return d.approve_status == 0 ? '待审核' : (d.approve_status == 1 ? '<span style="color: green;">已通过</span>' : '<span style="color:red;">不通过</span>');
                    }
                },
                {
                    field: 'is_check',
                    title: '验收状态',
                    width: 80,
                    align: 'center',
                    templet: function (d) {
                        return d.is_check == 0 ? '待验收' : (d.is_check == 1 ? '<span style="color: green;">已通过</span>' : '<span style="color:red;">不通过</span>');
                    }
                },
                {field: 'tran_reason', title: '转出原因', align: 'center'}
            ]]
        });

        //列排序
        table.on('sort(transfer)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('transfer', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //设备维修记录
        table.render({
            elem: '#RepairSearchList'
            , size: 'sm'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , where: {
                assid: assid
                , sort: 'repid'
                , order: 'desc'
                , action: 'getRepairRecord'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'repid' //排序字段，对应 cols 设定的各字段名
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
            , cols: [[ //表头
                {
                    field: 'repid',
                    title: '序号',
                    width: 65,
                    align: 'center',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }, {field: 'repnum', title: '维修单编号', width: 150, align: 'center'}
                , {field: 'model', title: '规格/型号', width: 150, align: 'center'}
                , {field: 'department', title: '使用科室', width: 100, align: 'center'}
                , {field: 'applicant', title: '报修人', width: 100, align: 'center'}
                , {field: 'engineer', title: '维修工程师', width: 100, align: 'center'}
                , {field: 'part_num', title: '配件数', width: 150, align: 'center'}
                , {field: 'applicant_time', title: '报修时间', width: 150, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'right',
                    width: 180,
                    align: 'center'
                }
            ]]
        });

        //列排序
        table.on('sort(RepairSearchList)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('RepairSearchList', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //维修操作栏按钮
        table.on('tool(RepairSearchList)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            if (layEvent === 'showRepair') {
                //显示维修单
                top.layer.open({
                    type: 2,
                    title: '【' + rows.assets + '】维修单详情',
                    area: ['75%', '100%'],
                    offset: 'r',
                    anim: 2,
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url + '?repid=' + rows.repid + '&assid=' + rows.assid],
                });
            } else if (layEvent === 'showUpload') {
                //显示上传文件
                top.layer.open({
                    type: 2,
                    title: '【' + rows.assets + '】相关上传文件查看',
                    area: ['75%', '100%'],
                    offset: 'r',
                    anim: 2,
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url + '?repid=' + rows.repid]
                });
            }
        });

        //不良事件
        table.render({
            elem: '#getAdverseLists'
            , size: 'sm' //小尺寸的表格
            , limits: [5, 10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , where: {
                assid: assid
                , sort: 'id'
                , order: 'desc'
                , action: 'getAdverseRecord'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'id' //排序字段，对应 cols 设定的各字段名
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
            , cols: [[ //表头
                {
                    field: 'id',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'sign', title: '上报人员', width: 100, align: 'center'}
                , {field: 'reporter', title: '上报人员职称', width: 120, align: 'center'}
                , {field: 'report_date', title: '报告日期', width: 110, align: 'center'}
                , {field: 'express_date', title: '事件发生时间', width: 120, align: 'center'}
                , {field: 'consequence', title: '事件后果', width: 300, align: 'center'}
                , {field: 'express', title: '事件主要表现', width: 200, align: 'center'}
                , {field: 'cause', title: '事件发生初步原因分析', width: 300, align: 'center'}
                , {field: 'situation', title: '事件初步处理情况', width: 300, align: 'center'}
                , {field: 'report_status', title: '事件报告状态', width: 450, align: 'center'}
                , {field: 'report', title: '附件', width: 120, align: 'center'}
            ]]
        });


        //设备质控记录 todo
        table.render({
            elem: '#quality',
            method: 'POST',
            initSort: {
                field: '' //排序字段，对应 cols 设定的各字段名
                , type: '' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            },
            where: {
                assid: assid
                , action: 'getQuality'
            },
            response: {
                statusName: 'code' //数据状态的字段名称，默认：code
                , statusCode: 200 //成功的状态码，默认：0
                //,msgName: 'hint' //状态信息的字段名称，默认：msg
                , countName: 'total' //数据总数的字段名称，默认：count
                , dataName: 'rows' //数据列表的字段名称，默认：data
            }
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , page: true //开启分页
            , cols: [[ //表头
                {
                    field: 'qsid',
                    title: '序号',
                    align: 'center',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    unresize: true,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'plan_name', title: '质控计划名称', align: 'center', width: 200},
                {field: 'do_date', title: '预计开始日期', align: 'center', width: 120},
                {field: 'end_date', title: '预计结束日期', align: 'center', width: 120},
                {field: 'is_cycle', title: '是否周期执行', align: 'center', width: 120},
                {field: 'cycle', title: '周期(月)', align: 'center', width: 100},
                {field: 'period', title: '当前期次', align: 'center', width: 100},
                {field: 'start_date', title: '启用日期', align: 'center', width: 110, sort: true},
                {
                    field: 'is_start',
                    title: '计划执行状态',
                    align: 'center',
                    width: 120,
                    templet: function (d) {
                        return d.is_start == 0 ? '<span style="color:#FFB800;">未启用</span>' : (d.is_start == 1 ? '<span style="color:#009688;">执行中</span>' : (d.is_start == 2 ? '已暂停' : (d.is_start == 3 ? '已完成' : '<span style="color: #01AAED;">已结束</span>')));
                    }
                },
                {field: 'username', title: '检测人', align: 'center', width: 100},
                {
                    field: 'result',
                    title: '检测结果',
                    width: 100,
                    align: 'center',
                    templet: function (d) {
                        return d.result == 1 ? '合格' : (d.result == 2 ? '<span style="color:#FF5722;">不合格</span>' : '');
                    }
                },
                {field: 'report', title: '质控报告', align: 'center', width: 150}
            ]]
        });

        //设备计量记录
        table.render({
            elem: '#meteringLists',
            method: 'POST',
            initSort: {
                field: '' //排序字段，对应 cols 设定的各字段名
                , type: '' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            },
            where: {
                assid: assid
                , action: 'getMeteringRecord'
            },
            response: {
                statusName: 'code' //数据状态的字段名称，默认：code
                , statusCode: 200 //成功的状态码，默认：0
                //,msgName: 'hint' //状态信息的字段名称，默认：msg
                , countName: 'total' //数据总数的字段名称，默认：count
                , dataName: 'rows' //数据列表的字段名称，默认：data
            }
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , page: true //开启分页
            , cols: [[ //表头
                {
                    field: 'mpid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'plan_num', fixed: 'left', title: '计划编号', width: 150, align: 'center'},
                {field: 'mcategory', title: '计量分类', width: 125, align: 'center'},
                {field: 'cycle', title: '计量周期(月)', width: 120, align: 'center'},
                {field: 'respo_user', title: '计量负责人', width: 100, align: 'center'},
                {
                    field: 'test_way',
                    title: '检定方式',
                    width: 115,
                    align: 'center',
                    templet: function (d) {
                        return (d.test_way == 1) ? '<span style="color: #F581B1;">院内</span>' : '院外';
                    }
                },
                {
                    field: 'status',
                    title: '检查状态',
                    width: 100,
                    align: 'center',
                    templet: function (d) {
                        return (d.status == 1) ? '已执行' : '<span style="color: #F581B1;">未执行</span>';
                    }
                },
                {field: 'company', title: '检定机构', width: 100, align: 'center'},
                {field: 'money', title: '计量费用', width: 100, align: 'center'},
                {field: 'test_person', title: '检定人', width: 100, align: 'center'},
                {field: 'this_date', title: '检定日期', width: 110, align: 'center'},
                {
                    field: 'result',
                    title: '检定结果',
                    width: 100,
                    align: 'center',
                    templet: function (d) {
                        return (d.status == 1 && d.result == 1) ? '合格' : ((d.status == 1 && d.result == 0) ? '<span style="color: #F581B1;">不合格</span>' : '');
                    }
                },
                {field: 'operation', title: '检定报告', width: 100, style: 'background-color: #f9f9f9;', align: 'center'}
            ]]
        });


        //轮播
        carousel.render({
            elem: '#carouselAssetsPic'
            ,arrow: 'always'
            ,width: '290px'
            ,height: '430px'
        });

        //点击轮播的图片显示相册层
        layer.photos({
            photos: '#carouselAssetsPic'
            ,anim: 5
            ,maxmin: false
        });
        //列排序
        table.on('sort(qualityData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('transfer', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });

        //维保列表
        table.render({
            elem: '#doRenewal'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , size: 'sm' //小尺寸的表格
            , loading: true
            , url: showAssets //数据接口
            , where: {
                assid: assid
                ,action:'doRenewal'
                , sort: 'insurid'
                , order: 'asc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'insurid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            }
            , method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                dataName: 'rows' //数据字段
            }
            //,page: true //开启分页
            , cols: [[ //表头
                {
                    field: 'insurid',
                    title: '序号',
                    width: 65,
                    fixed: 'left',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'company', fixed: 'left', title: '维保公司名称', width: 150, align: 'center'}
                , {field: 'nature', title: '维保性质', width: 120, align: 'center'}
                , {field: 'cost', title: '参保费用', width: 120, align: 'center'}
                , {field: 'buydate', title: '购入日期', width: 120, align: 'center'}
                , {field: 'term', title: '维保期限', width: 200, align: 'center'}
                , {field: 'contacts', title: '联系人', width: 100, align: 'center'}
                , {field: 'telephone', title: '联系电话', width: 120, align: 'center'}
                , {field: 'adduser', title: '添加用户', width: 100, align: 'center'}
                , {field: 'adddate', title: '添加时间', width: 120, align: 'center'}
                , {field: 'edituser', title: '修改用户', width: 100, align: 'center'}
                , {field: 'editdate', title: '修改时间', width: 120, align: 'center'}
                , {field: 'content', title: '维保内容', width: 180, align: 'center'}
                , {field: 'file_data', title: '相关文件', width: 150, align: 'center'}
                , {field: 'remark', title: '备注', width: 180, align: 'center'}]]
        });


        //借调
        table.render({
            elem: '#borrowRecordList'
            ,size:'sm'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , where: {
                showlifeBorrow:1,
                assid: assid,
                action:'borrowRecordList',
                sort: 'assid',
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
                {field: 'mpid', title: '序号', width: 80, fixed: 'left', align: 'center', type: 'space',rowspan:2,
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'borrow_num', title: '流水号', width: 150, fixed: 'left', align: 'center'},
                {field: 'assets', title: '设备名称', fixed: 'left', width: 180, align: 'center'},
                {field: 'department', title: '所属科室', width: 150, align: 'center'},
                {field: 'assnum', title: '设备编号', width: 140, align: 'center'},
                {field: 'brand', title: '品牌', width: 120, align: 'center'},
                {field: 'model', title: '规格/型号', width: 180, align: 'center'},
                {field: 'apply_department', title: '申请科室', width: 120, align: 'center'},
                {field: 'apply_user', title: '申请人', width: 150, align: 'center'},
                {field: 'apply_time', title: '申请时间', width: 150, align: 'center'},
                {field: 'borrow_in_time', title: '借入时间', width: 150, align: 'center'},
                {field: 'give_back_time', title: '归还时间', width: 150, align: 'center'},
                {field: 'deparment_approve', title: '科室审批', width: 120, align: 'center'},
                {field: 'assets_approve', title: '设备科审核', width: 120, align: 'center'},
                {field: 'operation', title: '操作', fixed: 'right', width: 120, align: 'center'}
            ]]
        });

        //借调操作栏
        table.on('tool(borrowRecordData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'showDetails':
                    top.layer.open({
                        type: 2,
                        title: '【' + rows.assets + '】借调记录详情',
                        area: ['75%', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [url+'&showLifeBorrow=1']
                    });
                    break;
            }
        });

        //如果是外调结果页面点击进来 选项卡切换到外调记录
        var changeTab = $("input[name='changeTab']").val();
        if (changeTab == 14) {
            element.tabChange('change', 14);
        }
        //详情页面进入 默认展开隐藏的tab
        $(".layui-tab-bar").click();
    });


//时间线弹详情提示jq
    $(".layui-timeline-axis").hover(function () {
        $(".timeLineDetail").hide();
        $(this).siblings(".timeLineDetail").show();
    });


    $(".timeLineDetail").mouseleave(function () {
        $(this).hide();
    });

//下载
    $(document).on('click','.downFile',function () {
        var params={};
        params.path= $(this).data('path');
        params.filename=$(this).data('name');
        postDownLoadFile({
            url:admin_name+'/Tool/downFile',
            data:params,
            method:'POST'
        });
        return false;
    });

//预览
    $(document).on('click','.showFile',function () {
        var path= $(this).data('path');
        var name=$(this).data('name');
        var url = admin_name+'/Tool/showFile';
        top.layer.open({
            type: 2,
            title: name + '相关文件查看',
            scrollbar: false,
            offset: 'r',//弹窗位置固定在右边
            anim: 2, //动画风格
            area: ['70%', '100%'],
            closeBtn: 1,
            content: [url +'?path=' + path + '&filename=' + name]
        });
        return false;
    });
    exports('controller/assets/lookup/showLife', {});
});