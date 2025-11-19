layui.define(function (exports) {
    var gloabOptions = {};
    layui.use(['table', 'suggest', 'laydate', 'form', 'upload', 'tablePlug'], function () {
        var table = layui.table, suggest = layui.suggest, form = layui.form,laydate = layui.laydate, tablePlug = layui.tablePlug;
        form.render();
        //监听提交
        form.on('submit(userTraSearch)', function (data) {
            gloabOptions = data.field;
            //搜索时重新初始化全局搜索参数，方便修改删除用户时候刷表格面用
            //刷新表格时，默认回到第一页
            var table = layui.table;
            table.reload('userTraLists', {
                url: userTra
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });
        layer.config(layerParmas());
        laydate.render({
            elem: '#scan_date' //指定元素
        });

        //初始化搜索建议插件
        suggest.search();
        table.render({
            elem: '#userTraLists'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , title: '用户轨迹列表'
            , url: userTra //数据接口
            , where: {
                sort: 'userid'
                , order: 'desc'
            } //如果无需传递额外参数，可不加该参数
            , initSort: {
                field: 'userid' //排序字段，对应 cols 设定的各字段名
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
            },
            defaultToolbar: ['filter', 'exports']
            , cols: [[ //表头
                {type: 'checkbox', style: 'background-color: #f9f9f9;'}
                , {
                    field: 'userid',
                    title: '序号',
                    width: 65,
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'username',
                    hide: get_now_cookie(userid + cookie_url + '/username') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    title: '用户名',
                    width: 120,
                    align: 'center'
                }
                , {
                    field: 'scan_date',
                    hide: get_now_cookie(userid + cookie_url + '/scan_date') == 'false' ? true : false,
                    style: 'background-color: #f9f9f9;',
                    title: '日期',
                    width: 120,
                    align: 'center'
                }
                , {
                    field: 'trajectory',
                    hide: get_now_cookie(userid + cookie_url + '/trajectory') == 'false' ? true : false,
                    title: '用户轨迹',
                    minWidth: 800,
                    align: 'center',
                    rowspan: 2,
                    templet: function (rows) {
                        let div = '';
                        rows.departs.forEach((item, index) => {
                            let tem = '<div class="layui-col-md2" style="text-align: center;">\n' +
                                '    <div class="layui-panel">\n' +
                                '      <div>' + item + '</div>\n' +
                                '      <div>' + rows.scantime[index] + '</div>\n' +
                                '    </div>   \n' +
                                '  </div>';
                            if((index+1) !== rows.departs.length){
                                tem += '<div class="layui-col-md1" style="height: 56px;line-height:56px;text-align: center;">\n' +
                                    '      <div>➔</div>\n' +
                                    '  </div>';
                            }
                            div += tem;
                        });
                        return div;
                    }
                }
            ]]
            , done: function (res, curr, count) {
                //如果是异步请求数据方式，res即为你接口返回的信息。
                //如果是直接赋值的方式，res即为：{data: [], count: 99} data为当前页数据、count为数据总长度
            }
        });

        form.on('checkbox', function (data) {
            var type = $(this).attr('lay-filter');
            if (type == 'LAY_TABLE_TOOL_COLS') {
                var key = data.elem.name;
                var status = data.elem.checked;
                document.cookie = userid + cookie_url + '/' + key + '=' + status + "; expires=Fri, 31 Dec 9999 23:59:59 GMT";
            }
            //
        });

        //监听排序
        table.on('sort(userTraData)', function (obj) {
            //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            //尽管我们的 table 自带排序功能，但并没有请求服务端。
            //有些时候，你可能需要根据当前排序的字段，重新向服务端发送请求，从而实现服务端排序，如：
            table.reload('userTraLists', {
                initSort: obj //记录初始排序，如果不设的话，将无法标记表头的排序状态。 layui 2.1.1 新增参数
                , where: { //请求参数（注意：这里面的参数可任意定义，并非下面固定的格式）
                    sort: obj.field //排序字段
                    , order: obj.type //排序方式
                }
            });
        });
        /*
         /选择用户
         */
        $("#bsSuggestUserTra").bsSuggest(
            returnUser()
        );
    });
    exports('basesetting/user/userTra', {});
});




