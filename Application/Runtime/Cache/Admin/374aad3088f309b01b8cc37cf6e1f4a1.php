<?php if (!defined('THINK_PATH')) exit();?><style>
    #taskul li{
        padding: 6px 10px;
    }
    /*#taskul li .title{*/
    /*float: left;*/
    /*}*/
    #LAY-BaseSetting-System-message .layui-card-header{
        border-bottom-color: #dddddd;
    }

    #LAY-BaseSetting-System-message .layui-tab-content{
        padding: 0;
        padding-left: 10px;
    }
    #LAY-BaseSetting-System-message .layui-tab-content .layui-badge{
        margin-top: 12px;
    }

    #LAY-BaseSetting-System-message .layui-tab-title .layui-this:after{
        height: 51px;
    }
    #LAY-BaseSetting-System-message .layui-badge{
        margin-top: 13px;
        margin-left: 12px;
        float: right;
    }
    #LAY-BaseSetting-System-message .titlecolor{
        line-height: 40px;
    }
    #LAY-BaseSetting-System-message .prompt{
        padding-left: 10px;
        margin-top:10px;
        padding-top: 10px;
        border-top:1px solid #f6f6f6;
    }
    /*竖向选项卡样式优化调整*/
    #LAY-BaseSetting-System-message .layui-tab-title li{
        display: block;
        text-align: left;
        min-width: 120px;
        padding:5px 17px 5px 14px
    }
    #LAY-BaseSetting-System-message .layui-tab-title{
        float: left;
        width: auto;
        height: 100%;
        border-bottom: none;
        border-right: 1px solid #dddddd;
    }
    #LAY-BaseSetting-System-message .layui-tab-content{
        float: left;
        width: auto;
    }
    #LAY-BaseSetting-System-message .layui-card-body{
        padding: 0;
        height:100%;
    }
    #LAY-BaseSetting-System-message .layui-tab-content{
        height: auto !important;
    }
    #LAY-BaseSetting-System-message .layui-tab{
        margin: 0;
    }
    #LAY-BaseSetting-System-message .table_left:last-child{
        display: none;
    }
    #LAY-BaseSetting-System-message .table_left span.title{
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
        width: 370px;
        float: left;
    }


    #LAY-BaseSetting-System-message,#LAY_adminPopupTheme{
        height:100%;
    }
    #LAY-BaseSetting-System-message{
        position: relative;
        overflow: hidden;
    }
    #LAY-BaseSetting-System-message .layui-tab-brief{
        height:100%;
    }
</style>
<div id="LAY-BaseSetting-System-message">
    <div class="layui-card-header">任务提醒&nbsp&nbsp（注：<span style="color: sandybrown">橙色</span>标签为提示内容，不统计为未读消息）</div>
    <div class="layui-card-body">
        <div class="layui-tab layui-tab-brief">
            <ul class="layui-tab-title">
                <?php if(empty($task)): ?><li>暂无任务</li>
                    <?php else: ?>
                    <?php if(is_array($task)): $key = 0; $__LIST__ = $task;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($key % 2 );++$key;?><li><?php echo ($key); ?> <span class="layui-badge"><?php echo ($v["total"]); ?></span></li><?php endforeach; endif; else: echo "" ;endif; endif; ?>
            </ul>
            <div class="layui-tab-content">
                <?php if(!empty($task)): if(is_array($task)): $i = 0; $__LIST__ = $task;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><div class="layui-tab-item">
                            <ul>
                                <?php if(is_array($v)): $i = 0; $__LIST__ = $v;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v1): $mod = ($i % 2 );++$i;?><li class="table_left">
                                        <span class="title"><?php echo ($v1["title"]); ?></span>
                                        <span class="layui-badge"><?php echo ($v1["num"]); ?></span>
                                    </li><?php endforeach; endif; else: echo "" ;endif; ?>
                            </ul>
                        </div><?php endforeach; endif; else: echo "" ;endif; endif; ?>
            </div>
        </div>
        <div class="clear"></div>
    </div>
</div>
<script type="text/javascript">
    var baseSettingMessageObj = $("#LAY-BaseSetting-System-message");
    var title = baseSettingMessageObj.find(".layui-tab-title").children(),item = baseSettingMessageObj.find(".layui-tab-item");
    $(function(){
        $.each(title,function(k,v){
            if (k == 0){
                $(v).addClass("layui-this");
            }
        });
        $.each(item,function(k,v){
            if (k == 0){
                $(v).addClass("layui-show");
            }
        });
    });
</script>