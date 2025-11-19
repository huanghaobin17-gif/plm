layui.define(function(exports){
    layui.use(['layer', 'form', 'laydate', 'upload', 'tipsType', 'formSelects','element'], function () {
        var layer = layui.layer,
            formSelects = layui.formSelects,
            form = layui.form,
            laydate = layui.laydate,
            upload = layui.upload,
            element = layui.element,
            tipsType = layui.tipsType,
            $ = layui.jquery;
        //先更新页面部分需要提前渲染的控件
        form.render();
        tipsType.choose();

        formSelects.render('suppliers_type', selectParams(1));
        formSelects.btns('suppliers_type', selectParams(2));

        //日期初始化
        laydate.render(dateConfig('#BusinessLicenseStartDate'));
        laydate.render(dateConfig('#BusinessLicenseEndDate'));

        laydate.render(dateConfig('#ManagementStartDate'));
        laydate.render(dateConfig('#ManagementEndDate'));

        laydate.render(dateConfig('#KeepOnRecordStartDate'));
        laydate.render(dateConfig('#KeepOnRecordEndDate'));

        laydate.render(dateConfig('#GenerateLicenseStartDate'));
        laydate.render(dateConfig('#GenerateLicenseEndDate'));

        laydate.render(dateConfig('#ProductionRecordStartDate'));
        laydate.render(dateConfig('#ProductionRecordEndDate'));

        laydate.render(dateConfig('#registrationStartDate'));
        laydate.render(dateConfig('#registrationEndDate'));

        laydate.render(dateConfig('#othersStartDate'));
        laydate.render(dateConfig('#othersEndDate'));

        form.verify({
            tel: function (value) {
                if (value !== '') {
                    if (/(^\_)|(\__)|(\_+$)/.test(value)) {
                        return '号码首尾不能出现下划线\'_\'';
                    }
                    if (!checkTel(value)) {
                        return '请正确填写号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                    }
                }
            },
            checkEmail: function (value) {
                if (value !== '') {
                    if (!/^[\w\-\.]+@[\w\-\.]+(\.\w+)+$/.test(value)) {
                        return '请输入正确的邮箱';
                    }
                }
            }
        });


        form.on('submit(cesi)', function (data) {
            var params = data.field;


            console.log(params);
            return false;

        });


        //监听提交
        form.on('submit(submit)', function (data) {
            var params = data.field;
            var fileTr = $('.fileTable').find('tbody').find('tr');
            params.path = '';
            params.fileType = '';
            params.ext = '';
            params.name = '';
            params.startDate = '';
            params.endDate = '';
            params.fileid='';
            var errorPath='';
            var error=false;
            $.each(fileTr, function (key, value) {
                var pathval = $(value).find('input[name="path"]').val();
                if (pathval) {
                    params.path += pathval + '|';
                    params.fileType += $(value).find('input[name="fileType"]').val() + '|';
                    params.fileid += $(value).find('input[name="fileid"]').val() + '|';
                    params.ext += $(value).find('input[name="ext"]').val() + '|';
                    params.name += $(value).find('input[name="name"]').val() + '|';
                    if($(value).find('input[name="startDate"]').val() && $(value).find('input[name="endDate"]').val()){
                        if($(value).find('input[name="startDate"]').val()>=$(value).find('input[name="endDate"]').val()){
                            error=true;
                            errorPath=$(value).find('input[name="name"]').val();
                            return false;
                        }
                    }

                    params.startDate += $(value).find('input[name="startDate"]').val() + '|';
                    params.endDate += $(value).find('input[name="endDate"]').val() + '|';
                }
            });
            if(error){
                layer.msg(errorPath+'证件的有效期与发证日期有冲突不符合逻辑，请核对', {icon: 2, time: 3000});
                return false;
            }

            // console.log(params);
            // return false;
            submit($, params, editOfflineSupplierUrl);
            return false;
        });

        //已选省份、城市
        var old_provinces=0;
        var old_provincesName = '';
        var old_cityName = '';
        var old_city=0;

        //选择省份
        form.on('select(provinces)', function (data) {
            var provinces = parseInt(data.value), provincesName = $(data.elem).siblings('.layui-form-select').find('input').val();
            if (data.value) {
                if (provinces !== old_provinces) {
                    var params = {};
                    params.action = 'getCity';
                    params.provinceid = provinces;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: editOfflineSupplierUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if(data.result.length>0){
                                    var html='<option value="">请选择城市</option>';
                                    $('select[name="areas"]').html(html);
                                    $.each(data.result,function (key,value) {
                                        html+='<option value="'+value.cityid+'">'+value.city+'</option>';
                                    });
                                    $('select[name="city"]').html(html);
                                }else{
                                    $('select[name="city"]').html('<option>/</option>');
                                    $('select[name="areas"]').html('<option>/</option>');
                                }
                                form.render();
                                old_provinces = provinces;
                                old_provincesName = provincesName;
                                $("input[name='address']").val(old_provincesName);
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
                var html='<option value="">请选择省份</option>';
                //选择空名称 复位填充数据
                $('select[name="city"]').html(html);
                $('select[name="areas"]').html(html);
                form.render();
                old_provinces=0;
                old_provincesName = '';
            }
        });

        //选择城市
        form.on('select(city)', function (data) {
            var city = parseInt(data.value), cityName = $(data.elem).siblings('.layui-form-select').find('input').val();
            if (data.value) {
                if (city !== old_city) {
                    var params = {};
                    params.action = 'getAreas';
                    params.cityid = city;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: editOfflineSupplierUrl,
                        data: params,
                        dataType: "json",
                        success: function (data) {
                            if (data.status === 1) {
                                if(data.result.length>0) {
                                    var html = '<option value="">请选择区/城镇</option>';
                                    $.each(data.result, function (key, value) {
                                        html += '<option value="' + value.areaid + '">' + value.area + '</option>';
                                    });
                                    $('select[name="areas"]').html(html);
                                }else{
                                    $('select[name="areas"]').html('<option value="">/</option>');
                                }
                                form.render();
                                old_city = city;
                                old_cityName = cityName;
                                $("input[name='address']").val(old_provincesName + old_cityName);
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
                var html='<option value="">请选择城市</option>';
                //选择空名称 复位填充数据
                $('select[name="areas"]').html(html);
                form.render();
                old_city=0;
                old_cityName = '';
            }
        });

        form.on('select(areas)', function (data) {
            var areasName = $(data.elem).siblings('.layui-form-select').find('input').val();
            $("input[name='address']").val(old_provincesName + old_cityName + areasName);
        });


        //上传文件
        uploadFile = upload.render({
            elem: '.file_url'  //绑定元素
            , url: editOfflineSupplierUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|pdf' //格式 用|分隔
            , method: 'POST'
            , data: {action: 'upload'}
            , choose: function (obj) {
                //选择文件后
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var item = this.item;
                    this.item.html('更新');
                    item.prev('input').val(res.path);
                    item.siblings('.layui-btn').removeClass('layui-btn-disabled');
                    item.siblings('.layui-btn').addClass('layui-btn-primary');
                    item.siblings('input[name="ext"]').val(res.ext);
                    var i = item.parents('.th-align-center').siblings('.fileTitle').find('i');
                    i.removeClass('layui-icon-zzban');
                    i.addClass('layui-icon-zzcheck');
                    i.css('color', '#5FB878');
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
            }
        });


        //预览
        $(document).on('click', '.showFile', function () {
            var path = $(this).siblings('input[name="path"]').val();
            var name = $(this).siblings('input[name="name"]').val();
            var ext = $(this).siblings('input[name="ext"]').val();
            if (path) {
                var url = admin_name+'/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: name + '相关文件查看',
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['75%', '100%'],
                    closeBtn: 1,
                    content: [url + '?path=' + path + '&filename=' + name+'.'+ext]
                });
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
        });


        //授权信息
        lay('.record-date').each(function(){
            laydate.render({
                elem: this
                ,trigger: 'click'
                ,isInitValue: false
            });
        });
        lay('.term-date').each(function(){
            laydate.render({
                elem: this
                ,trigger: 'click'
                ,isInitValue: false
            });
        });
        var fileListView = $('#fileListView')
            , auth_upload_obj = upload.render({
            elem: '#authUpload'
            , url: editOfflineSupplierUrl
            , data: {"action": "upload"}
            , accept: 'file'
            , exts: 'jpg|jpeg|png|pdf|xls|xlsx|doc|docx|ppt|pptx'
            , multiple: true
            , auto: true
            , choose: function (obj) {
                $('#authFileEmpty').remove();
                var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
                var tr = $('#fileList').find('tr');
                var xuhao = tr.length + 1;
                //读取本地文件
                obj.preview(function (index, file, result) {
                    var tr = $(['<tr id="upload-' + index + '">'
                        , '<td class="td-align-center">' + file.name + '</td>'
                        , '<td class="td-align-center"><span class="is_upload">等待上传</span></td>'
                        , '<td class="td-align-center" style="padding: 0;"><input type="text" name="auth_record_date[]" value="" placeholder="点击选择时间" class="layui-input record-date" style="cursor: pointer;border: none;height: 49px;"></td>'
                        , '<td class="td-align-center" style="padding: 0;"><input type="text" name="auth_term_date[]" value="" placeholder="点击选择时间" class="layui-input term-date" style="cursor: pointer;border: none;height: 49px;"></td>'
                        , '<td class="td-align-center">'
                        , '<button class="layui-btn layui-btn-xs layui-btn-danger file-delete"><i class="layui-icon">&#xe640;</i></button>'
                        , '<input type="hidden" name="auth_file_name[]" class="auth_file_name">\n' +
                        ' <input type="hidden" name="auth_ext[]" class="auth_ext">\n' +
                        ' <input type="hidden" name="auth_type[]" class="auth_type">\n' +
                        ' <input type="hidden" name="auth_path[]" class="auth_path">'
                        , '</td>'
                        , '</tr>'].join(''));

                    //删除
                    tr.find('.file-delete').on('click', function () {
                        console.log($(this).parents('tr').find('td:eq(0)').html());
                        delete files[index]; //删除对应的文件
                        tr.remove();
                        //重新编排序号
                        var alltr = $('#arcfileList').find('tr');
                        var i = 1;
                        $.each(alltr, function () {
                            $(this).find('td.xuhao').html(i);
                            i++;
                        });
                        auth_upload_obj.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                    });
                    fileListView.append(tr);
                    //动态同时绑定多个选择时间
                    tr.find('.record-date').each(function(){
                        laydate.render({
                            elem: this
                            ,trigger: 'click'
                            ,isInitValue: false
                        });
                    });
                    tr.find('.term-date').each(function(){
                        laydate.render({
                            elem: this
                            ,trigger: 'click'
                            ,isInitValue: false
                        });
                    });
                });
            }
            , done: function (res, index, upload) {
                if (res.status === 1) { //上传成功
                    var tr = fileListView.find('tr#upload-' + index)
                        , tds = tr.children();
                    tds.eq(1).html('<span style="color: #5FB878;" class="is_upload">上传成功</span>');
                    tr.find(".auth_file_name").val(res.formerly);
                    tr.find(".auth_ext").val(res.ext);
                    tr.find(".auth_type").val(5);
                    tr.find(".auth_path").val(res.path);
                    // tds.eq(3).html(''); //清空操作
                    //tds.eq(3).html(''); //清空操作
                    return delete this.files[index]; //删除文件队列已经上传成功的文件
                }
                this.error(index, upload);
            }
            , error: function (index, upload) {
                var tr = fileListView.find('tr#upload-' + index)
                    , tds = tr.children();
                tds.eq(2).html('<span style="color: #FF5722;" class="is_upload">上传失败</span>');
                //tds.eq(3).find('.file-reload').removeClass('layui-hide'); //显示重传
            }
        });

        //查看文件
        $(document).on('click', '.showAuthFile', function () {
            var path = $(this).attr('data-path');
            var name = $(this).attr('data-name');
            if (path) {
                var url = admin_name+'/Tool/showFile';
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
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
        });

        var changeArchiveTab = localStorage.getItem('auth_tab');
        if (changeArchiveTab){
            element.tabChange('change', parseInt(changeArchiveTab));
            localStorage.setItem('auth_tab','');
        }
        $(document).on('click', '.delFile', function () {
            var params = {};
            var fileid = $(this).attr('data-file-id');
            params.fileid = fileid;
            params.action = 'delete_auth';
            if (fileid) {
                $.ajax({
                    timeout: 5000,
                    type: "POST",
                    url: editOfflineSupplierUrl,
                    data: params,
                    dataType: "json",
                    success: function (data) {
                        if (data.status === 1) {
                            layer.msg('删除成功', {icon: 1, time: 1000},function () {
                                localStorage.setItem('auth_tab','2');
                                location.reload();
                            });

                        } else {
                            layer.msg('删除失败', {icon: 2, time: 3000});
                        }
                    },
                    error: function () {
                        layer.msg('网络访问失败', {icon: 2, time: 3000});
                    }
                });
            }
        });

    });
    exports('controller/offlineSuppliers/offlineSuppliers/editOfflineSupplier', {});
});
