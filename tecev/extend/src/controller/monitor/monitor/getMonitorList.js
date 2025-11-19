layui.define(function (exports) {
    getAllAssetCount()

    var exportAssetsID = '';//导出设备assID
    window.localStorage.setItem('exportAssetsID', exportAssetsID);
    layui.use(['admin', 'layer', 'form', 'formSelects', 'laydate', 'table', 'suggest', 'upload', 'tablePlug', 'soulTable'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, laydate = layui.laydate,
            table = layui.table, suggest = layui.suggest, upload = layui.upload, tablePlug = layui.tablePlug,
            soulTable = layui.soulTable;

        // 定一个全局变量 存所有设备编号
        var all_assnum;


        //初始化搜索建议插件
        suggest.search();

        layer.config(layerParmas());

        //先更新页面部分需要提前渲染的控件
        form.render();

        //定义一个全局空对象
        var gloabOptions = {};
        table.render({
            elem: '#monitorLists'
            , limits: [100, 500, 1000, 5000]
            , loading: true
            , limit: 100
            , height: 'full-150'
            , title: '接口日志'
            , url: getMonitorList //数据接口
            , where: {
                sort: 'id'
                , order: 'desc'
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
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            },
            toolbar: '#LAY-Monitor-Monitor-getMonitorListToolbar',
            defaultToolbar: []
            , cols: [[ //表头
                {type: 'checkbox', fixed: 'left'},
                {
                    field: 'id',
                    title: '序号',
                    width: 65,
                    align: 'center',
                    templet: function (d) {
                        return d.LAY_INDEX
                    }
                }
                , {field: 'assnum', title: '设备编码', width: 160, align: 'center'}
                , {field: 'assets', title: '设备名称', width: 160, align: 'center'}
                , {
                    field: 'model', title: '来源系统', width: 140, align: 'center',
                    templet: function (d) {
                        return '物联网';
                    }
                },
                {field: 'powerOnTime', title: '最近开机时间', width: 140, align: 'center'},
                {field: 'useTotalTime', title: '监控总时长', width: 140, align: 'center'},
                {field: 'powerOnTotals', title: '开机次数', width: 140, align: 'center'},
                {field: 'runningDuration', title: '开机总时长', width: 140, align: 'center'},
                {field: 'useRate', title: '开机率', width: 140, align: 'center'},
                {field: 'realRunningTotals', title: '运行总次数', width: 140, align: 'center'},
                {field: 'realRunningDuration', title: '运行总时长', width: 140, align: 'center'},
                {field: 'runUseRate', title: '使用率', width: 140, align: 'center'},
                {field: 'powerOnUtilizeRate', title: '开机利用率', width: 140, align: 'center'},
                {field: 'exposureNum', title: '激发次数', width: 140, align: 'center'},
                {field: 'exposureTime', title: '激发时长', width: 140, align: 'center'},
                {field: 'energyWork', title: '能耗', width: 140, align: 'center'},
                {
                    field: 'isOnline',
                    title: '在线状态',
                    width: 120,
                    align: 'center',
                    templet: function (d) {
                        switch (d.status) {
                            case "0":
                                return '在线';
                            case "1":
                                return '不在线';
                            default:
                                return '';
                        }
                    }
                },
                {
                    field: 'isAvailable',
                    title: '是否可用',
                    width: 120,
                    align: 'center',
                    templet: function (d) {
                        switch (d.status) {
                            case "0":
                                return '可用';
                            case "1":
                                return '故障';
                            default:
                                return '';
                        }
                    }
                },
                {
                    field: 'status',
                    title: '使用状态',
                    width: 120,
                    align: 'center',
                    templet: function (d) {
                        switch (d.status) {
                            case "0":
                                return '在线';
                            case "1":
                                return '不在线';
                            default:
                                return '';
                        }
                    }
                },
                {
                    field: 'evaluate',
                    title: '评价',
                    width: 120,
                    align: 'center',
                    templet: function (d) {
                        switch (d.evaluate) {
                            case "1":
                                return '优';
                            case "2":
                                return '良';
                            case "3":
                                return '一般';
                            case "4":
                                return '差';
                            default:
                                return '';
                        }
                    }
                },
                {field: 'place', title: '位置', width: 140, align: 'center'},
                {field: 'did', title: '资产id', width: 140, align: 'center'}
            ]],
            done: function (res) {
                all_assnum = res.all_assnum
            }
        });

        //搜索按钮
        form.on('submit(monitorListsSearch)', function (data) {
            gloabOptions = data.field;
            console.log(gloabOptions)
            table.reload('monitorLists', {
                url: getMonitorList
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                },
                done: function (res) {
                    all_assnum = res.all_assnum
                }
            });
            return false;
        });

        table.on('toolbar(monitorLists)', function (obj) {
            var event = obj.event,
                url = $(this).attr('data-url'),
                flag = 1;
            switch (event) {
                case 'download':
                    const all_checked = table.checkStatus('monitorLists');
                    const {data: checked} = all_checked
                    let assnum;
                    let count;
                    if (checked.length > 0) {
                        assnum = checked.map(item => item.assnum)
                        count = assnum.length
                    } else {
                        assnum = all_assnum
                        count = all_assnum.length;
                    }
                    layer.open({
                        type: 1,
                        area: '350px',
                        move: false,
                        shade: 0.5,
                        closeBtn: 0,
                        maxmin: false,
                        resize: false,
                        title: '下载设备数据',
                        content: `
          <div class="layui-form" style="margin: 16px;">
            <div class="demo-login-container">
              <div class="layui-form-item">
                <div class="layui-inline">
      <label class="layui-form-label">系统名称：</label>
      <div class="layui-input-inline">
  <select lay-verify="required">
      <option value="中科物联网">中科物联网</option>
    </select>
      </div>
    </div>
    </div>
              <div class="layui-form-item">
                <div class="layui-inline">
      <label class="layui-form-label"><span style="color: red;">*</span>开始时间：</label>
      <div class="layui-input-inline">
        <input lay-verify="required" type="text" class="layui-input" id="ID-laydate-demo" name="startTime" placeholder="yyyy-MM-dd HH:mm:ss">
      </div>
    </div>
        <div class="layui-inline">
      <label class="layui-form-label"><span style="color: red;">*</span>结束时间：</label>
      <div class="layui-input-inline">
        <input lay-verify="required" type="text" class="layui-input" id="ID-laydate-demo1" name="endTime" placeholder="yyyy-MM-dd HH:mm:ss">
      </div>
    </div>
              </div>
                <div class="layui-form-item" style="display: flex;justify-content: center">
                <button style="flex-grow: 1" class="layui-btn" lay-submit lay-filter="download">开始</button>
                <button style="flex-grow: 1" class="layui-btn layui-btn-primary" id="goBack">返回</button>
              </div>
            </div>
              <div style="margin: 16px;text-align: center;color: red;">提示：下载${count}条设备的实施数据</div>
          </div>
        `,
                        success: function () {
                            laydate.render({
                                elem: '#ID-laydate-demo',
                                type: 'date'
                            });
                            laydate.render({
                                elem: '#ID-laydate-demo1',
                                type: 'date'
                            });
                            // 对弹层中的表单进行初始化渲染
                            form.render();
                            // 表单提交事件
                            form.on('submit(download)', function (data) {
                                var field = data.field; // 获取表单字段值
                                $.ajax({
                                    type: "POST",
                                    url: '/A/Monitor/downloadAssetsData',
                                    data: {assnum: assnum.join(","), ...field},
                                    dataType: "json",
                                    // success: function () {
                                    //
                                    // },
                                    complete: function(){
                                        layer.closeAll()
                                        layer.alert(`下载了${count}条记录`,{},function (){
                                            layer.closeAll()
                                            table.reload('monitorLists', {
                                                url: getMonitorList
                                                , where: gloabOptions
                                                , page: {
                                                    curr: 1 //重新从第 1 页开始
                                                },
                                                done: function (res) {
                                                    all_assnum = res.all_assnum
                                                }
                                            });
                                        });
                                    }
                                });
                                return false; // 阻止默认 form 跳转
                            });

                            $("#goBack").on('click', function () {
                                layer.closeAll()
                            });
                        }
                    })
                    break;
            }
        });


        //设备名称搜索建议
        $("#getMonitorListAssets").bsSuggest(
            returnAssets()
        );

        //设备编号搜索建议
        $("#getMonitorListAssnum").bsSuggest(
            returnAssnum()
        )

    });
    exports('monitor/monitor/getMonitorList', {});
});


// 每次进页面和刷新页面都调用此方法更新上方面板的数据
function getAllAssetCount() {
    $.ajax({
        type: "POST",
        url: '/A/Monitor/AssetTagStatusStatisticsQuantity',
        dataType: "json",
        success: function (res) {
            if (res.code === 200) {
                const {data} = res;
                // 找到统计的那个div 一个个赋值进去就是了 对应页面显示的顺序
                const parents = $("#LAY-Monitor-Monitor-getMonitorList .response-data");
                $(parents).children()[0].innerHTML = data.totalCount;
                $(parents).children()[1].innerHTML = data.onCount;
                $(parents).children()[2].innerHTML = data.offCount;
                $(parents).children()[3].innerHTML = data.great;
                $(parents).children()[4].innerHTML = data.good;
                $(parents).children()[5].innerHTML = data.normal;
                $(parents).children()[6].innerHTML = data.weak;
            }
        }
    });
}


