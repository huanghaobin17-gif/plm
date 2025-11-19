layui.define(function(exports){
    layui.use(['form', 'layedit', 'laydate', 'upload', 'suggest', 'formSelects','tipsType','asyncFalseUpload'], function() {
        var form = layui.form, $ = layui.jquery,laydate = layui.laydate,suggest = layui.suggest, upload = layui.upload, tipsType = layui.tipsType,formSelects = layui.formSelects,asyncFalseUpload = layui.asyncFalseUpload;
        var  old_departid = 0;

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
        var uploadHandle = false;
        //上传设备档案按钮
        //记录上传的文件
        var need_upload_files = [];
        var fileListView2 = $('#arcfileList')
            , uploadListIns2 = asyncFalseUpload.render({
            elem: '#multifile2'
            , url: 'addAssets'
            , data: {
                "assid": $('input[name="assid"]').val(),
                "type": 'archives',
                "action": "uploadFile"
            }
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
                        , '<td class="td-align-center">' + xuhao + '</td>'
                        , '<td class="td-align-center">' + file.name + '</td>'
                        , '<td class="td-align-center"><span class="is_upload">等待上传</span></td>'
                        , '<td class="td-align-center" style="padding: 0;"><input type="text" name="archives_time[]" value="" readonly placeholder="点击选择日期" class="layui-input archives-time" style="cursor: pointer;border: none;height: 49px;"></td>'
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
                            , isInitValue: false
                            , done: function (value, date, endDate) {

                            }
                        });
                    });
                    tr.find('.expire-time').each(function () {
                        laydate.render({
                            elem: this
                            , trigger: 'click'
                            , isInitValue: false
                            , done: function (value, date, endDate) {

                            }
                        });
                    });
                });
            }
            , done: function (res, index, upload) {
                if (res.status == 1) { //上传成功
                    var tr = fileListView2.find('tr#upload-' + index)
                        , tds = tr.children();
                    tds.eq(2).html('<span style="color: #5FB878;" class="is_upload">上传成功</span>');
                    need_upload_files.remove(index);
                    return delete this.files[index]; //删除文件队列已经上传成功的文件
                }
                this.error(index, upload);
            },
            allDone: function (obj) { //当文件全部被提交后，才触发
                uploadHandle = true;
            }
            , error: function (index, upload) {
                var tr = fileListView2.find('tr#upload-' + index)
                    , tds = tr.children();
                tds.eq(2).html('<span style="color: #FF5722;" class="is_upload">上传失败</span>');
                //tds.eq(3).find('.file-reload').removeClass('layui-hide'); //显示重传
            }
        });
        //监听提交
        form.on('submit(editAssets)', function (data) {
            var params = data.field;
            if (old_is_subsidiary === parseInt(YES_STATUS)) {
                var main_assid = $('input[name="main_assid"]');
                if (!main_assid.data('id')) {
                    layer.msg('请选择当前附属设备所属的设备', {icon: 2, time: 3000});
                    return false;
                } else {
                    params.main_assid = main_assid.data('id');
                    params.main_assets = main_assid.val();
                    params.is_subsidiary = parseInt(YES_STATUS);
                }
            }
            if(!params.file){
                //没文件上传时候
                uploadHandle = true;
            }
            if (params.ols_facid) {
                params.factory = $('select[name="ols_facid"]').find('option[value="' + params.ols_facid + '"]').html();
            }
            if (params.ols_supid) {
                params.supplier = $('select[name="ols_supid"]').find('option[value="' + params.ols_supid + '"]').html();
            }
            if (params.ols_repid) {
                params.repair = $('select[name="ols_repid"]').find('option[value="' + params.ols_repid + '"]').html();
            }
            if (params.assorignum == "") {
                params.assorignum = '/';
            }
            if (params.serialnum == "") {
                params.serialnum = '/';
            }
            if (params.assorignum_spare == "") {
                params.assorignum_spare = '/';
            }
            if (params.factorynum == "") {
                params.factorynum = '/';
            }
            if (params.invoicenum == "") {
                params.invoicenum = '/';
            }
            //计算建议项的比例
            var num = 20;
            var count = 20;
            var suggest = "";
            if (params.model == "") {
                num--;
                suggest += "规格/型号,";
            }
            if (params.serialnum == "/") {
                num--;
                suggest += "产品序列号,";
            }
            if (params.assorignum == "/") {
                num--;
                suggest += "设备原编码,";
            }
            if (params.buy_price == "") {
                num--;
                suggest += "设备原值,";
            }
            if (params.guarantee_date == "") {
                num--;
                suggest += "保修到期日期,";
            }
            if (params.ols_facid == "") {
                num--;
                suggest += "生产厂商,";
            }
            if (params.ols_supid == "") {
                num--;
                suggest += "供应商,";
            }
            if (params.ols_repid == "") {
                num--;
                suggest += "维修商,";
            }
            if (params.factorydate == "") {
                num--;
                suggest += "出厂日期,";
            }
            if (params.patrol_xc_cycle == "") {
                num--;
                suggest += "巡查周期,";
            }
            if (params.patrol_pm_cycle == "") {
                num--;
                suggest += "保养周期,";
            }
            if (params.quality_cycle == "") {
                num--;
                suggest += "质控周期,";
            }
            if (params.metering_cycle == "") {
                num--;
                suggest += "计量周期,";
            }
            if (params.financeid == "") {
                num--;
                suggest += "财务分类,";
            }
            if (params.assfromid == "") {
                num--;
                suggest += "设备来源,";
            }
            if (params.capitalfrom == "") {
                num--;
                suggest += "资金来源,";
            }
            if (params.storage_date == "") {
                num--;
                suggest += "入库日期,";
            }
            if (params.opendate == "") {
                num--;
                suggest += "启用日期,";
            }
            if (params.registration == "") {
                num--;
                suggest += "注册证编号,";
            }
            if (params.file_number == "") {
                num--;
                suggest += "档案盒编号,";
            }
            suggest = suggest.substring(0, suggest.lastIndexOf(','));
            num = parseInt(num / count * 100);
            if (num != 100) {
                var title = '信息完善程度  <div class="layui-progress layui-progress-big" lay-showPercent="yes"><div class="layui-progress-bar layui-bg-green" style="width:' + num + '%"><font color="#fff">' + num + '%</font></div></div></br>建议继续完善：</br>' + suggest;
                layer.confirm(title, {area: ['600px'], skin: 'demo-class', btn: ['确定提交', '继续完善']},
                    function (index, layer) {
                        $("#testUpload").click();
                        if(need_upload_files.length == 0){
                            submit($, params, editAssetsUrl);
                        }else{
                            var timer = setInterval(function () {
                                if (uploadHandle) {
                                    clearInterval(timer);
                                    submit($, params, editAssetsUrl);
                                }
                            }, 500);
                        }
                    }
                );
                return false;
            } else {
                $("#testUpload").click();
                var timer = setInterval(function () {
                    if (uploadHandle) {
                        clearInterval(timer);
                        submit($, params, editAssetsUrl);
                    }
                }, 500);
                return false;
            }
        });

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
                                layer.msg(data.msg, {icon: 1, time: 2000}, function () {
                                    var id = target.parent().parent().parent().parent().attr('id');
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
                return false;
            }
        });
        //初始化搜索建议插件
        suggest.search();
        tipsType.choose();
        form.render();
        //初始化数据
        if (old_is_subsidiary === parseInt(YES_STATUS)) {
            $('.mainAssets').css('display', 'none');
            $('.is_subsidiarySpan').hide();
            $('.subsidiaryAssets').css('display', 'inline-block');
            initsuggestMainAssets();

        } else {
            $('.is_subsidiarySpan').show();
        }


        //初始化时间
        lay('.formatDate').each(function () {
            laydate.render(dateConfig(this));
        });
        laydate.render({
            elem: '#date4', //指定元素
            calendar: true
            , max: now
            ,done: function(value){
              depreciation(value);
            }
        });

        // 数据验证
        form.verify({
            //assets: function(value){
            //    value = $.trim(value);
            //    if (value){
            //        if(/(^\_)|(\__)|(\_+$)/.test(value)){
            //            return '所填项首尾不能出现下划线\'_\'';
            //        }
            //        if(/^\d+\d+\d$/.test(value)){
            //            return '所填项不能全为数字';
            //        }
            //    }else{
            //        return '请输入设备名称！';
            //    }
            //},
            //dic_assets_sel: function (value) {
            //    value = $.trim(value);
            //    if (!value) {
            //        return '请选择通用名称';
            //    }
            //},
            //model: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请输入设备规格 / 型号！';
            //    }
            //},
            //serialnum: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请填写产品序列号！';
            //    }
            //},
            //catid: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请选择设备分类！';
            //    }
            //},
            //buy_price: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请输入设备原值！';
            //    }else{
            //        var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
            //        if (!reg.test(value)) {
            //            return "设备原值格式不正确！";
            //        }
            //    }
            //},
            //expected_life: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请输入预计使用年限！';
            //    }
            //},
            //residual_value: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请输入残净值率！';
            //    }
            //},
            //guarantee_date: function (value) {
            //    if(!value){
            //        return '请选择保修到期日期！';
            //    }
            //},
            //assetsrespon: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请输入资产负责人！';
            //    }
            //},
            //financeid: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请选择财务分类！';
            //    }
            //},
            //assfromid: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请选择设备来源！';
            //    }
            //},
            //capitalfrom: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请选择资金来源！';
            //    }
            //},
            //storage_date: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请选择入库日期！';
            //    }
            //},
            //opendate: function (value) {
            //    value = $.trim(value);
            //    if(!value){
            //        return '请选择启用日期！';
            //    }
            //}
            assets: function (value) {
                value = $.trim(value);
                if (value) {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '所填项首尾不能出现下划线\'_\'';
                    }
                    if (/^\d+\d+\d$/.test(value)) {
                        return '所填项不能全为数字';
                    }
                } else {
                    return '请输入设备名称！';
                }
            },
            dic_assets_sel: function (value, dom) {
                value = $.trim(value);
                if (!value) {
                    $(dom).next().find('input').focus();
                    return '请选择设备名称';
                }
            },
            catid: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择设备分类！';
                }
            },
            buy_price: function (value) {
                value = $.trim(value);
                if (value) {
                    var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                    if (!reg.test(value)) {
                        return "设备原值格式不正确！";
                    }
                }
            },
            impairment_provision: function (value) {
                value = $.trim(value);
                if (value) {
                    var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                    if (!reg.test(value)) {
                        return "减值准备格式不正确！";
                    }
                }
            },
            expected_life: function (value) {
                value = $.trim(value);
                if (value && value != 0) {
                    var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                    if (!reg.test(value)) {
                        return "请输入数字！";
                    }
                }
            },
            residual_value: function (value) {
                value = $.trim(value);
                if (value && value != 0) {
                    var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                    if (!reg.test(value)) {
                        return value;
                        return "请输入数字！" + value;
                    }
                }
            },
            depreciable_lives: function (value) {
                value = $.trim(value);
                if (value && value != 0) {
                    var reg = /^(([1-9][0-9]*)|(([0]\.\d{1,2}|[1-9][0-9]*\.\d{1,2})))$/;
                    if (!reg.test(value)) {
                        return "请输入数字！";
                    }
                }
            }
        });
        form.on('select(depreciation_method)', function(data){
            depreciation();
        });
        $("#buy_price").blur(function () {
            depreciation();
        });
        $("#residual_value").blur(function () {
            depreciation();
        });
        $("#depreciable_lives").blur(function () {
            depreciation();
        });
        $("#impairment_provision").blur(function () {
            depreciation();
        });

        // 折旧算法 
        function depreciation(value='')
        {
            var depreciation_method = $("select[name='depreciation_method']").val();
            var buy_price = $('input[name="buy_price"]').val();
            var depreciable_lives = $('input[name="depreciable_lives"]').val();
            if (!depreciable_lives) {
              // 未设置折旧年限
              return false;
            } else{
                depreciable_lives = Number(depreciable_lives);
            }
            if (!buy_price) {
              // 未设置设备原值或设备原值为0
              return false;
            }
            if (!depreciation_method) {
                // 未选中折旧方式
              return false;
            }
            var residual_value = $('input[name="residual_value"]').val();
            if (!residual_value) {
              // 未设置残净值率则默认为0
              residual_value = 0;
            }
            var storage_date = $('input[name="storage_date"]').val();
            if (value) {
                var storage_date =value;
            }
            if (!storage_date) {
              // 未设置入库日期
              return false;
            } else{
              var storage_time = Date.parse(storage_date);
              var storage_date = new Date(storage_time);
            }
            var new_date = new Date();
            switch(depreciation_method)
            {
              case '1':
              case 1:
                  var depreciable_quota_m = buy_price*(100-residual_value)/100/depreciable_lives/12;
                  var depreciable_quota_y = depreciable_quota_m*12;
                  var y = new_date.getFullYear()-storage_date.getFullYear();
                  var m = new_date.getMonth() - storage_date.getMonth();
                  var depreciable_quota_count = depreciable_quota_m*(y*12+m);
                  var net_asset_value = buy_price - depreciable_quota_count;
                  depreciable_quota_m = depreciable_quota_m.toFixed(2);
                  break;
              case '3':
              case 3:
                  var depreciable_quota_m = '';
                  var depreciable_quota_y = '';
                  var depreciable_quota = 0;//双倍余额递减法折旧额
                  var depreciable_quota_count = 0;//已提折旧额
                  var y = new_date.getFullYear()-storage_date.getFullYear()-1;
                  var m = new_date.getMonth() - storage_date.getMonth();
                  if (m>0) {
                      y = y+1;
                  }
                  m = Math.abs(m);
                  for (var i = 0; i < depreciable_lives-2; i++) {
                      if (y>i) {
                          depreciable_quota_y = (buy_price-depreciable_quota)*2/depreciable_lives;
                          depreciable_quota_m = depreciable_quota_y/12;
                          depreciable_quota_count = depreciable_quota = depreciable_quota+depreciable_quota_y;
                      } else{
                          break;
                      }
                  }
                  for (var j = i;depreciable_lives-3< j&& j < depreciable_lives; j++) {
                      if (i!=y) {
                          depreciable_quota_y = (buy_price-depreciable_quota-buy_price*residual_value/100)/2;
                          depreciable_quota_m = depreciable_quota_y/12;
                          depreciable_quota_count = depreciable_quota_count+depreciable_quota_y;
                      } else{
                          break;
                      }
                  }
                  depreciable_quota_count = depreciable_quota_count+depreciable_quota_m*m;
                  if (depreciable_quota_y!='') {
                      var depreciable_quota_y = depreciable_quota_y.toFixed(2);
                  }
                  if (depreciable_quota_m!='') {
                      var depreciable_quota_m = depreciable_quota_m.toFixed(2);
                  }
                  var net_asset_value = buy_price - depreciable_quota_count;
                  break;
              case '4':
              case 4:
                  var depreciable_quota_m = '';
                  var depreciable_quota_y = '';
                  var depreciable_quota = 0;//折旧额
                  var y = new_date.getFullYear()-storage_date.getFullYear();
                  var m = new_date.getMonth() - storage_date.getMonth();
                  if (m>0) {
                      y = y+1;
                  }
                  for (var i = 0; i < y; i++) {
                      depreciable_quota_y = (buy_price-buy_price*residual_value/100)*(depreciable_lives-i)/(depreciable_lives*(1+depreciable_lives)/2);
                      depreciable_quota_m = depreciable_quota_y/12;
                      depreciable_quota = depreciable_quota+depreciable_quota_y;
                  }
                  var depreciable_quota_count = depreciable_quota = depreciable_quota+depreciable_quota_m*m;
                  if (depreciable_quota_y!='') {
                      var depreciable_quota_y = depreciable_quota_y.toFixed(2);
                  }
                  if (depreciable_quota_m!='') {
                      var depreciable_quota_m = depreciable_quota_m.toFixed(2);
                  }
                  var net_asset_value = (buy_price-buy_price*residual_value/100) - depreciable_quota;
                  break;    
            }
            if (y>depreciable_lives) {
                //超过折旧率
                depreciable_quota_y = 0;
                depreciable_quota_m = 0;
                net_asset_value = buy_price*residual_value/100;
            }
            $('input[name="depreciable_quota_m"]').val(depreciable_quota_m);
            $('input[name="depreciable_quota_y"]').val(depreciable_quota_y);
            if (net_asset_value<(buy_price*residual_value/100)) {
                net_asset_value = buy_price*residual_value/100;
            }
            $('input[name="depreciable_quota_count"]').val(depreciable_quota_count.toFixed(2));
            $('input[name="net_asset_value"]').val(net_asset_value.toFixed(2));
            var impairment_provision = $('input[name="impairment_provision"]').val();
            if (impairment_provision) {
              var net_assets = net_asset_value - impairment_provision;
              $('input[name="net_assets"]').val(net_assets.toFixed(2));
            }
        }
        $("#assorignum").blur(function () {
            //判断原编码是否重复
            var value = this.value;
            var params = {};
            params.action = 'getjudgement';
            params.field = "assorignum";
            params.value = value;
            params.assid = $('input[name="assid"]').val();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: editAssetsUrl,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        console.log(data);
                    } else {

                        layer.msg(data.msg, {icon: 2, time: 3000});
                    }
                },
                error: function () {
                    layer.msg('网络访问失败', {icon: 2, time: 3000});
                }
            });
        });
        $("#assorignum_spare").blur(function () {
            //判断原编码(备注)是否重复
            var value = this.value;
            var params = {};
            params.action = 'getjudgement';
            params.field = "assorignum_spare";
            params.value = value;
            params.assid = $('input[name="assid"]').val();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: editAssetsUrl,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        console.log(data);
                    } else {

                        layer.msg(data.msg, {icon: 2, time: 3000});
                    }
                },
                error: function () {
                    layer.msg('网络访问失败', {icon: 2, time: 3000});
                }
            });
        });

        function save($, params, editAssetsUrl) {
            submit($, params, editAssetsUrl)
        }

        //单选 是否附属设备
        form.on('radio(is_subsidiary)', function (data) {

            var is_subsidiary = data.value;
            var mainAssets = $('.mainAssets');
            var subsidiaryAssets = $('.subsidiaryAssets');
            if (is_subsidiary === YES_STATUS) {
                $('.is_subsidiarySpan').hide();
                old_is_subsidiary = parseInt(YES_STATUS);
                mainAssets.css('display', 'none');
                subsidiaryAssets.css('display', 'inline-block');
                // //判断是否已获取的是对应医院的主设备列表
                var is_acquired = $('input[name="is_acquired"]').val();
                if (parseInt(is_acquired) === parseInt(NO_STATUS)) {
                    initsuggestMainAssets();
                }
            } else {
                $('.is_subsidiarySpan').show();
                old_is_subsidiary = parseInt(NO_STATUS);
                mainAssets.css('display', 'inline-block');
                subsidiaryAssets.css('display', 'none');
            }
        });
        //选择科室 
        form.on('select(departid)', function (data) {

            var departid = parseInt(data.value);
            if (data.value) {
                if (departid !== old_departid) {
                    var params = {};
                    params.action = 'getdepartDetail';
                    params.departid = departid;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: editAssetsUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                // $("input[name='managedepart']").val(data.result.department);
                                if (data.result.address) {
                                    $("input[name='address']").val(data.result.address);
                                }
                                if (data.result.assetsrespon) {
                                    $("input[name='assetsrespon']").val(data.result.assetsrespon);
                                }
                                old_departid = departid;
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
                //选择空名称 复位填充数据
                // $("input[name='managedepart']").val('');
                $("input[name='address']").val('');
                $("input[name='assetsrespon']").val('');
            }
        });
        //选择字典
        form.on('select(dic_assets_sel)', function (data) {
            var assets = data.value;
            if (data.value) {
                if (assets !== old_dic_assets) {
                    if (assets === this_assets) {
                        //选择本身  恢复已选的选项
                        $("select[name='catid']").val(this_catid);
                        $('input[name="unit"]').val(this_unit);
                        $("input[name='is_firstaid']").removeAttr("checked");
                        $("input[name='is_special']").removeAttr("checked");
                        $("input[name='is_metering']").removeAttr("checked");
                        $("input[name='is_qualityAssets']").removeAttr("checked");
                        $("input[name='is_benefit']").removeAttr("checked");
                        $("input[name='is_lifesupport']").removeAttr("checked");
                        var assets_category = this_assets_category;
                        var assets_category_arr = assets_category.split(',');
                        $.each(assets_category_arr, function (index, item) {
                            $("input[name='" + item + "']").prop("checked", true);
                        });
                        form.render('select');
                        form.render('checkbox');
                        old_dic_assets = assets;
                    } else {
                        var params = {};
                        params.action = 'getDicAssetsDetail';
                        params.assets = assets;
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: editAssetsUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $("select[name='catid']").val(data.result.catid);
                                    $('input[name="unit"]').val(data.result.unit);
                                    $("input[name='is_firstaid']").removeAttr("checked");
                                    $("input[name='is_special']").removeAttr("checked");
                                    $("input[name='is_metering']").removeAttr("checked");
                                    $("input[name='is_qualityAssets']").removeAttr("checked");
                                    $("input[name='is_benefit']").removeAttr("checked");
                                    $("input[name='is_lifesupport']").removeAttr("checked");
                                    var assets_category = data.result.assets_category;
                                    var assets_category_arr = assets_category.split(',');
                                    $.each(assets_category_arr, function (index, item) {
                                        $("input[name='" + item + "']").prop("checked", true);
                                    });
                                    form.render('select');
                                    form.render('checkbox');
                                    old_dic_assets = assets;
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
                $('input[name="assets"]').val('');
                $("select[name='catid']").val('');
                $("input[name='is_firstaid']").removeAttr("checked");
                $("input[name='is_special']").removeAttr("checked");
                $("input[name='is_metering']").removeAttr("checked");
                $("input[name='is_qualityAssets']").removeAttr("checked");
                $("input[name='is_benefit']").removeAttr("checked");
                $("input[name='is_lifesupport']").removeAttr("checked");
                form.render('select');
                form.render('checkbox');
            }
        });


        function initsuggestMainAssets() {
            var main_assets_div = $('#main_assets_div');
            var html = '<div class="input-group">';
            html += '<input type="text" class="form-control bsSuggest" id="addMainAssetsName" placeholder="请选择所属设备" data-id="' + main_assid + '" name="main_assid" value="' + main_assets + '" alt="' + main_assets + '">';
            html += '<div class="input-group-btn">';
            html += '<ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">';
            html += '</ul>';
            html += '</div></div>';
            main_assets_div.html('');
            main_assets_div.html(html);
            $("#addMainAssetsName").bsSuggest({
                url: admin_name + '/Public/getAllAssetsSearch?not_get_assid=' + $('input[name="assid"]').val(),
                effectiveFieldsAlias: {
                    assid: '设备id',
                    assnum: "设备编号",
                    assets: "设备名称",
                    pinyin: "拼音",
                    assorignum: "设备原编号"
                },
                listStyle: {
                    "max-height": "375px", "max-width": "550px",
                    "overflow": "auto", "width": "550px", "text-align": "center"
                },
                ignorecase: false,
                showHeader: true,
                showBtn: false,     //不显示下拉按钮
                delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                idField: "assid",
                keyField: "assets",
                clearable: false
            }).on('onDataRequestSuccess', function (e, result) {
                if (result.value.length === 0) {
                    //未设置科室
                    layer.msg('未添加设备，请先补充设备', {icon: 2, time: 3000});
                } else {
                    if (main_assid > 0) {
                        var selected_obj = {};
                        $.each(result.value, function (key, value) {
                            if (parseInt(value.assid) === main_assid) {
                                selected_obj = value;
                            }
                        });
                        var main_assid_obj = $('input[name="main_assid"]');
                        main_assid_obj.data('id', selected_obj.assid);
                        main_assid_obj.val(selected_obj.assets);
                        main_assid_obj.attr('alt', selected_obj.assets);
                    }
                    $('input[name="is_acquired"]').val(parseInt(YES_STATUS));
                }
            }).on('onSetSelectValue', function (e, keyword, data) {
                var main_assid_obj = $('input[name="main_assid"]');
                main_assid_obj.data('id', parseInt(data.assid));
                main_assid_obj.val(data.assets);
            }).on('onUnsetSelectValue', function () {
                layer.msg('请点击选择所属设备', {icon: 2, time: 3000});
            });
        }


        //添加明细-补充设备
        $(document).on('click', '#addAssetsDic', function () {
            layer.open({
                id: 'addOLSContractAddAssetsDicDiv',
                type: 1,
                title: '新增设备字典',
                area: ['450px', '500px'],
                offset: '20px',
                shade: [0.8, '#393D49'],
                shadeClose: true,
                anim: 5,
                resize: false,
                scrollbar: false,
                isOutAnim: true,
                closeBtn: 1,
                content: $('#addAssetsDicDiv'),
                success: function (layero, index) {
                    //复位操作
                    resetAddDic();
                    form.on('submit(addDic)', function (data) {
                        var params = {};
                        params.assets = data.field.dic_assets;
                        params.catid = data.field.dic_catid;
                        params.assets_category = data.field.dic_assets_category;
                        params.dic_category = data.field.dic_category;
                        params.unit = data.field.dic_unit;
                        params.action = 'addDic';
                        $.ajax({
                            timeout: 5000,
                            type: "POST",
                            url: addAssetsUrl,
                            data: params,
                            dataType: "json",
                            success: function (data) {
                                if (data.status === 1) {
                                    $('select[name="dic_assets_sel"]').append('<option value="' + params.assets + '">' + params.assets + '</option>');
                                    form.render();
                                    layer.close(layer.index);
                                    layer.msg(data.msg, {icon: 1, time: 3000});
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

        //复位添加字典Div from
        function resetAddDic() {
            $('select[name="dic_catid"]').val('');
            $('input[name="assets"]').val('');
            formSelects.value('dic_assets_category', []);
            $('input[name="dic_category"]').val('');
            $('input[name="unit"]').val('');
            getDicCategory();
            form.render();
        }

        /*
         /选择品牌名称
         */
        $("#dic_brand").bsSuggest(
            returnDicBrand()
        );
        $('#addBrandDic').on('click', function () {
            var url = $(this).attr('data-url');
            var flag = 1;
            top.layer.open({
                id: 'addDictorys',
                type: 2,
                title: '添加品牌字典',
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar: false,
                area: ['680px', '100%'],
                closeBtn: 1,
                content: url + '?otherPage=addAssetsPage',
                end: function () {
                    if (flag) {
                        var newBrand = localStorage.getItem('addAssetsNewBrand'),
                            brandObj = $("#brand"),
                            dataIndex = brandObj.find(".dropdown-menu .table tbody tr:last-child").attr('data-index'),
                            html = '<tr data-index="' + (dataIndex + 1) + '" data-id="' + newBrand + '" data-key="' + newBrand + '"><td data-name="brand_name">' + newBrand + '</td></tr>';
                        brandObj.find(".dropdown-menu .table tbody").append(html);
                        localStorage.removeItem('addAssetsNewBrand');
                    }
                },
                cancel: function () {
                    //如果是直接关闭窗口的，则不刷新表格
                    flag = 0;
                }
            });
            return false;
        });

        //点击生产 供应 维修商隔壁的添加按钮
        $(".addSuppliers").on('click', function () {
            var typeRefresh = $(this).siblings('span').attr('class'), refresh = '';
            switch (typeRefresh) {
                case 'factoryClick':
                    refresh = 'factory';
                    refreshResetName = '生产厂商';
                    break;
                case 'supplierClick':
                    refresh = 'supplier';
                    refreshResetName = '供应商';
                    break;
                case 'repairClick':
                    refresh = 'repair';
                    refreshResetName = '维修商';
                    break;
            }
            layer.open({
                type: 2,
                title: '添加' + refreshResetName,
                scrollbar: false,
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                area: ['790px', '100%'],
                closeBtn: 1,
                content: [admin_name + '/OfflineSuppliers/addOfflineSupplier?otherPage=' + refresh],
                end: function () {
                    $.getJSON(admin_name + "/Lookup/addAssets?action=getNewSuppliers&type=" + refresh, function (result) {
                        var html = '<option value="">请选择' + refreshResetName + '</option>';
                        $.each(result, function (k, v) {
                            html += '<option value="' + v.sup_name + '">' + v.sup_name + '</option>';
                        });
                        switch (refresh) {
                            case 'factory':
                                $("select[name='ols_facid']").html(html);
                                form.render('select');
                                break;
                            case 'supplier':
                                $("select[name='ols_supid']").html(html);
                                form.render('select');
                                break;
                            case 'repair':
                                $("select[name='ols_repid']").html(html);
                                form.render('select');
                                break;
                        }
                    });
                }
            });
        });

        function getDicCategory() {
            var dic_category_div = $('#dic_category_div');
            var html = '<div class="input-group">';
            html += '<input type="text" class="form-control bsSuggest" name="dic_category" id="dic_category_add" placeholder="选择或填写新类别" />';
            html += '<div class="input-group-btn">';
            html += '<ul class="dropdown-menu dropdown-menu-right ulwidth" role="menu">';
            html += '</ul>';
            html += '</div>';
            dic_category_div.html('');
            dic_category_div.html(html);
            $("#dic_category_add").bsSuggest({
                url: admin_name + "/Public/getAllAssetsDicCategory",
                effectiveFields: ["dic_category"],
                searchFields: ["dic_category"],
                effectiveFieldsAlias: {dic_category: "字典类别"},
                ignorecase: false,
                showHeader: true,
                listStyle: {
                    "max-height": "400px", "max-width": "300px",
                    "overflow": "auto", "width": "200px", "text-align": "center"
                },
                showBtn: false,     //不显示下拉按钮
                delayUntilKeyup: true, //获取数据的方式为 firstByUrl 时，延迟到有输入/获取到焦点时才请求数据
                idField: "dic_category",
                keyField: "dic_category",
                clearable: false
            });
        }

        //档案编号
        $("#box_num").bsSuggest(
            returnBoxNum()
        );

    });
    exports('controller/assets/lookup/editassets', {});
});


