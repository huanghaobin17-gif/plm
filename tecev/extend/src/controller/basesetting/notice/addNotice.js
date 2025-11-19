layui.define(function (exports) {
    layui.use(['form', 'layedit', 'upload', 'element', 'formSelects'], function () {
        var form = layui.form, layer = layui.layer, layedit = layui.layedit, upload = layui.upload, element = layui.element, formSelects = layui.formSelects;

        //发送用户组 多选框初始配置
        formSelects.render('group', selectParams(1));
        formSelects.btns('group', [], []);

        //发送用户 多选框初始配置
        formSelects.render('group_user', selectParams(1));
        formSelects.btns('group_user', selectParams(2));

        //监听联动变化事件
        formSelects.on('group', function () {
            setTimeout(function () {
                var roleid = formSelects.value('group', 'valStr');
                if (!roleid) {
                    //local模式
                    formSelects.data('group_user', 'local', {arr: []});
                } else {
                    //server模式
                    formSelects.data('group_user', 'server', {
                        url: "addNotice?action=getRoleUser&roleid=" + roleid
                    });
                }
            }, 500)
        });

        //上传文件
        var uploadInst = upload.render({
            elem: '#uploadFileEmer' //绑定元素
            , exts: 'pdf|txt|doc|docx|xls|xlsx|csv'
            , data: {"action": 'uploadFile'}
            , url: 'addNotice' //上传接口
            , done: function (res) {
                //上传完毕回调
                if (res.status == 1) {
                    switch (res.ext) {
                        case 'pdf':
                            var pic = '<img src="/Public/images/pdf.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                        case 'doc':
                        case 'docx':
                            var pic = '<img src="/Public/images/word.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                        case 'xls':
                        case 'xlsx':
                        case 'csv':
                            var pic = '<img src="/Public/images/excel.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                        case 'txt':
                            var pic = '<img src="/Public/images/text.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                        default:
                            var pic = '<img src="/Public/images/word.png" style="width: 18px;margin-right:5px;"/>';
                            break;
                    }
                    var html = '<li>' +
                        '   <input type="hidden" name="file_name[]" value="' + res['formerly'] + '">\n' +
                        '   <input type="hidden" name="save_name[]" value="' + res['title'] + '">\n' +
                        '   <input type="hidden" name="file_type[]" value="' + res['ext'] + '">\n' +
                        '   <input type="hidden" name="file_size[]" value="' + res['size'] + '">\n' +
                        '   <input type="hidden" name="file_url[]" value="' + res['src'] + '">' + pic +
                        '   <span>' + res.formerly + '</span>\n' +
                        '   <span class="show_file">预览</span>\n' +
                        '   <span class="del_file">删除</span>\n' +
                        '</li>';
                    $('#add_files').append(html);
                } else {
                    layer.msg(res.msg, {icon: 2, time: 2000});
                }
            }
            , error: function () {
                //请求异常回调
                layer.msg('网络繁忙', {icon: 2, time: 2000});
            }
        });

        layedit.set({
            uploadImage: {
                url: admin_name + '/Tool/addLayerImg' //接口url
            }
        });
        index = layedit.build('editor'); //建立编辑器

        //自定义验证规则
        form.verify({
            title: function (value) {
                if (value.length < 5) {
                    return '标题至少得5个字符啊';
                }
            }
            , content: function (value) {
                layedit.sync(editIndex);
            }
        });

        //监听指定开关
        form.on('switch(switchTest)', function (data) {
            layer.msg((this.checked ? '已开置顶' : '置顶关闭'), {
                offset: '6px'
            });
            if (data.elem.checked) {
                $("input[name='top']").val('1');
            } else {
                $("input[name='top']").val('0');
            }
        });

        //监听提交
        form.on('submit(save)', function (data) {
            var params = data.field;
            params.sendUserId = formSelects.value('group_user', 'valStr');
            params.content = layedit.getContent(index);
            submit($, params, 'addNotice');
            return false;
        });

        $(document).on('click', '.del_file', function () {
            $(this).parent().remove();
        });
        $(document).on('click', '.show_file', function () {
            var path = $(this).siblings('input[name="file_url[]"]').val();
            var name = $(this).siblings('input[name="file_name[]"]').val();
            var ext = $(this).siblings('input[name="file_type[]"]').val();
            name = name.substring(0, name.lastIndexOf("."));
            if (path) {
                var url = admin_name + '/Tool/showFile';
                top.layer.open({
                    type: 2,
                    title: name + '相关文件查看',
                    scrollbar: false,
                    offset: 'r',//弹窗位置固定在右边
                    anim: 2, //动画风格
                    area: ['75%', '100%'],
                    closeBtn: 1,
                    content: [url + '?path=' + path + '&filename=' + name + '.' + ext]
                });
            } else {
                layer.msg(name + '未上传,请先上传', {icon: 2}, 1000);
            }
        });
    });
    exports('controller/basesetting/notice/addNotice', {});
});