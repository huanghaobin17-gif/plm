layui.define(function(exports){
    var gloabOptions = {};
    layui.use(['table', 'form', 'treeGrid', 'admin', 'formSelects'], function () {
        layer.config(layerParmas());
        var treeGrid = layui.treeGrid, admin = layui.admin, formSelects = layui.formSelects, form = layui.form, table = layui.table;
        //switch (admin.screen()){
        //    case 3://大屏幕
        //        tableHeight = 600;
        //        break;
        //    case 2://中屏幕
        //        tableHeight = 300;
        //        break;
        //    case 1://小屏幕
        //        tableHeight = 300;
        //        break;
        //    case 0://超小屏幕
        //        tableHeight = 300;
        //        break;
        //}
        //渲染所有多选下拉
        formSelects.render('departmentList', selectParams(1));
        formSelects.btns('departmentList', selectParams(2), selectParams(3));
        ptable = treeGrid.render({
            elem: '#departmentLists'
            , url: departmentLists
            , cellMinWidth: 10
            , idField: 'departid'//必須字段
            , treeId: 'departid'//树形id字段名称
            , treeUpId: 'parentid'//树形父id字段名称
            , treeShowName: 'department'//以树形式显示的字段
            , height: 'full-200' //高度最大化减去差值
            //, height: tableHeight
            // , limits: [20, 50, 100, 200, 500]
            // , limit: 20
            , isFilter: false
            // , isPage: true
            , iconOpen: true//是否显示图标【默认显示】
            , isOpenDefault: false//节点默认是展开还是折叠【默认展开】
            , loading: true
            , method: 'POST'
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            , cols: [[
                {field: 'department', minWidth: 200, title: '科室名称', align: 'left'}
                , {field: 'departnum', width: 90, title: '科室编码', align: 'center'}
                , {field: 'manager', title: '审批负责人', width: 100, align: 'center'}
                , {field: 'departrespon', title: '科室负责人', width: 100, align: 'center'}
                , {field: 'assetssum', title: '设备数量', width: 90, align: 'center'}
                , {field: 'assetsprice', title: '设备总金额', width: 100, align: 'center'}
                , {field: 'assetsrespon', title: '设备负责人', width: 100, align: 'center'}
                , {field: 'address', title: '所在位置', width: 130, align: 'center'}
                , {field: 'departtel', title: '科室电话', width: 130, align: 'center'}
                , {field: 'depart_operation', width: 210, title: '操作', fixed: 'right', align: 'center'}
            ]], done: function (res, curr) {
                    // var pages = this.page.pages;
                    // var thisId = '#' + this.id;
                    // if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                    //     $(thisId).next().css('height', 'auto');
                    //     $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    // } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                    //     $(thisId).next().css('height', 'auto');
                    //     $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    // }
                }
            ,onClickRow:function (index, o) {
                //console.log(index,o,"单击！");
            }
            ,onDblClickRow:function (index, o) {
                //console.log(index,o,"双击");
            }
        });
        //监听工具条
        treeGrid.on('tool(departmentData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var departid = rows.departid;
            var flag = 1;
            switch (layEvent) {
                case 'addDepartment':
                    top.layer.open({
                        id: 'addChildDepartments',
                        type: 2,
                        title: '添加【'+rows.department+'】的子科室',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['490px', '100%'],
                        closeBtn: 1,
                        content: url+'?parentid='+departid,
                        end: function () {
                            if(flag){
                                treeGrid.reload('departmentLists', {
                                    url: departmentLists
                                    ,where: gloabOptions
                                })
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'editDepartment'://修改主科室
                    top.layer.open({
                        id: 'editDepartments',
                        type: 2,
                        title: '修改科室【'+ rows.department +'】',
                        area: ['490px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?departid='+departid],
                        end: function () {
                            if(flag){
                                layui.index.render();
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'deleteDepartment'://删除主科室
                    var data = {};
                    data.departid = departid;
                    if(rows.parentid == 0){
                        data.all = 1;//删除主科室
                    }else{
                        data.all = 0;//删除子科室
                    }
                    layer.confirm('如删除的是主科室，则其下子科室会一并删除，已绑定这些科室的设备将无法显示设备科室，确定要删除吗？', {icon: 3, title:'删除科室【'+rows.department+'】'}, function(index){
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            data:data,
                            url: url,
                            dataType: "json",
                            beforeSend:beforeSend,
                            success: function (data) {
                                if (data) {
                                    if (data.status == 1) {
                                        layer.msg(data.msg,{icon : 1,time:2000},function(){
                                            layui.index.render();
                                        });
                                    }else{
                                        layer.msg(data.msg,{icon : 2},1000);
                                    }
                                }else {
                                    layer.msg('数据异常',{icon : 2},1000);
                                }
                            },
                            error: function () {
                                layer.msg("网络访问失败！",{icon : 2},1000);
                            },
                            complete:complete
                        });
                        layer.close(index);
                    });
                    break;
                case 'manager'://设置主科室审批人
                    top.layer.open({
                        id: 'managers',
                        type: 2,
                        title: '设置科室审批负责人【'+rows.department+'】',
                        area: ['420px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        isOutAnim: true,
                        closeBtn: 1,
                        content: [departmentLists+'?action=setApproveUser&departid='+departid],
                        end: function () {
                            if(flag){
                                layui.index.render();
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
            }
        });
        //添加设备主科室
        $('#addDepartment').on('click',function() {
            var url = $(this).attr('data-url');
            var flag = 1;
            top.layer.open({
                id: 'addParentDepartments',
                type: 2,
                title: $(this).html(),
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                area: ['490px', '100%'],
                closeBtn: 1,
                content: url+'?parentid=0',
                end: function () {
                    if(flag){
                        layui.index.render();
                    }
                },
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });

        //批量添加科室
        $("#batchAddDepartment").on('click',function() {
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'batchAddDepartmentss',
                type: 2,
                title: $(this).html(),
                scrollbar:false,
                area: ['100%', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    treeGrid.reload('departmentLists', {
                        url: departmentLists
                        ,where: gloabOptions
                    });
                }
            });
            return false;
        });

        $('.dHead').on('click',function () {
            var treedata=treeGrid.getDataTreeList('departmentLists');
            treeGrid.treeOpenAll('departmentLists',!treedata[0][treeGrid.config.cols.isOpen]);
        });

        //搜索按钮
        form.on('submit(departmentListSearch)', function (data) {
            gloabOptions = data.field;
            treeGrid.reload('departmentLists', {
                url: departmentLists
                , height: 'full-100' //高度最大化减去差值
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
                , where: gloabOptions
                , done: function (res, curr) {
                    var pages = this.page.pages;
                    var thisId = '#' + this.id;
                    if ($(thisId).next().find('.layui-table-main').height() > $(thisId).next().find('.layui-table-main .layui-table').height() && curr == pages) {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    } else if (typeof res.total === 'undefined' || typeof res.limit === 'undefined') {
                        $(thisId).next().css('height', 'auto');
                        $(thisId).next().find('.layui-table-main').css('height', 'auto');
                    }
                }
            });
            return false;
        });
    });
    exports('basesetting/integratedSetting/department', {});
});
