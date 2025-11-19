layui.define(function(exports){
    layui.use(['layer', 'form', 'element','table'], function() {
        var layer = layui.layer, form = layui.form, element = layui.element, laydate = layui.laydate, table = layui.table;
        //先更新页面部分需要提前渲染的控件
        form.render();
        //tips
        $ddd = $('.layui-card-header');
        $ddd.on('mouseenter', '*[lay-tips]', function () {
            var othis = $(this);

            if (othis.parent().hasClass('layui-nav-item') && !container.hasClass(SIDE_SHRINK)) return;

            var tips = othis.attr('lay-tips')
                , offset = othis.attr('lay-offset')
                , direction = othis.attr('lay-direction')
                , index = layer.tips(tips, this, {
                tips: direction || 1
                , time: -1
                , success: function (layero, index) {
                    if (offset) {
                        layero.css('margin-left', offset + 'px');
                    }
                }
            });
            othis.data('index', index);
        }).on('mouseleave', '*[lay-tips]', function () {
            layer.close($(this).data('index'));
        });

        //定义一个全局空对象
        var gloabOptions = {};
    });
    exports('remind/remind/remindSetting', {});
});
