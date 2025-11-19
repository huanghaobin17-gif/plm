layui.define(function(exports){
    layui.use(['form','laydate','element','upload'], function(){
        var $ = layui.$
            , laydate = layui.laydate
            , element = layui.element
            ,upload = layui.upload
            ,form = layui.form;
        laydate.render({
            elem: '#checkAssetsArrival_date',
            max: now_date
        });
        laydate.render({
            elem: '#checkAssetsCheck_date'
        });
        laydate.render({
            elem: '#checkAssetsFactorydate',
            max:now_date
        });
        laydate.render({
            elem: '#checkAssetsOpendate',
            min: now_date
        });
        laydate.render({
            elem: '#guarantee_date',
            min: now_date
        });
        laydate.render({
            elem: '#date6'
        });
        form.render();
        form.verify({
            model: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请输入设备规格 / 型号！';
                }
            },
            serialnum: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请填写产品序列号！';
                }
            },
            expected_life: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请输入预计使用年限！';
                }
            },
            departid: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择所属科室！';
                }
            },
            assetsrespon: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请输入资产负责人！';
                }
            },
            financeid: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择财务分类！';
                }
            },
            assfromid: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择设备来源！';
                }
            },
            capitalfrom: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择资金来源！';
                }
            },
            storage_date: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择入库日期！';
                }
            },
            opendate: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择启用日期！';
                }
            },
            check_date: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择验收日期！';
                }
            },
            arrival_date: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择到货日期！';
                }
            },
            factorydate: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择出厂日期！';
                }
            },
            depreciation_method: function (value) {
                value = $.trim(value);
                if (!value) {
                    return '请选择折旧方式！';
                }
            }
        });
        //监听提交
        form.on('submit(add)', function (data) {
            var params = data.field;
            submit($, params, checkAssetsUrl);
            return false;
        });


        //上传资料
        var fileData={};
        uploadFile = upload.render(
            {elem: '.file_url'  //绑定元素
                , url: checkAssetsUrl //接口
                , accept: 'file'
                , exts: 'jpg|png|bmp|jpeg|pdf' //格式 用|分隔
                , method: 'POST'
                , data: fileData
                , choose: function () {
                fileData.action='upload';
                fileData.file_id=this.item.siblings('input[name="file_id"]').val();
                fileData.style=this.item.siblings('input[name="style"]').val();
                fileData.file_name=this.item.siblings('input[name="file_name"]').val();
                fileData.assets_id=$('input[name="assets_id"]').val();
            }
                , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var item = this.item;
                    item.html('更新');
                    item.siblings('input[name="file_url"]').val(res.file_url);
                    item.siblings('input[name="file_id"]').val(res.file_id);
                    item.siblings('input[name="file_type"]').val(res.file_type);

                    item.parents('.th-align-center').siblings('.file_size').html(res.file_size);
                    item.parents('.th-align-center').siblings('.add_user').html(res.add_user);
                    item.parents('.th-align-center').siblings('.add_time').html(res.add_time);
                    item.siblings('.layui-btn').removeClass('layui-btn-disabled');
                    item.siblings('.layui-btn').addClass('layui-btn-primary');

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

        //上传附件
        uploadFile = upload.render({
            elem: '#addAssetsFile'  //绑定元素
            , url: checkAssetsUrl //接口
            , accept: 'file'
            , exts: 'jpg|png|bmp|jpeg|doc|docx|pdf' //格式 用|分隔
            , method: 'POST'
            , data: fileData
            , choose: function (obj) {
                //选择文件后
                fileData.action='upload';
                fileData.assets_id=$('input[name="assets_id"]').val();
            }
            , done: function (res) {
                layer.closeAll('loading');
                if (res.status === 1) {
                    var notFileDataTr = $('.notFileDataTr');
                    if (notFileDataTr.length > 0) {
                        notFileDataTr.remove();
                    }
                    var addFileTbody = $('.addFileTbody');
                    var html = '<tr class="fileDataTr">';
                    html += '<td class="file_name">' + res.formerly + '</td>';
                    html += '<td class="add_user">' + res.add_user + '</td>';
                    html += '<td class="add_time">' + res.add_time + '</td>';
                    html+='<div class="layui-btn-group">';
                    var input = '<input type="hidden" name="file_id" value="' + res.file_id + '">';
                    input += '<input type="hidden" name="file_type" value="' + res.file_type + '">';
                    input += '<input type="hidden" name="file_name" value="' + res.file_name + '">';
                    input += '<input type="hidden" name="file_url" value="' + res.file_url + '">';

                    var button='<button class="layui-btn layui-btn-xs downFile" lay-event="" style="" data-url="">下载</button>';
                    if(res.file_type!=='doc' || res.file_type!=='docx'){
                        button+='<button class="layui-btn layui-btn-xs layui-btn-normal showFile" lay-event="" style="" data-url="">预览</button>';
                    }
                    button+='<button class="layui-btn layui-btn-xs layui-btn-danger del_file">移除</button>';
                    html += '<td><div class="layui-btn-group">'+input+button+'</div></td>';
                    html += '</div>';
                    addFileTbody.append(html);
                    layer.msg(res.msg, {icon: 1}, 1000);
                } else {
                    layer.msg(res.msg, {icon: 2}, 1000);
                }
            }
            , error: function (index, upload) {
                //失败
                layer.msg('上传失败', {icon: 2}, 1000);
            }
        });

        //预览
        $(document).on('click', '.showFile', function () {
            var path = $(this).siblings('input[name="file_url"]').val();
            var name = $(this).siblings('input[name="file_name"]').val();
            var ext = $(this).siblings('input[name="file_type"]').val();
            if (path) {
                var url = admin_name+'/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: name + ' 文件查看',
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
            return false;
        });

        //下载
        $(document).on('click','.downFile',function () {
            var params={};
            params.path= $(this).siblings('input[name="file_url"]').val();
            params.filename=$(this).siblings('input[name="file_name"]').val();
            postDownLoadFile({
                url:admin_name+'/Tool/downFile',
                data:params,
                method:'POST'
            });
            return false;
        });

        //移除文件
        $(document).on('click', '.del_file', function () {
            var thisTr = $(this).parents('tr');
            var addFileTbody = $('.addFileTbody');
            var params={};
            params.action='deleteFile';
            params.file_id=$(this).siblings('input[name="file_id"]').val();
            $.ajax({
                timeout: 5000,
                type: "POST",
                url: checkAssetsUrl,
                data: params,
                dataType: "json",
                success: function (data) {
                    if (data.status === 1) {
                        thisTr.remove();
                        if (addFileTbody.find('tr').length === 0) {
                            addFileTbody.html('<tr class="notFileDataTr"><td colspan="4" style="text-align: center!important;">暂无数据</td></tr>');
                        }
                        layer.msg(data.msg, {icon: 1}, 1000);
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

        $('#paizhao').on('mouseover',function () {
            $('.qrcode').show();
        });
        $('#paizhao').on('mouseout',function () {
            $('.qrcode').hide();
        });




    });
    exports('controller/purchases/purchaseCheck/checkAssets', {});
});