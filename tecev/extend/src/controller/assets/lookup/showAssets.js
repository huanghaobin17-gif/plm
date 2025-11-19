layui.define(function (exports) {
    layui.use(['layer', 'form', 'element', 'formSelects', 'laydate', 'table', 'upload', 'tipsType', 'carousel', 'suggest', 'tablePlug', 'asyncFalseUpload'], function () {
        var layer = layui.layer, form = layui.form, formSelects = layui.formSelects, element = layui.element,
            laydate = layui.laydate, table = layui.table, upload = layui.upload, tipsType = layui.tipsType,
            carousel = layui.carousel, suggest = layui.suggest, tablePlug = layui.tablePlug, asyncFalseUpload = layui.asyncFalseUpload;
        layer.config(layerParmas());

        //轮播
        var carouselOption = {
            elem: '#carouselAssetsPic'
            , arrow: 'always'
            , width: '290px'
            , height: '410px'
        };
        carousel.render(carouselOption);

        //点击轮播的图片显示相册层
        layer.photos({
            photos: '#carouselAssetsPic'
            , anim: 5
            , maxmin: false
        });
        element.on('tab(tableChange)', function () {
            table.resize();
        });
        tipsType.choose();
        formSelects.render('suppliers_type', selectParams(2));
        formSelects.btns('suppliers_type', selectParams(2));
        laydate.render(dateConfig('#doRenewalBuydate'));

        //初始化搜索建议插件
        suggest.search();

        //如果有需要切换tab
        var tabVal = localStorage.getItem('changeTechTab');
        element.tabChange('change', tabVal);
        localStorage.removeItem('changeTechTab');
        laydate.render({
            elem: '#doRenewalStartdate',
            calendar: true
            , min: 0
        });
        laydate.render({
            elem: '#doRenewalOverdate',
            calendar: true
            , min: 1
        });
        var assid = $("input[name='assid']").val();
        //选择的文件数量
        var chooseFileNum = 0;
        //绑定的设备Id
        var bindAssetsAssid = [];

        //技术资料相同设备列表
        table.render({
            elem: '#sameAssetsList'
            , limits: [10, 20, 50, 100]
            , loading: true
            , size: 'sm'
            , limit: 10
            , url: admin_name + '/Lookup/showAssets' //数据接口
            , where: {
                action: 'getSameAssetsList'
                , sort: 'assid'
                , order: 'desc'
                , assid: assid
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
            , page: {
                groups: 10 //只显示 1 个连续页码
                , prev: '上一页'
                , next: '下一页'
            }
            , cols: [[ //表头
                {type: 'checkbox'},
                {
                    field: 'assid', title: '序号', width: 65, align: 'center', type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {field: 'assets', title: '设备名称', width: 260, align: 'center'}
                , {field: 'model', title: '规格/型号', align: 'center'}
                , {field: 'brand', title: '品牌', align: 'center'}
                , {field: 'operation', title: '操作', width: 100, align: 'center'}
            ]],
            done: function () {
                if (bindAssetsAssid.length > 0) {
                    var allTd = $("#sameAssetsList").next().find('.layui-table-body .layui-table tr td');
                    $(allTd).each(function (k, v) {
                        if ($(v).attr('data-field') == 'assid') {
                            if ($.inArray($(v).attr('data-content'), bindAssetsAssid) != -1) {
                                $($($(v).parent()).find('td')[0]).find('.layui-table-cell').html('');
                                $($(v).parent()).find('td button').addClass('layui-btn-disabled');
                                $($(v).parent()).find('td button').html('已绑定');
                                $($(v).parent()).find('td button').removeAttr('lay-event');
                            }
                        }
                    });
                }
            }
        });

        //批量绑定
        $("#batchSameAssetsBind").click(function () {
            var checkStatus = table.checkStatus('sameAssetsList'),
                data = checkStatus.data;
            if (chooseFileNum == 0) {
                layer.msg('请先在技术资料表格右方本地上传中选择需要上传的文件', {icon: 2});
            } else {
                if (data.length == 0) {
                    layer.msg('请先选择一台设备进行绑定', {icon: 2});
                } else {
                    layer.confirm('绑定仅该次上传文件生效，是否需要绑定？', function (index) {
                        var waitBindId = [];
                        $(data).each(function (k, v) {
                            waitBindId.push(v.assid);
                        });
                        var allTd = $("#sameAssetsList").next().find('.layui-table-body .layui-table tr td');
                        $(allTd).each(function (k, v) {
                            if ($(v).attr('data-field') == 'assid') {
                                if ($.inArray($(v).attr('data-content'), waitBindId) != -1) {
                                    $($($(v).parent()).find('td')[0]).find('.layui-table-cell').html('');
                                    $($(v).parent()).find('td button').addClass('layui-btn-disabled');
                                    $($(v).parent()).find('td button').html('已绑定');
                                    $($(v).parent()).find('td button').removeAttr('lay-event');
                                    bindAssetsAssid.push($(v).attr('data-content'));
                                }
                            }
                        });
                        layer.msg('绑定成功', {icon: 1});
                        layer.close(index);
                    });
                }
            }
        });

        //搜索按钮
        form.on('submit(sameAssetsSearch)', function (data) {
            gloabOptions = data.field;
            gloabOptions.action = 'getSameAssetsList';
            table.reload('sameAssetsList', {
                url: admin_name + '/Lookup/showAssets'
                , where: gloabOptions
                , page: {
                    curr: 1 //重新从第 1 页开始
                }
            });
            return false;
        });

        //设备名称搜索建议
        $("#getAssetsListAssets").bsSuggest(
            returnAssets()
        );
        //技术资料相同设备列表操作栏按钮
        table.on('tool(sameAssetsListData)', function (obj) { //注：tool是工具条事件名，test是table原始容器的属性 lay-filter="对应的值"
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event; //获得 lay-event 对应的值（也可以是表头的 event 参数对应的值）
            var tr = obj.tr; //获得当前行 tr 的DOM对象
            var url = $(this).attr('data-url');
            switch (layEvent) {
                case 'bind'://编辑主设备详情
                    if (chooseFileNum == 0) {
                        layer.msg('请先在技术资料表格右方本地上传中选择需要上传的文件', {icon: 2});
                    } else {
                        layer.confirm('绑定仅该次上传文件生效，是否需要绑定？', function (index) {
                            $(tr).find('td button').addClass('layui-btn-disabled');
                            $(tr).find('td button').html('已绑定');
                            $(tr).find('td button').removeAttr('lay-event');
                            $($(tr).find('td')[0]).find('.layui-table-cell').html('');
                            layer.msg('绑定成功', {icon: 1});
                            bindAssetsAssid.push(rows.assid);
                            layer.close(index);
                        });
                    }
                    break;
            }
        });

        //上传技术资料
        var fileListView1 = $('#qualifileList')
            , uploadListIns1 = upload.render({
            elem: '#multifile1'
            , url: 'addAssets'
            , data: {"assid": assid, "type": 'technical', "action": 'uploadFile'}
            , accept: 'file'
            , exts: 'jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx|rar|zip'
            , multiple: true
            , auto: false
            , bindAction: '#uploadFileQuali'
            , choose: function (obj) {
                $('.quali-empty').remove();
                var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
                var tr = $('#fileList').find('tr');
                var xuhao = tr.length + 1;
                //读取本地文件
                obj.preview(function (index, file, result) {
                    var tr = $(['<tr id="upload-' + index + '">'
                        , '<td class="xuhao td-align-center">' + xuhao + '</td>'
                        , '<td class="td-align-center">' + file.name + '</td>'
                        , '<td class="td-align-center"><span class="is_upload">等待上传</span></td>'
                        , '<td class="td-align-center">'
                        , '<button class="layui-btn layui-btn-xs layui-btn-danger file-delete"><i class="layui-icon">&#xe640;</i></button>'
                        , '</td>'
                        , '</tr>'].join(''));

                    //删除
                    tr.find('.file-delete').on('click', function () {
                        delete files[index]; //删除对应的文件
                        tr.remove();
                        //重新编排序号
                        var alltr = $('#qualifileList').find('tr');
                        var i = 1;
                        $.each(alltr, function () {
                            $(this).find('td.xuhao').html(i);
                            i++;
                        });
                        uploadListIns1.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                    });
                    fileListView1.append(tr);
                    chooseFileNum = chooseFileNum + 1;
                });
            }
            , done: function (res, index, upload) {
                if (res.status == 1) { //上传成功
                    var tr = fileListView1.find('tr#upload-' + index)
                        , tds = tr.children();
                    tds.eq(2).html('<span style="color: #5FB878;" class="is_upload">上传成功</span>');
                    tds.eq(3).html(res.html); //清空操作
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: 'addAssets',
                        data: {
                            "tech_id": res.tech_id,
                            "bindAssetsAssid": bindAssetsAssid,
                            "action": "bindSameAssetsFile"
                        },
                        dataType: "json",
                        success: function (data) {

                        },
                        error: function () {
                            layer.msg("网络访问失败", {icon: 2}, 1000);
                        }
                    });
                    return delete this.files[index]; //删除文件队列已经上传成功的文件
                }
                this.error(index, upload);
            }
            , allDone: function (obj) { //当文件全部被提交后，才触发
                if (obj.total == obj.successful) {
                    layer.msg('上传技术资料成功', {icon: 1}, 1000);
                    localStorage["changeTechTab"] = 3;
                    setTimeout(function () {
                        location.reload();
                    }, 2500);
                } else {
                    layer.msg('上传失败', {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                var tr = fileListView1.find('tr#upload-' + index)
                    , tds = tr.children();
                tds.eq(2).html('<span style="color: #FF5722;" class="is_upload">上传失败</span>');
            }
        });

        //上传维保文件
        uploadFile = upload.render({
            elem: '#file_url'  //绑定元素
            , url: admin_name + '/Lookup/doRenewal' //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {type: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status == 1) {
                    $(".file_url").append('<span class="file_data" data-name="' + res.name + '"><span>' + res.formerly + '</span><input type="hidden" name="path" class="path" value="' + res.path + '"><a style="color: red;text-decoration: none;cursor: pointer;"  onclick="delDoRenewaFile(this)">  删除  </a></span>');
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });
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
                , shade: 0.3
                , btn: btn
                , yes: function (index) {
                    var params = {};
                    params.path = path;
                    params.filename = name;
                    postDownLoadFile({
                        url: admin_name + '/Tool/downFile',
                        data: params,
                        method: 'POST'
                    });
                }
                , btn2: function (index, layero) {
                    var url = admin_name + '/Tool/showFile';
                    top.layer.open({
                        type: 2,
                        title: name + '相关文件查看',
                        scrollbar: false,
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        area: ['75%', '100%'],
                        closeBtn: 1,
                        content: [url + '?path=' + path + '&filename=' + name]
                    });
                    return false;
                }
                , cancel: function () {
                    //右上角关闭回调
                }
            });
        });

        //删除设备图片按钮
        $("#deleteAssetsPicBtn").click(function () {
            var carouselAssetsPicObj = $("#carouselAssetsPic"),
                thisSrc = carouselAssetsPicObj.find(".layui-this").attr("src"),
                thisIndex = (parseInt(carouselAssetsPicObj.find(".layui-this").attr("layer-index")) + 1);
            layer.confirm('确认删除第' + thisIndex + '张照片吗？', {
                btn: ['是', '否'],
                title: false,
                closeBtn: 0
            }, function () {
                var params = {};
                params.src = thisSrc;
                params.assid = assid;
                params.action = 'deleteAssetsPic';
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: admin_name + '/Lookup/showAssets',
                    data: params,
                    dataType: "json",
                    async: true,
                    beforeSend: beforeSend,
                    success: function (data) {
                        if (data.status == 1) {
                            layer.msg(data.msg, {icon: 1}, 1000);
                            setTimeout(function () {
                                location.reload();
                            }, 1000)
                        } else {
                            layer.msg(data.msg, {icon: 2}, 1000);
                        }
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2}, 1000);
                    },
                    complete: complete
                });
            });

        });

        //上传资质文件按钮
        //var fileListView = $('#fileList')
        //    , uploadListIns = upload.render({
        //    elem: '#multifile'
        //    , url: 'addAssets'
        //    , data: {"assid": assid, "type": 'quali', "action": 'uploadFile'}
        //    , accept: 'file'
        //    , exts: 'jpg|jpeg|png|pdf'
        //    , multiple: true
        //    , auto: false
        //    , bindAction: '#uploadFileTech'
        //    , choose: function (obj) {
        //        $('.tech-empty').remove();
        //        var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
        //        var tr = $('#fileList').find('tr');
        //        var xuhao = tr.length + 1;
        //        //读取本地文件
        //        obj.preview(function (index, file, result) {
        //            var tr = $(['<tr id="upload-' + index + '">'
        //                , '<td class="xuhao td-align-center">' + xuhao + '</td>'
        //                , '<td class="td-align-center">' + file.name + '</td>'
        //                , '<td class="td-align-center"><span class="is_upload">等待上传</span></td>'
        //                , '<td class="td-align-center">'
        //                , '<button class="layui-btn layui-btn-xs layui-btn-danger file-delete"><i class="layui-icon">&#xe640;</i></button>'
        //                , '</td>'
        //                , '</tr>'].join(''));
        //
        //            //删除
        //            tr.find('.file-delete').on('click', function () {
        //                delete files[index]; //删除对应的文件
        //                tr.remove();
        //                //重新编排序号
        //                var alltr = $('#fileList').find('tr');
        //                var i = 1;
        //                $.each(alltr, function () {
        //                    $(this).find('td.xuhao').html(i);
        //                    i++;
        //                });
        //                uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
        //            });
        //            fileListView.append(tr);
        //        });
        //    }
        //    , done: function (res, index, upload) {
        //        if (res.status == 1) { //上传成功
        //            var tr = fileListView.find('tr#upload-' + index)
        //                , tds = tr.children();
        //            tds.eq(2).html('<span style="color: #5FB878;" class="is_upload">上传成功</span>');
        //            tds.eq(3).html(res.html); //清空操作
        //            return delete this.files[index]; //删除文件队列已经上传成功的文件
        //        }
        //        this.error(index, upload);
        //    }
        //    , error: function (index, upload) {
        //        var tr = fileListView.find('tr#upload-' + index)
        //            , tds = tr.children();
        //        tds.eq(2).html('<span style="color: #FF5722;" class="is_upload">上传失败</span>');
        //    }
        //});
        //上传技术文件按钮

        //上传设备档案按钮
        var uploadHandle = false;
        var changeArchiveTab = localStorage.getItem('archive_tab');
        if (changeArchiveTab) {
            element.tabChange('change', parseInt(changeArchiveTab));
            localStorage.setItem('archive_tab', '');
        }
        lay('.archives-time').each(function () {
            laydate.render({
                elem: this
                , trigger: 'click'
                , isInitValue: false
                , done: function (value, date, endDate) {

                }
            });
        });
        lay('.expire-time').each(function () {
            laydate.render({
                elem: this
                , trigger: 'click'
                , isInitValue: false
                , done: function (value, date, endDate) {

                }
            });
        });
        //记录已上传的文件
        var need_upload_files = [];
        var fileListView2 = $('#arcfileList')
            , uploadListIns2 = asyncFalseUpload.render({
            elem: '#multifile2'
            , url: 'addAssets'
            , data: {"assid": assid, "type": 'archives', "action": "uploadFile"}
            , accept: 'file'
            , exts: 'jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx'
            , multiple: true
            , auto: false
            , bindAction: '#testUpload'
            , choose: function (obj) {
                $('.arc-empty').remove();
                var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
                var tr = $('#arcfileList').find('tr');
                var xuhao = tr.length + 1;
                //读取本地文件
                obj.preview(function (index, file, result) {
                    need_upload_files.push(index);
                    var tr = $(['<tr id="upload-' + index + '">'
                        , '<td class="xuhao td-align-center">' + xuhao + '</td>'
                        , '<td class="td-align-center">' + file.name + '</td>'
                        , '<td class="td-align-center"><span class="is_upload">等待上传</span></td>'
                        , '<td class="td-align-center" style="padding: 0;"><input type="text" name="archives_time[]" value="' + today + '" readonly placeholder="点击选择日期" class="layui-input archives-time" style="cursor: pointer;border: none;height: 49px;"></td>'
                        , '<td class="td-align-center" style="padding: 0;"><input type="text" name="expire_time[]" value="" readonly placeholder="点击选择日期" class="layui-input expire-time" style="cursor: pointer;border: none;height: 49px;"></td>'
                        , '<td class="td-align-center">'
                        , '<button class="layui-btn layui-btn-xs layui-btn-danger file-delete"><i class="layui-icon">&#xe640;</i></button>'
                        , '</td>'
                        , '</tr>'].join(''));
                    //删除
                    tr.find('.file-delete').on('click', function () {
                        delete files[index]; //删除对应的文件
                        tr.remove();
                        //重新编排序号
                        var alltr = $('#arcfileList').find('tr');
                        var i = 1;
                        $.each(alltr, function () {
                            $(this).find('td.xuhao').html(i);
                            i++;
                        });
                        uploadListIns2.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                    });
                    fileListView2.append(tr);
                    //动态同时绑定多个选择时间   档案管理
                    tr.find('.archives-time').each(function () {
                        laydate.render({
                            elem: this
                            , trigger: 'click'
                        });
                    });
                    tr.find('.expire-time').each(function () {
                        laydate.render({
                            elem: this
                            , trigger: 'click'
                        });
                    });
                });
            }
            , done: function (res, index, upload) {
                if (res.status == 1) { //上传成功
                    var tr = fileListView2.find('tr#upload-' + index)
                        , tds = tr.children();
                    tds.eq(2).html('<span style="color: #5FB878;" class="is_upload">上传成功</span>');
                    tds.eq(5).html(res.html); //清空操作
                    need_upload_files.remove(index);
                    return delete this.files[index]; //删除文件队列已经上传成功的文件
                }
                this.error(index, upload);
            },
            allDone: function (obj) { //当文件全部被提交后，才触发
                uploadHandle = true;
                localStorage.setItem('archive_tab', '4');
            }
            , error: function (index, upload) {
                var tr = fileListView2.find('tr#upload-' + index)
                    , tds = tr.children();
                tds.eq(2).html('<span style="color: #FF5722;" class="is_upload">上传失败</span>');
                //tds.eq(3).find('.file-reload').removeClass('layui-hide'); //显示重传
            }
        });
        form.on('submit(uploadFileArcSubmit)', function (data) {
            var params = data.field;
            params.assid = assid;
            params.action = 'uploadUpdate';
            $("#testUpload").click();
            if (need_upload_files.length == 0) {
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: admin_name + '/Lookup/showAssets',
                    data: params,
                    dataType: "json",
                    async: false,
                    success: function (data) {
                        window.location.reload();
                    },
                    error: function () {
                        layer.msg("网络访问失败", {icon: 2}, 1000);
                    }
                });
            } else {
                var timer = setInterval(function () {
                    if (uploadHandle) {
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: admin_name + '/Lookup/showAssets',
                            data: params,
                            dataType: "json",
                            async: false,
                            success: function (data) {
                                clearInterval(timer);
                                window.location.reload();
                            },
                            error: function () {
                                layer.msg("网络访问失败", {icon: 2}, 1000);
                            },

                        });
                    }
                }, 500);
            }
        });
        //非计划类文档 2020.07.09新增
        var tabTableType = '',unplanTable;
        element.on('tab(tableChange)', function (data) {
            var unplanDataListObj = $("#unplanDataList"),
                unplanClassObj = $(".unplan");
            unplanDataListObj.next().remove();
            switch (data.index) {
                case 3:
                    //维修
                    tabTableType = 'WX';
                    unplanClassObj.eq(0).after(unplanDataListObj);
                    break;
                case 4:
                    //质控
                    tabTableType = 'ZK';
                    unplanClassObj.eq(1).after(unplanDataListObj);
                    break;
                case 5:
                    //计量
                    tabTableType = 'JL';
                    unplanClassObj.eq(2).after(unplanDataListObj);
                    break;
                case 6:
                    //不良事件
                    tabTableType = 'BLSJ';
                    unplanClassObj.eq(3).after(unplanDataListObj);
                    break;
            }
            unplanTable = table.render({
                elem: '#unplanDataList',
                size: 'sm',
                where: {
                    assid: assid
                    , action: 'getUnplanData'
                    , type: tabTableType
                },
                response: {
                    statusName: 'code' //数据状态的字段名称，默认：code
                    , statusCode: 200 //成功的状态码，默认：0
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
                        field: 'arc_id', title: '序号', align: 'center', width: 60, unresize: true, templet: function (d) {
                            return d.LAY_INDEX;
                        }
                    },
                    {field: 'file_name', title: '文件名称', align: 'center', width: 200},
                    {field: 'add_time', title: '上传时间', align: 'center', width: 150, sort: true},
                    {field: 'archive_time', title: '档案日期', align: 'center', width: 150, sort: true},
                    {field: 'add_user', title: '添加者', align: 'center', width: 100},
                    {field: 'operation', title: '操作', align: 'center', width: 200}
                ]], done: function (res, curr, count) {
                    var tableView = this.elem.next();
                    var tableId = this.id;
                    // 初始化laydate
                    layui.each(tableView.find('td[data-field="archive_time"]'), function (index, tdElem) {
                        var id = $(tdElem).parent().find('td[data-field="arc_id"]').attr('data-content');
                        tdElem.onclick = function (event) {
                            layui.stope(event)
                        };
                        laydate.render({
                            elem: tdElem.children[0],
                            trigger: 'click'
                            ,done: function (value) {
                                var params = {};
                                params.arc_id = id;
                                params.action = 'updateUnplanFileDate';
                                params.archive_time = value;
                                $.ajax({
                                    timeout: 5000,
                                    type: "POST",
                                    url: showAssets,
                                    data: params,
                                    dataType: "json",
                                    success: function (data) {
                                        // window.location.reload();
                                    },
                                    error: function () {
                                        layer.msg("网络访问失败", {icon: 2}, 1000);
                                    }
                                });
                            }
                        })
                    });
                }
            });
        });
        upload.render({
            elem: '#unplan-upload0'
            , url: showAssets
            , data: {"assid": assid, "type": 'archives', "action": "uploadUnplanFile", "unplanType": 'WX'}
            , accept: 'file'
            , exts: 'jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx'
            , multiple: true
            , allDone: function (obj) { //当文件全部被提交后，才触发
                layer.msg('上传成功', {icon: 1}, function () {
                    unplanTable.reload({
                        where: {
                            assid: assid
                            , action: 'getUnplanData'
                            , type: tabTableType
                        }
                        , page: {
                            curr: 1 //重新从第 1 页开始
                        }, done: function (res, curr, count) {
                            var tableView = this.elem.next();
                            var tableId = this.id;
                            // 初始化laydate
                            layui.each(tableView.find('td[data-field="archive_time"]'), function (index, tdElem) {
                                var id = $(tdElem).parent().find('td[data-field="arc_id"]').attr('data-content');
                                tdElem.onclick = function (event) {
                                    layui.stope(event)
                                };
                                laydate.render({
                                    elem: tdElem.children[0],
                                    trigger: 'click'
                                    ,done: function (value) {
                                        var params = {};
                                        params.arc_id = id;
                                        params.action = 'updateUnplanFileDate';
                                        params.archive_time = value;
                                        $.ajax({
                                            timeout: 5000,
                                            type: "POST",
                                            url: showAssets,
                                            data: params,
                                            dataType: "json",
                                            success: function (data) {
                                                // window.location.reload();
                                            },
                                            error: function () {
                                                layer.msg("网络访问失败", {icon: 2}, 1000);
                                            }
                                        });
                                    }
                                })
                            });
                        }
                    });
                });
            }
        });
        upload.render({
            elem: '#unplan-upload1'
            , url: showAssets
            , data: {"assid": assid, "type": 'archives', "action": "uploadUnplanFile", "unplanType": 'ZK'}
            , accept: 'file'
            , exts: 'jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx'
            , multiple: true
            , allDone: function (obj) { //当文件全部被提交后，才触发
                layer.msg('上传成功', {icon: 1}, function () {
                    unplanTable.reload({
                        where: {
                            assid: assid
                            , action: 'getUnplanData'
                            , type: tabTableType
                        }
                        , page: {
                            curr: 1 //重新从第 1 页开始
                        }, done: function (res, curr, count) {
                            var tableView = this.elem.next();
                            var tableId = this.id;
                            // 初始化laydate
                            layui.each(tableView.find('td[data-field="archive_time"]'), function (index, tdElem) {
                                var id = $(tdElem).parent().find('td[data-field="arc_id"]').attr('data-content');
                                tdElem.onclick = function (event) {
                                    layui.stope(event)
                                };
                                laydate.render({
                                    elem: tdElem.children[0],
                                    trigger: 'click'
                                    ,done: function (value) {
                                        var params = {};
                                        params.arc_id = id;
                                        params.action = 'updateUnplanFileDate';
                                        params.archive_time = value;
                                        $.ajax({
                                            timeout: 5000,
                                            type: "POST",
                                            url: showAssets,
                                            data: params,
                                            dataType: "json",
                                            success: function (data) {
                                                // window.location.reload();
                                            },
                                            error: function () {
                                                layer.msg("网络访问失败", {icon: 2}, 1000);
                                            }
                                        });
                                    }
                                })
                            });
                        }
                    });
                });
            }
        });
        upload.render({
            elem: '#unplan-upload2'
            , url: showAssets
            , data: {"assid": assid, "type": 'archives', "action": "uploadUnplanFile", "unplanType": 'JL'}
            , accept: 'file'
            , exts: 'jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx'
            , multiple: true
            , allDone: function (obj) { //当文件全部被提交后，才触发
                layer.msg('上传成功', {icon: 1}, function () {
                    unplanTable.reload({
                        where: {
                            assid: assid
                            , action: 'getUnplanData'
                            , type: tabTableType
                        }
                        , page: {
                            curr: 1 //重新从第 1 页开始
                        }, done: function (res, curr, count) {
                            var tableView = this.elem.next();
                            var tableId = this.id;
                            // 初始化laydate
                            layui.each(tableView.find('td[data-field="archive_time"]'), function (index, tdElem) {
                                var id = $(tdElem).parent().find('td[data-field="arc_id"]').attr('data-content');
                                tdElem.onclick = function (event) {
                                    layui.stope(event)
                                };
                                laydate.render({
                                    elem: tdElem.children[0],
                                    trigger: 'click'
                                    ,done: function (value) {
                                        var params = {};
                                        params.arc_id = id;
                                        params.action = 'updateUnplanFileDate';
                                        params.archive_time = value;
                                        $.ajax({
                                            timeout: 5000,
                                            type: "POST",
                                            url: showAssets,
                                            data: params,
                                            dataType: "json",
                                            success: function (data) {
                                                // window.location.reload();
                                            },
                                            error: function () {
                                                layer.msg("网络访问失败", {icon: 2}, 1000);
                                            }
                                        });
                                    }
                                })
                            });
                        }
                    });
                });
            }
        });
        upload.render({
            elem: '#unplan-upload3'
            , url: showAssets
            , data: {"assid": assid, "type": 'archives', "action": "uploadUnplanFile", "unplanType": 'BLSJ'}
            , accept: 'file'
            , exts: 'jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx'
            , multiple: true
            , allDone: function (obj) { //当文件全部被提交后，才触发
                layer.msg('上传成功', {icon: 1}, function () {
                    unplanTable.reload({
                        where: {
                            assid: assid
                            , action: 'getUnplanData'
                            , type: tabTableType
                        }
                        , page: {
                            curr: 1 //重新从第 1 页开始
                        }, done: function (res, curr, count) {
                            var tableView = this.elem.next();
                            var tableId = this.id;
                            // 初始化laydate
                            layui.each(tableView.find('td[data-field="archive_time"]'), function (index, tdElem) {
                                var id = $(tdElem).parent().find('td[data-field="arc_id"]').attr('data-content');
                                tdElem.onclick = function (event) {
                                    layui.stope(event)
                                };
                                laydate.render({
                                    elem: tdElem.children[0],
                                    trigger: 'click'
                                    ,done: function (value) {
                                        var params = {};
                                        params.arc_id = id;
                                        params.action = 'updateUnplanFileDate';
                                        params.archive_time = value;
                                        $.ajax({
                                            timeout: 5000,
                                            type: "POST",
                                            url: showAssets,
                                            data: params,
                                            dataType: "json",
                                            success: function (data) {
                                                // window.location.reload();
                                            },
                                            error: function () {
                                                layer.msg("网络访问失败", {icon: 2}, 1000);
                                            }
                                        });
                                    }
                                })
                            });
                        }
                    });
                });
            }
        });
        ////

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
            elem: '#Status',
            initSort: {
                field: 'id' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            },
            size: 'sm',
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
                    width: 60,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'changeTime', title: '变更时间', width: 300, align: 'center'},
                {field: 'remark', title: '说明', align: 'center'}
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
            elem: '#transfer',
            method: 'post',
            initSort: {
                field: 'atid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
            },
            size: 'sm',
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
                    field: 'atid', title: '序号', align: 'center', width: 60, unresize: true, templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'applicant_time', title: '申请时间', align: 'center', width: 145},
                {field: 'tranout_department', title: '转出科室', align: 'center', width: 140},
                {field: 'tranin_department', title: '转入科室', align: 'center', width: 140},
                {
                    field: 'approve_status', title: '审核状态', width: 80, align: 'center', templet: function (d) {
                        return d.approve_status == 0 ? '待审核' : (d.approve_status == 1 ? '<span style="color: green;">已通过</span>' : '<span style="color:red;">不通过</span>');
                    }
                },
                {
                    field: 'is_check', title: '验收状态', width: 80, align: 'center', templet: function (d) {
                        return d.is_check == 0 ? '待验收' : (d.is_check == 1 ? '<span style="color: green;">已通过</span>' : '<span style="color:red;">不通过</span>');
                    }
                },
                {field: 'tran_reason', title: '转科原因', align: 'center'}
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
                    width: 60,
                    align: 'center',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'repnum',
                    title: '维修单编号',
                    width: 160,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center'
                }
                , {field: 'statusName', title: '维修状态', width: 100, align: 'center'}
                , {
                    field: 'over_status', title: '是否修复', width: 100, align: 'center', templet: function (d) {
                        return d.over_status == 0 ? '<span style="color:red;">未修复</span>' : '<span style="color:green;">已修复</span>';
                    }
                }
                , {field: 'model', title: '规格/型号', width: 150, align: 'center'}
                , {field: 'department', title: '使用科室', width: 100, align: 'center'}
                , {field: 'applicant', title: '报修人', width: 100, align: 'center'}
                , {field: 'engineer', title: '维修工程师', width: 100, align: 'center'}
                , {field: 'part_num', title: '配件数', width: 150, align: 'center'}
                , {field: 'applicant_time', title: '报修时间', width: 150, align: 'center'}
                , {
                    field: 'operation',
                    title: '操作',
                    fixed: 'right',
                    style: 'background-color: #f9f9f9;',
                    width: 200,
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
            var flag = 1;
            if (layEvent === 'showRepair') {
                //显示维修单
                top.layer.open({
                    type: 2,
                    title: '【' + rows.assets + '】维修单 ' + rows.repnum + ' 详情',
                    area: ['920px', '100%'],
                    offset: 'r',
                    anim: 2,
                    scrollbar: false,
                    closeBtn: 1,
                    content: [url + '?repid=' + rows.repid + '&assid=' + rows.assid],
                    end: function () {
                        if (flag) {
                            table.reload('RepairSearchList', {
                                url: showAssets
                                , where: {assid: assid, action: 'getRepairRecord'}
                                ,page: {
                                    curr: 1 //重新从第 1 页开始
                                }
                            });
                        }
                    },
                    cancel: function () {
                        //如果是直接关闭窗口的，则不刷新表格
                        flag = 0;
                    }
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
            } else if(layEvent === 'cancelRepair'){
                //显示维修单--撤单
                top.layer.open({
                    type: 2,
                    title: '【'+rows.assets+'】维修单'+rows.repnum+'撤单申请',
                    area: ['920px', '100%'],
                    anim:2,
                    offset: 'r',//弹窗位置固定在右边
                    scrollbar:false,
                    closeBtn: 1,
                    content: [url+'?repid='+rows.repid+'&assid='+rows.assid],
                    end: function () {
                        if(flag){
                            table.reload('RepairSearchList', {
                                url: showAssets
                                , where: {assid: assid, action: 'getRepairRecord'}
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
            }
        });

        //设备质控记录 todo
        table.render({
            elem: '#quality',
            method: 'POST',
            size: 'sm',
            initSort: {
                field: 'qsid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                    field: 'qsid', title: '序号', align: 'center', width: 60, unresize: true, templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {field: 'plan_name', title: '质控计划名称', align: 'center', width: 160},
                {field: 'start_date', title: '质控日期', align: 'center', width: 110, sort: true},
                {field: 'username', title: '检测人', align: 'center', width: 100},
                {
                    field: 'is_start', title: '计划执行状态', align: 'center', width: 120,
                    templet: function (d) {
                        return d.is_start == 0 ? '<span style="color:#FFB800;">未启用</span>' : (d.is_start == 1 ? '<span style="color:#009688;">执行中</span>' : (d.is_start == 2 ? '已暂停' : (d.is_start == 3 ? '已完成' : '<span style="color: #01AAED;">已结束</span>')));
                    }
                },
                {
                    field: 'result', title: '检测结果', width: 100, align: 'center',
                    templet: function (d) {
                        return d.result == 1 ? '合格' : (d.result == 2 ? '<span style="color:#FF5722;">不合格</span>' : '');
                    }
                },
                {field: 'report', title: '质控报告', align: 'center'}
            ]]
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
        //设备计量记录
        table.render({
            elem: '#meteringLists',
            method: 'POST',
            size: 'sm',
            initSort: {
                field: 'mpid' //排序字段，对应 cols 设定的各字段名
                , type: 'asc' //排序方式  asc: 升序、desc: 降序、null: 默认排序
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
                    width: 60,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                },
                {
                    field: 'plan_num',
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    title: '计划编号',
                    width: 150,
                    align: 'center'
                },
                {field: 'mcategory', title: '计量分类', width: 125, align: 'center'},
                {field: 'cycle', title: '计量周期(月)', width: 120, align: 'center'},
                {field: 'respo_user', title: '计量负责人', width: 100, align: 'center'},
                {
                    field: 'test_way', title: '检定方式', width: 115, align: 'center', templet: function (d) {
                        return (d.test_way == 1) ? '<span style="color: #F581B1;">院内</span>' : '院外';
                    }
                },
                {
                    field: 'status', title: '检查状态', width: 100, align: 'center', templet: function (d) {
                        return (d.status == 1) ? '已执行' : '<span style="color: #F581B1;">未执行</span>';
                    }
                },
                {field: 'company', title: '检定机构', width: 100, align: 'center'},
                {field: 'money', title: '计量费用', width: 100, align: 'center'},
                {field: 'test_person', title: '检定人', width: 100, align: 'center'},
                {field: 'this_date', title: '检定日期', width: 110, align: 'center'},
                {
                    field: 'result', title: '检定结果', width: 100, align: 'center', templet: function (d) {
                        return (d.status == 1 && d.result == 1) ? '合格' : ((d.status == 1 && d.result == 0) ? '<span style="color: #F581B1;">不合格</span>' : '');
                    }
                },
                {field: 'operation', title: '检定报告', width: 100, align: 'center'}
            ]]
        });
        //不良事件记录
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
                    width: 60,
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
        //设备参保记录
        table.render({
            elem: '#doRenewal'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , size: 'sm' //小尺寸的表格
            , loading: true
            , url: showAssets //数据接口
            , where: {
                assid: assid
                , action: 'doRenewal'
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
                    width: 60,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'company',
                    style: 'background-color: #f9f9f9;',
                    fixed: 'left',
                    title: '维保公司名称',
                    width: 150,
                    align: 'center'
                }
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
                , {field: 'remark', title: '备注', width: 180, align: 'center'}
                , {
                    field: 'operation',
                    style: 'background-color: #f9f9f9;',
                    title: '操作',
                    fixed: 'right',
                    width: 120,
                    align: 'center'
                }]]
        });
        //设备审批记录
        table.render({
            elem: '#getAssetsedit'
            , size: 'sm' //小尺寸的表格
            , limits: [5, 10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , where: {
                assid: assid
                , sort: 'id'
                , order: 'desc'
                , action: 'geteditData'
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
                    width: 60,
                    fixed: 'left',
                    style: 'background-color: #f9f9f9;',
                    align: 'center',
                    type: 'space',
                    templet: function (d) {
                        return d.LAY_INDEX;
                    }
                }
                , {
                    field: 'operation_type',
                    title: '申请类型',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'desc',
                    title: '申请备注',
                    width: 350,
                    align: 'center'
                }
                , {
                    field: 'applicant_user',
                    title: '申请人',
                    width: 110,
                    align: 'center'
                }
                , {
                    field: 'applicant_time',
                    title: '申请时间',
                    width: 150,
                    align: 'center'
                }
                , {
                    field: 'approval',
                    title: '申请状态',
                    width: 100,
                    align: 'center'
                }
            ]]
        });
        //监听工具条
        table.on('tool(doRenewalData)', function (obj) {
            var rows = obj.data; //获得当前行数据
            var layEvent = obj.event;
            var action = $(this).attr('data-url');
            switch (layEvent) {
                case 'save_insurance':
                    var html = '<form class="layui-form layui-form-pane" action="">';
                    html += '<div class="layui-form-item">';
                    html += '<div class="layui-inline">';
                    html += '<label class="layui-form-label" style="width: 125px;">维保公司：</label>';
                    html += '<div class="layui-input-inline">';
                    html += '<input type="text" name="company_save" class="layui-input" readonly value="' + rows.company + '">';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="layui-inline">';
                    html += '<label class="layui-form-label" style="width: 125px;"><span class="rquireCoin"> * </span>联系人：</label>';
                    html += '<div class="layui-input-inline">';
                    html += '<input type="text" name="contacts_save" class="layui-input" autocomplete="off" value="' + rows.contacts + '">';
                    html += '</div>';
                    html += '</div>';
                    html += '<div class="layui-inline">';
                    html += '<label class="layui-form-label" style="width: 125px;"><span class="rquireCoin"> * </span>联系电话：</label>';
                    html += '<div class="layui-input-inline">';
                    html += '<input type="text" autocomplete="off" class="layui-input" name="telephone_save" lay-verify="number" value="' + rows.telephone + '">';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                    html += '</form>';
                    layer.open({
                        title: '维保公司【' + rows.company + ' 】编辑',
                        area: ['450px', '290px'],
                        content: html
                        , btn: ['确认', '取消']
                        , yes: function (index, layero) {
                            //按钮【按钮一】的回调
                            contacts = $('input[name="contacts_save"]').val();
                            telephone = $('input[name="telephone_save"]').val();
                            if ($.trim(contacts) == '') {
                                layer.msg("联系人不能为空！", {icon: 2}, 1000);
                                return false;
                            }
                            if (!checkTel(telephone)) {
                                layer.msg("请输入正确的号码！", {icon: 2}, 1000);
                                return false;
                            }
                            var params = {};
                            params.type = 'saveInsurance';
                            params.insurid = rows.insurid;
                            params.contacts = contacts;
                            params.telephone = telephone;
                            $.ajax({
                                timeout: 5000,
                                type: "POST",
                                url: 'doRenewal',
                                data: params,
                                dataType: "json",
                                success: function (data) {
                                    if (data) {
                                        if (data.status === 1) {
                                            table.reload('doRenewal', {
                                                url: showAssets
                                                , where: {assid: assid, action: 'doRenewal'}
                                            });
                                            layer.msg(data.msg, {icon: 1}, 1000);
                                        } else {
                                            layer.msg(data.msg, {icon: 2}, 1000);
                                        }
                                    } else {
                                        layer.msg("数据异常！", {icon: 2}, 1000);
                                    }
                                },
                                error: function () {
                                    layer.msg("网络访问失败", {icon: 2}, 1000);
                                }
                            });
                        }
                        , btn2: function (index, layero) {
                            //按钮【按钮二】的回调
                            //return false 开启该代码可禁止点击该按钮关闭
                        }
                        , cancel: function () {
                            //右上角关闭回调
                        }
                    });
                    break;
            }
        });
        //选择维保性质
        form.on('select(nature)', function (data) {
            var nature = data.value;
            var company = $('input[name="company"]');
            var contacts = $('input[name="contacts"]');
            var telephone = $('input[name="telephone"]');
            if (nature == INSURANCE_IS_GUARANTEE) {
                if (usecompany != null) {
                    company.val(usecompany.repair);
                    contacts.val(usecompany.repa_user);
                    telephone.val(usecompany.repa_tel);
                }
            } else {
                //选择第三方
                company.val('');
                contacts.val('');
                telephone.val('');
            }
            form.render();
        });
        //监听提交 上传维保
        form.on('submit(addDoRenewal)', function (data) {
            var params = data.field;
            var formerly = '', fileName = '';
            $('.file_data').each(function () {
                formerly += $(this).find('span').html() + '|';
                fileName += $(this).find('input[name="path"]').val() + '|';
            });
            params.formerly = formerly;
            params.fileName = fileName;
            params.assid = assid;
            params.company = $('select[name="company_id"]').find('option[value="' + params.company_id + '"]').html();
            params.type = 'addInsurance';
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: admin_name + '/Lookup/doRenewal.html',
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        table.reload('doRenewal', {
                            url: showAssets
                            , where: {assid: assid, action: 'doRenewal'}
                        });
                        $('.file_url').empty();
                        $('#addInsurance')[0].reset();
                        form.render();
                        layer.msg(data.msg, {icon: 1}, 1000);
                    } else {
                        layer.msg(data.msg, {icon: 2}, 1000);
                    }
                },
                error: function () {
                    layer.msg("网络访问失败", {icon: 2}, 1000);
                }
            });
            return false;
        });
        //维护资质信息
        $('.editFactory').on('click', function () {
            var flag = 1, url = $(this).attr('data-url'), olsid = $(this).attr('data-id');
            top.layer.open({
                type: 2,
                title: $(this).html(),
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                // area: ['99%', '98%'],
                area: ['790px', '100%'],
                closeBtn: 1,
                content: [url + '?olsid=' + olsid + '&otherPage=showAssets'],
                end: function () {
                    if (flag) {
                        location.reload()
                    }
                },
                cancel: function () {
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });

        //附属设备表格初始化
        table.init('subsidiaryList');
        table.on('tool(subsidiaryList)', function (obj) {
            var layEvent = obj.event;
            var rows = obj.data; //获得当前行数据
            switch (layEvent) {
                case 'showAssets'://显示附属设备详情
                    top.layer.open({
                        type: 2,
                        title: '附属设备：【' + rows.assets + '】详情信息',
                        area: ['1050px', '100%'],
                        offset: 'r',//弹窗位置固定在右边
                        anim: 2, //动画风格
                        scrollbar: false,
                        closeBtn: 1,
                        content: [$(this).attr('data-url')]
                    });
                    break;
            }
        });

        $('#reset').on('click', function () {
            //清空输入框数据
            $('input[name="increname"]').val('');
            $('input[name="brand"]').val('');
            $('input[name="model"]').val('');
            $('input[name="incre_num"]').val('');
            $('input[name="increprice"]').val('');
            $('input[name="remark"]').val('');
            $('select[name="incre_catid"]').val('');
            form.render('select');
            return false;
        });
        //监听上传设备图片按钮
        upload.render({
            elem: '#uploadAssetsPic'
            , url: admin_name + '/Lookup/addAssets'
            , data: {
                assid: assid,
                action: 'uploadPic'
            }
            , multiple: true
            , allDone: function (obj) { //当文件全部被提交后，才触发
                if (obj.total == obj.successful) {
                    layer.msg('上传设备图片成功', {icon: 1}, 1000);
                    $.ajax({
                        type: "POST",
                        url: admin_name + '/Lookup/addAssets',
                        data: {count: obj.successful, action: 'uploadPic', assid: assid},
                        dataType: "json"
                    });
                    setTimeout(function () {
                        location.reload();
                    }, 2000)
                }
            }
        });

        //删除文件
        $(document).on('click', '.delFile', function () {
            var target = $(this);
            var flag = true;
            if (target.attr('data-type') == 'technical') {
                $.getJSON(target.attr('data-url') + '?action=checkRepeatFile&id=' + target.attr('data-id'), function (result) {
                    if (result.status == 1) {
                        layer.confirm(result.msg, {
                            btn: ['一并删除', '删除', '取消'] //可以无限个按钮
                            , btn3: function (index, layero) {
                            }
                        }, function (index, layero) {
                            //按钮【一并删除】的回调
                            var url = target.attr('data-url');
                            url += '?action=delFile&id=' + target.attr('data-id') + '&dtype=' + target.attr('data-type') + '&allDelete=1';
                            $.ajax({
                                type: "GET",
                                url: url,
                                success: function (data) {
                                    layer.closeAll('loading');
                                    if (data.status == 1) {
                                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                            var id = target.parent().parent().parent().attr('id');
                                            target.parent().parent().remove();
                                            //重新编排序号
                                            var alltr = $('#' + id).find('tr');
                                            if (alltr.length == 0) {

                                            } else {
                                                var i = 1;
                                                $.each(alltr, function () {
                                                    $(this).find('td.xuhao').html(i);
                                                    i++;
                                                });
                                            }
                                        });
                                    } else {
                                        layer.msg(data.msg, {icon: 2});
                                    }
                                },
                                //调用出错执行的函数
                                error: function () {
                                    //请求出错处理
                                    layer.msg('服务器繁忙', {icon: 5});
                                }
                            });
                        }, function (index) {
                            //按钮【删除】的回调
                            var url = target.attr('data-url');
                            url += '?action=delFile&id=' + target.attr('data-id') + '&dtype=' + target.attr('data-type');
                            $.ajax({
                                type: "GET",
                                url: url,
                                //成功返回之后调用的函数
                                success: function (data) {
                                    layer.closeAll('loading');
                                    if (data.status == 1) {
                                        layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                            var id = target.parent().parent().parent().attr('id');
                                            target.parent().parent().remove();
                                            //重新编排序号
                                            var alltr = $('#' + id).find('tr');
                                            if (alltr.length == 0) {

                                            } else {
                                                var i = 1;
                                                $.each(alltr, function () {
                                                    $(this).find('td.xuhao').html(i);
                                                    i++;
                                                });
                                            }
                                        });
                                    } else {
                                        layer.msg(data.msg, {icon: 2});
                                    }
                                },
                                error: function () {
                                    layer.msg('服务器繁忙', {icon: 5});
                                }
                            });
                        });
                    }
                });
            } else {
                var url = target.attr('data-url');
                url += '?action=delFile&id=' + target.attr('data-id') + '&dtype=' + target.attr('data-type');
                layer.confirm('确定删除该文件吗？', {icon: 3, title: target.html()}, function (index) {
                    $.ajax({
                        type: "GET",
                        url: url,
                        //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".//返回数据的格式
                        beforeSend: function () {
                            layer.load(1);
                        },
                        //成功返回之后调用的函数
                        success: function (data) {
                            layer.closeAll('loading');
                            if (data.status == 1) {
                                if (target.attr('data-unplan') == 1){
                                    unplanTable.reload({
                                        where: {
                                            assid: assid
                                            , action: 'getUnplanData'
                                            , type: tabTableType
                                        }
                                        , page: {
                                            curr: 1 //重新从第 1 页开始
                                        }, done: function (res, curr, count) {
                                            var tableView = this.elem.next();
                                            var tableId = this.id;
                                            // 初始化laydate
                                            layui.each(tableView.find('td[data-field="archive_time"]'), function (index, tdElem) {
                                                var id = $(tdElem).parent().find('td[data-field="arc_id"]').attr('data-content');
                                                tdElem.onclick = function (event) {
                                                    layui.stope(event)
                                                };
                                                laydate.render({
                                                    elem: tdElem.children[0],
                                                    trigger: 'click'
                                                    ,done: function (value) {
                                                        var params = {};
                                                        params.arc_id = id;
                                                        params.action = 'updateUnplanFileDate';
                                                        params.archive_time = value;
                                                        $.ajax({
                                                            timeout: 5000,
                                                            type: "POST",
                                                            url: showAssets,
                                                            data: params,
                                                            dataType: "json",
                                                            success: function (data) {
                                                                // window.location.reload();
                                                            },
                                                            error: function () {
                                                                layer.msg("网络访问失败", {icon: 2}, 1000);
                                                            }
                                                        });
                                                    }
                                                })
                                            });
                                        }
                                    });
                                }else {
                                    layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                        var id = target.parent().parent().parent().attr('id');
                                        target.parent().parent().parent().remove();
                                        //重新编排序号
                                        var alltr = $('#' + id).find('tr');
                                        if (alltr.length == 0) {

                                        } else {
                                            var i = 1;
                                            $.each(alltr, function () {
                                                $(this).find('td.xuhao').html(i);
                                                i++;
                                            });
                                        }
                                    });
                                }

                            } else {
                                layer.msg(data.msg, {icon: 2});
                            }
                        },
                        //调用出错执行的函数
                        error: function () {
                            //请求出错处理
                            layer.msg('服务器繁忙', {icon: 5});
                        }
                    });
                    layer.close(index);
                });
            }
        });
        //如果是附属设备点击进来 选项卡切换到附属设备
        var changeTab = $("input[name='changeTab']").val();
        if (changeTab == 5) {
            element.tabChange('change', 5);
        }


        //借调
        table.render({
            elem: '#borrowRecordList'
            , size: 'sm'
            //,height: '600'
            , limits: [10, 20, 50, 100]
            , loading: true
            , limit: 10
            , url: showAssets //数据接口
            , where: {
                showlifeBorrow: 1,
                assid: assid,
                action: 'borrowRecordList',
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
            , cols: [[ //表头
                {
                    field: 'mpid', title: '序号', width: 60, fixed: 'left', align: 'center', type: 'space', rowspan: 2,
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

        //操作栏
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
                        content: [url + '&showLifeBorrow=1']
                    });
                    break;
            }
        });

        //修改维保性质
        var old_nature = -1;
        var salesman_name_all = [];
        var salesman_phone_all = [];
        form.on('select(nature)', function (data) {
            var nature = parseInt(data.value);
            var company_id = $('select[name="company_id"]');
            var contacts = $('input[name="contacts"]');
            var telephone = $('input[name="telephone"]');
            var html = '';
            if (data.value) {
                if (nature !== old_nature) {
                    if (nature === parseInt(INSURANCE_IS_GUARANTEE)) {
                        html = '<option value="' + usecompany.ols_facid + '">' + usecompany.factory + '</option>';
                        company_id.html(html);
                        company_id.val(usecompany.ols_facid);
                        contacts.val(usecompany.factory_user);
                        telephone.val(usecompany.factory_tel);
                        salesman_name_all[usecompany.ols_facid] = usecompany.factory_user;
                        salesman_phone_all[usecompany.ols_facid] = usecompany.factory_tel;
                        old_nature = nature;
                    } else {
                        //选择第三方
                        var params = {};
                        params.action = 'getRepairOffList';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: showAssets,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    var html = '<option value="">请选择第三方公司</option>';
                                    $.each(data.result, function (key, value) {
                                        salesman_name_all[value.olsid] = value.salesman_name;
                                        salesman_phone_all[value.olsid] = value.salesman_phone;
                                        html += '<option value="' + value.olsid + '">' + value.sup_name + '</option>';
                                    });
                                    company_id.html(html);
                                    contacts.val('');
                                    telephone.val('');
                                    old_nature = nature;
                                    form.render();
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                    }
                }
            } else {
                //选择空名称 复位填充数据
                html = ' <option value="-1">请先选择维保性质</option>';
                company_id.html(html);
                contacts.val('');
                telephone.val('');
                old_nature = nature;
            }
            form.render();
        });


        //选择维保公司
        form.on('select(company_id)', function (data) {
            var contacts = $('input[name="contacts"]');
            var telephone = $('input[name="telephone"]');
            if (data.value) {
                contacts.val(salesman_name_all[data.value]);
                telephone.val(salesman_phone_all[data.value]);
                form.render();
            } else {
                contacts.val('');
                telephone.val('');
            }
        });

        //点击补充厂家
        $(document).on('click', '#addSupplier', function () {
            layer.open({
                id: 'addOLSContractAddSupplier',
                type: 1,
                title: '添加厂商',
                area: ['750px', '450px'],
                offset: 'auto',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addSuppliersDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddSuppliers();
                    form.on('submit(addSuppliers)', function (data) {
                        var params = data.field;
                        params.action = 'addSuppliers';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: showAssets,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('input[name="sup_num"]').val(data.result.sup_num);
                                    if (parseInt(old_nature) === parseInt(INSURANCE_THIRD_PARTY)) {
                                        var arr = params.suppliers_type.split(',');
                                        var html = '';
                                        var set = false;
                                        if ($.inArray('4', arr) >= 0) {
                                            html = '<option value="' + data.result.olsid + '">' + data.result.sup_name + '</option>';
                                            $('select[name="company_id"]').append(html);
                                            form.render();
                                        }
                                    }
                                    layer.close(layer.index);
                                    layer.msg('补充成功', {icon: 1, time: 3000});
                                } else {
                                    layer.msg(data.msg, {icon: 2, time: 3000});
                                }
                            },
                            error: function () {
                                layer.msg('网络访问失败', {icon: 2, time: 3000});
                            }
                        });
                        return false;
                    });
                    form.render('select');
                }
            });
        });

        //复位添加厂商Div from
        function resetAddSuppliers() {
            $('input[name="sup_name"]').val('');
            formSelects.value('suppliers_type', []);
            $('input[name="salesman_name"]').val('');
            $('input[name="salesman_phone"]').val('');
            $('input[name="address"]').val('');
            $('select[name="provinces"]').val('');
            $('select[name="city"]').html('<option value="">请选择省份</option>');
            $('select[name="areas"]').html('<option value="">请选择城市</option>');
            form.render();
        }

        //已选省份、城市
        var old_provinces = 0;
        var old_city = 0;
        //选择省份
        form.on('select(provinces)', function (data) {
            var provinces = parseInt(data.value);
            if (data.value) {
                if (provinces !== old_provinces) {
                    var params = {};
                    params.action = 'getCity';
                    params.provinceid = provinces;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: showAssets,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if (data.result.length > 0) {
                                    var html = '<option value="">请选择城市</option>';
                                    $('select[name="areas"]').html(html);
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.cityid + '">' + value.city + '</option>';
                                    });
                                    $('select[name="city"]').html(html);
                                } else {
                                    $('select[name="city"]').html('<option>/</option>');
                                    $('select[name="areas"]').html('<option>/</option>');
                                }
                                form.render();
                                old_provinces = provinces;

                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html = '<option value="">请选择省份</option>';
                //选择空名称 复位填充数据
                $('select[name="city"]').html(html);
                $('select[name="areas"]').html(html);
                form.render();
                old_provinces = 0;
            }
        });
        //选择城市
        form.on('select(city)', function (data) {
            var city = parseInt(data.value);
            if (data.value) {
                if (city !== old_city) {
                    var params = {};
                    params.action = 'getAreas';
                    params.cityid = city;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: showAssets,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if (data.result.length > 0) {
                                    var html = '<option value="">请选择区/城镇</option>';
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.areaid + '">' + value.area + '</option>';
                                    });
                                    $('select[name="areas"]').html(html);
                                } else {
                                    $('select[name="areas"]').html('<option value="">/</option>');
                                }
                                form.render();
                                old_city = city;
                            } else {
                                layer.msg(data.msg, {icon: 2, time: 3000});
                            }
                        },
                        error: function () {
                            layer.msg('网络访问失败', {icon: 2, time: 3000});
                        }
                    });
                }
            } else {
                var html = '<option value="">请选择城市</option>';
                //选择空名称 复位填充数据
                $('select[name="areas"]').html(html);
                form.render();
                old_city = 0;
            }
        });

        $('#paizhao').on('mouseover', function () {
            $('.qrcode').show();
        });
        $('#paizhao').on('mouseout', function () {
            $('.qrcode').hide();
        });

    });
    var postDownLoadFile = function (options) {
        var config = $.extend(true, {method: 'POST'}, options);
        var $iframe = $('<iframe id="down-file-iframe" />');
        var $form = $('<form target="down-file-iframe" method="' + config.method + '" />');
        $form.attr('action', config.url);
        for (var key in config.data) {
            $form.append('<input type="hidden" name="' + key + '" value="' + config.data[key] + '" />');
        }
        $(document.body).append($iframe);
        $iframe.append($form);
        $form[0].submit();
        $iframe.remove();
    };

//删除上传文件
    function delDoRenewaFile(a) {
        $(a).parent().remove();
        layer.msg('删除成功', {icon: 1}, 1000);
    }

//下载
    $(document).on('click', '.downFile', function () {
        var params = {};
        params.path = $(this).data('path');
        params.filename = $(this).data('name');
        postDownLoadFile({
            url: admin_name + '/Tool/downFile',
            data: params,
            method: 'POST'
        });
        return false;
    });

//预览
    $(document).on('click', '.showFile', function () {
        var path = $(this).data('path');
        var name = $(this).data('name');
        var url = admin_name + '/Tool/showFile';
        top.layer.open({
            type: 2,
            title: name + '相关文件查看',
            scrollbar: false,
            offset: 'r',//弹窗位置固定在右边
            anim: 2, //动画风格
            area: ['70%', '100%'],
            closeBtn: 1,
            content: [url + '?path=' + path + '&filename=' + name]
        });
        return false;
    });

    exports('controller/assets/lookup/showAssets', {});
});
