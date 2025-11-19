layui.define(function(exports){
    layui.use(['layer', 'form', 'element','laydate','table','admin'], function(){
        var layer = layui.layer,form = layui.form,element = layui.element,laydate = layui.laydate,table = layui.table,admin = layui.admin;

        //先更新页面部分需要提前渲染的控件
        form.render();
        layer.config(layerParmas());

        //点击类别
        $('.getDetail').on('click',function() {
            //重载模板
            var type = $(this).attr('data-type');
            var params = {};
            params.type = type;
            var title = $(this).html();
            //更改模板
            $.ajax({
                timeout: 5000,
                type: "get",
                url: admin_name+'/Quality/presetQualityItem',
                data: params,
                dataType: "html",
                beforeSend:beforeSend,
                success: function (data) {
                    //更改显示标题
                    $(".detailName").html(title);
                    $("#printAssetsTemp").attr('data-type',type);
                    $("#main-content").html(data);
                },
                error: function () {
                    layer.msg("网络访问失败",{icon : 2},1000);
                },
                complete:complete
            });
            return false;
        });

        //打印空白文档
        $('#printTemp').on('click',function () {
            $('#templates-content').find('em').html('');
            $('.fr').css('padding-right','60px');
            $('#templates-content').printArea();
        });

        //打印设备模板
        $('#printAssetsTemp').on('click',function () {
            top.layer.open({
                id: 'printAssetsTemps',
                type: 2,
                title: '搜索打印设备',
                area: ['80%', '100%'],
                offset: 'r',//弹窗位置固定在右边
                anim: 2, //动画风格
                scrollbar:false,
                closeBtn: 1,
                content: [admin_name+'/Quality/presetQualityItem?action=searchAssets&temp='+$(this).attr('data-type')],
                end: function () {
                    if(flag){
                        table.reload('qualityPlanList', {
                            url: admin_name+'/Quality/getQualityList.html'
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
        });

        //控制子侧边菜单文字太长时省略号变成提示
        var item = $("#LAY-Qualities-Quality-presetQualityItem .layui-nav-item");
        $.each(item,function(j,val){
            if ($(val).children().html().length>18){
                var tips = $(val).children().html();
                $(val).attr("lay-tips",tips)
            }
        });

        //监听伸缩事件，控制子侧边菜单的绝对定位
        admin.on('side(leftChildmenu)', function(obj){
            if (obj.status == null){
                $("#LAY-Qualities-Quality-presetQualityItem .layui-side-child").css("left",80);
                $("#LAY-Qualities-Quality-presetQualityItem .addDetail").css("right",145);
            }else {
                $("#LAY-Qualities-Quality-presetQualityItem .layui-side-child").css("left",235);
                $("#LAY-Qualities-Quality-presetQualityItem .addDetail").css("right",0);
            }
        });


        $('#addPresetQI').on('click',function() {
            var flag = 1;
            var url = $(this).attr('data-url');
            top.layer.open({
                id: 'addPresetQIs',
                type: 2,
                anim: 2, //动画风格
                title: $(this).html(),
                scrollbar:false,
                offset: 'r',//弹窗位置固定在右边
                area: ['880px', '100%'],
                closeBtn: 1,
                content: [url]
            });
            return false;
        });

        //新增模板
        $('#addTemplates').on('click',function() {
            layer.open({
                type: 0,
                content: '新增质控模板请联系系统供应商！<br>王喜娟：132 0291 8082' //这里content是一个普通的String
            });
            return false;
        })

    });
    exports('qualities/quality/presetQualityItem', {});
});
