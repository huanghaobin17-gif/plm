layui.define(function(exports){
    layui.use(['layer','form','laydate','upload','tipsType'], function(){
        var form = layui.form,laydate = layui.laydate,upload = layui.upload,tipsType = layui.tipsType;

        //初始化tips的选择功能
        tipsType.choose();

        form.verify({
            mobilePhone: function(value){ //value：表单的值、item：表单的DOM对象
                if(!checkTel(value)){
                    return '请正确填写电话号码，例如:13800138000或020-12345678，若电话为400，800开头则不需要填写分隔符';
                }
            }

        });

        //提交报废处置
        form.on('submit(add)', function (data) {
            params = data.field;
            submit($,params,'result');
            return false;
        });

        //处置时间元素渲染
        laydate.render(dateConfig('#cleardate'));

        //上传文件按钮
        var fileListView = $('#fileList')
            ,uploadListIns = upload.render({
            elem: '#multifile'
            ,url: 'result?type=uploadFile'
            ,accept: 'file'
            ,exts: 'jpg|png|gif|bmp|jpeg|pdf|doc|docx'
            ,multiple: true
            ,auto: false
            ,bindAction: '.uploadFile'
            ,choose: function(obj){
                $('#empty').remove();
                var files = this.files = obj.pushFile(); //将每次选择的文件追加到文件队列
                //读取本地文件
                obj.preview(function(index, file){
                    var tr = $(['<tr id="upload-'+ index +'">'
                        ,'<td>'+ file.name +'</td>'
                        ,'<td><span>等待上传</span></td>'
                        ,'<td>'
                        //,'<button class="layui-btn layui-btn-sm file-reload layui-hide">重传</button>'
                        ,'<button class="layui-btn layui-btn-xs layui-btn-danger file-delete"><i class="layui-icon">&#xe640;</i></button>'
                        ,'</td>'
                        ,'</tr>'].join(''));
                    //删除
                    tr.find('.file-delete').on('click', function(){
                        delete files[index]; //删除对应的文件
                        tr.remove();
                        uploadListIns.config.elem.next()[0].value = ''; //清空 input file 值，以免删除后出现同名文件不可选
                    });

                    fileListView.append(tr);
                });
            }
            ,done: function(res, index, upload){
                if(res.status == 1){ //上传成功
                    var tr = fileListView.find('tr#upload-'+ index)
                        ,tds = tr.children();
                    tds.eq(1).html('<span style="color: #5FB878;">上传成功</span>');
                    params = {};
                    var scrid = $("input[name='scrid']").val();
                    params.scrid = scrid;
                    params.uploadFiles = res.path;
                    $.ajax({
                        timeout: 5000,
                        type: "POST",
                        url: 'result?type=uploadScrap',
                        data: params,
                        dataType: "json",
                        success: function (data) {

                        },
                        error: function () {

                        }
                    });
                    return delete this.files[index]; //删除文件队列已经上传成功的文件
                }
                this.error(index, upload);
            }
            ,error: function(index, upload){
                var tr = fileListView.find('tr#upload-'+ index)
                    ,tds = tr.children();
                tds.eq(1).html('<span style="color: #FF5722;" >上传失败</span>');
            }
        });

        //相关文件查看
        $("#showFile").on('click',function() {
            var scrid = $("input[name='scrid']").val();
            top.layer.open({
                id: 'showFiles',
                type: 2,
                title: '相关文件查看',
                shade: 0,
                anim:2,
                offset: 'r',//弹窗位置固定在右边
                scrollbar:false,
                area: ['70%', '100%'],
                closeBtn: 1,
                content: [admin_name+'/Scrap/result.html?type=showFile&scrid='+scrid]
            });
        });

    });
    exports('controller/assets/scrap/result', {});
});


