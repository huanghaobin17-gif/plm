layui.define(function(exports){
    layui.use(['layer', 'form', 'table', 'admin', 'tablePlug'], function () {
        var layer = layui.layer, form = layui.form, table = layui.table, admin = layui.admin, tablePlug = layui.tablePlug;

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
        });
        //定义一个全局空对象
        var gloabOptions = {};
        //数据表格
        var catid = $('input[name="catid"]').val();
        var hospital_id = $('select[name="hospital_id"] option:selected').val();
        table.render({
            elem: '#category'
            ,limits:[10,20,50,100]
            ,loading:true
            ,limit: 10
            ,title: '分类列表'
            ,url: category //数据接口
            ,method: 'POST' //如果无需自定义HTTP类型，可不加该参数
            , where: {
                sort: 'catenum'
                ,order: 'asc'
                ,catid: catid
                ,hospital_id: hospital_id
            }
            ,request: {
                pageName: 'page' //页码的参数名称，默认：page
                ,limitName: 'limit' //每页数据量的参数名，默认：limit
            } //如果无需自定义请求参数，可不加该参数
            , response: { //定义后端 json 格式，详细参见官方文档
                statusName: 'code', //状态字段名称
                statusCode: '200', //状态字段成功值
                msgName: 'msg', //消息字段
                countName: 'total', //总数字段
                dataName: 'rows' //数据字段
            }
            ,page: { //支持传入 laypage 组件的所有参数（某些参数除外，如：jump/elem） - 详见文档
                groups: 10 //只显示 1 个连续页码
                ,prev:'上一页'
                ,next:'下一页'
            },
            toolbar: '#LAY-BaseSetting-IntegratedSetting-categoryToolbar',
            defaultToolbar: ['filter','exports']
            ,cols: [[ //表头
                {field: 'catid',title: '序号',width: 60,fixed: 'left',align: 'center',type: 'space',templet: function (d) {return d.LAY_INDEX;}}
                , {
                    field: 'catenum',
                    title: '分类编号',
                    width: 100,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/catenum') == 'false' ? true : false
                }
                , {
                    field: 'category',
                    title: '分类名称',
                    width: 200,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/category') == 'false' ? true : false
                }
                , {
                    field: 'remark',
                    title: '品名举例',
                    width: 300,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/remark') == 'false' ? true : false
                }
                , {
                    field: 'assetssum',
                    title: '设备数量',
                    width: 100,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assetssum') == 'false' ? true : false
                }
                , {
                    field: 'assetsprice',
                    title: '设备金额',
                    width: 100,
                    align: 'center',
                    hide: get_now_cookie(userid + cookie_url + '/assetsprice') == 'false' ? true : false
                }
                , {field: 'operation', title: '操作', width: 100, fixed: 'right', align: 'center'}
            ]]
        });

        form.on('select(hospital_id)',function (data) {
            var params = {};
            var hosid = data.value;
            var tid = 0;
            params.action = 'getType';
            params.hospital_id = hosid;
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: category,
                data: params,
                dataType: "json",
                async: false,
                success: function (data) {
                    var html = '';
                    if(data.length > 0){
                        $.each(data,function (i,item) {
                            html += '<li class="layui-nav-item" data-url="">\n' +
                                '<a href="javascript:;" class="getCategory" data-category="'+item.category+'" data-catid="'+item.catid+'" style="text-decoration: none">'+item.category+'</a>\n' +
                                '</li>';
                        });
                        tid = data[0]['catid'];
                        $('input[name="catid"]').val(tid);
                        var cateListObj = $('.catelist');
                        cateListObj.html('');
                        cateListObj.html(html);
                        //更改显示标题
                        $(".detailName").html(data[0]['category']);
                    }else{
                        $('.catelist').html('');
                    }
                }
            });
            table.reload('category', {
                url: category
                ,where: {
                    "catid":tid,
                    "hospital_id":hosid
                }
                ,page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
        });
        //操作栏按钮
        table.on('tool(category)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            //var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            var flag = 1;
            switch (layEvent){
                case 'editCate'://修改分类
                    top.layer.open({
                        id: 'editCates',
                        type: 2,
                        title: '修改子类【'+ rows.category +'】',
                        area: ['500px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?catid='+rows.catid],
                        end: function () {
                            if(flag){
                                table.reload('category', {
                                    url: category
                                    ,where: gloabOptions
                                    ,page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'delCate'://删除分类
                    var data = {};
                    data.catid = rows.catid;
                    layer.confirm('删除分类后无法恢复，确定删除吗？', {icon: 3, title:'删除分类【'+rows.category+'】'}, function(index){
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
                                            table.reload('category', {
                                                url: category
                                                ,where: gloabOptions
                                                ,page: {
                                                    curr: 1 //重新从第 1 页开始
                                                }
                                            });
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
            }
        });

        //列排序
        table.on('sort(category)', function(obj){ //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('category', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                ,where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    ,order: obj.type //排序方式
                }
            });
        });

        //新增父分类
        $('#addCate').on('click',function() {
            var flag = 1;
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'addParentCates',
                type: 2,
                title: '新增父类',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                area: ['500px', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    if(flag){
                        changeCategory('');
                    }
                },
                cancel:function(){
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });
        //批量新增分类
        $('#batchAddCate').on('click',function() {
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'batchAddCates',
                type: 2,
                title: '批量新增分类',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                area: ['1200px', '100%'],
                closeBtn: 1,
                content: url,
                end: function () {
                    layui.index.render();
                    // table.reload('category', {
                    //     url: category
                    //     ,where: gloabOptions
                    //     ,page: {
                    //         curr: 1 //重新从第 1 页开始
                    //     }
                    // });
                }
            });
            return false;
        });
        table.on('toolbar(category)', function(obj){
            var event =  obj.event,
                url = $(this).attr('data-url'),
                catid = $("input[name='catid']").val(),
                cate_name = $("input[name='category']").val(),
                flag = 1;
            switch(event){
                case 'addSubCategory'://新增子类
                    top.layer.open({
                        id: 'addSubCategorys',
                        type: 2,
                        title: '新增子类',
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        area: ['500px', '100%'],
                        closeBtn: 1,
                        content: url+'?catid='+catid,
                        end: function () {
                            if(flag){
                                table.reload('category', {
                                    url: category
                                    ,where: gloabOptions
                                    ,page: {
                                        curr: 1 //重新从第 1 页开始
                                    }
                                });
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'editCategory'://修改父分类
                    top.layer.open({
                        id: 'editCategorys',
                        type: 2,
                        title: '修改父分类【'+ cate_name +'】',
                        area: ['500px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar:false,
                        closeBtn: 1,
                        content: [url+'?catid='+catid],
                        end: function () {
                            if(flag){
                                location.reload();
                            }
                        },
                        cancel:function(){
                            //如果是直接关闭窗口的，则不刷新表格
                            flag = 0;
                        }
                    });
                    break;
                case 'delCategory'://删除父分类
                    var data = {};
                    data.catid = catid;
                    layer.confirm('删除父分类后无法恢复，且所有其下子类会一并删除，已绑定这些类别的设备将无法显示设备分类，确定要删除吗？', {icon: 3, title:'删除父分类【'+cate_name+'】'}, function(index){
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
                                            window.parent.location.reload();
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
            }
        });
        //点击类别
        $(document).on('click','.getCategory',function(){
            var keyword = $("input[name='keyword']").val();
            //重载表格
            var params = {};
            params.catid = $(this).attr('data-catid');
            params.keyword = '';
            var thisCategory = $(this).attr('data-category');
            table.reload('category', {
                url: category
                ,where: params
                ,page: {
                    curr: 1 //重新从第 1 页开始
                },done: function(){
                    if (keyword != ''){
                        //高亮操作
                        gl(keyword);
                    }
                }
            });
            //更改显示标题
            $(".detailName").html($(this).html());
            //更改父id
            $("input[name='catid']").val(params.catid);
            //更改分类名称
            $("input[name='category']").val(thisCategory);
            return false;
        });

        //搜索按钮
        form.on('submit(cateSearch)', function(data){
            gloabOptions = data.field;
            var table = layui.table;
            table.reload('category', {
                url: category
                ,where: gloabOptions
                ,page: {
                    curr: 1 //重新从第 1 页开始
                },done: function(res){
                    if (res.parentCategory){
                        var parentCategory = res.parentCategory;
                        //改变菜单
                        changeCategory(parentCategory);
                        //改变其他信息
                        fisrtCategoryInfo(parentCategory);
                        //高亮操作
                        gl(gloabOptions.keyword);
                    }
                }
            });
            return false;
        });
        //控制子侧边菜单文字太长时省略号变成提示
        var item = $("#LAY-BaseSetting-IntegratedSetting-category").find(".layui-nav-item");
        $.each(item,function(j,val){
            if ($(val).children().html().length>14){
                var tips = $(val).children().html();
                $(val).attr("title",tips)
            }
        });

        //监听伸缩事件，控制子侧边菜单的绝对定位
        admin.on('side(leftChildmenu)', function(obj){
            var categoryListObj  = $("#LAY-BaseSetting-IntegratedSetting-category");
            if (obj.status == null){
                categoryListObj.find(".layui-side-child").css("left",80);
                categoryListObj.find(".addDetail").css("right",145);
            }else {
                categoryListObj.find(".layui-side-child").css("left",235);
                categoryListObj.find(".addDetail").css("right",0);
            }
        });

        //重置按钮
        $("#LAY-BaseSetting-IntegratedSetting-categoryReset").click(function(){
            $.getJSON(category+'?action=getParentCategory',function(d){
                //改变菜单
                changeCategory(d);
                //改变其他信息
                fisrtCategoryInfo(d);
                //重载子分类
                table.reload('category', {
                    url: category
                    ,where: {catid:d[0]['catid'],keyword:''}
                    ,page: {
                        curr: 1
                    },done: function(){

                    }
                });
            })
        });

        /**
         * 改变父分类
         * @param parentCategory 父分类的对象
         */
        function changeCategory(parentCategory){
            var html = '',catelist = $(".catelist");
            if (parentCategory == ''){
                html += '<li class="layui-nav-item"><a href="javascript:;" style="text-decoration: none">暂无相关数据</a></li>';
                catelist.html(html);
            }else {
                $.each(parentCategory, function(k, v){
                    html +=
                        '<li class="layui-nav-item">' +
                        '<a href="javascript:;" class="getCategory" data-category="'+ v.category+'" data-catid="'+ v.catid+'" style="text-decoration: none" title="'+v.catenum+'：'+v.category +'">'+v.catenum+'：'+v.category +
                        '</a>' +
                        '</li>';
                });
                catelist.html(html);
            }
        }

        /**
         * 动态改变分类信息
         * @param categoryInfo 传入的分类对象
         */
        function fisrtCategoryInfo(categoryInfo){
            //更改显示标题
            if (categoryInfo){
                $(".detailName").html(categoryInfo[0]['catenum']+'：'+categoryInfo[0]['category']);
                //更改父id
                $("input[name='catid']").val(categoryInfo[0]['catid']);
                //更改分类名称
                $("input[name='category']").val(categoryInfo[0]['category']);
            }else {
                $(".detailName").html('');
            }
        }


        /**
         * 高亮中间过程
         * @param html 传文字段落
         * @param keyword 关键字
         * @returns {XML|*|Cropper|string|void}
         */
        function gaoliang(html,keyword){
            var gaoliangHtml = '<span style="background-color:yellow">'+keyword+"</span>";
            var reg = new RegExp(keyword,"g");
            newHtml = html.replace(reg,gaoliangHtml);
            return newHtml;
        }

        /**
         * 封装高亮的操作
         * @param keyword 传关键字
         */
        function gl(keyword){
            var catelistObj = $(".catelist").find('li');
            //分两步 先改父类
            catelistObj.each(function(k,v){
                var aHtmlObj = $(v).children('a');
                aHtmlObj.html(gaoliang(aHtmlObj.html(),keyword));
            });
            //再改子类和品名
            var tableObj = $("#LAY-BaseSetting-IntegratedSetting-category").find(".layui-table-main .layui-table td");
            tableObj.each(function(k,v){
                var dataField = $(v).attr('data-field');
                if (dataField == 'category' || dataField == 'remark'){
                    var divHtmlObj = $(v).children();
                    divHtmlObj.html(gaoliang(divHtmlObj.html(),keyword));
                }
            });
        }
    });
    exports('basesetting/integratedSetting/category', {});
});
