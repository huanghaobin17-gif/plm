<?php if (!defined('THINK_PATH')) exit();?><style>
    .messageTriangle {
        width: 0;
        height: 0;
        border-bottom: 8px solid #FFB800;
        border-left: 8px solid transparent;
        border-right: 8px solid transparent;
        left: 90px;
        top: -8px;
        position: absolute;
    }

    .messageRange {
        width: 200px;
        height: 20px;
        position: absolute;
        background-color: #FFB800;
        left: -70px;
        line-height: 20px;
        display: none
    }

    .red {
        border: 1px solid #d00;
        background: #ffe9e8;
        color: #d00;
    }

    .auth-text a {
        color: #01aaed;
    }
</style>
<div class="layui-layout layui-layout-admin">
    <div class="layui-header">
        <!-- 头部区域 -->
        <ul class="layui-nav layui-layout-left">
            <li class="layui-nav-item layadmin-flexible" lay-unselect>
                <a href="javascript:;" layadmin-event="flexible" title="侧边伸缩">
                    <i class="layui-icon layui-icon-shrink-right" id="LAY_app_flexible"></i>
                </a>
            </li>
            <!--<li class="layui-nav-item layui-this layui-hide-xs layui-hide-sm layui-show-md-inline-block">
              <a lay-href="" title="">
                控制台
              </a>
            </li>-->
            <!--<li class="layui-nav-item layui-hide-xs" lay-unselect>-->
            <!--<a href="http://www.layui.com/admin/" target="_blank" title="前台">-->
            <!--<i class="layui-icon layui-icon-website"></i>-->
            <!--</a>-->
            <!--</li>-->
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" layadmin-event="refresh" title="刷新">
                    <i class="layui-icon layui-icon-refresh-3"></i>
                </a>
            </li>
        </ul>
        <ul class="layui-nav layui-layout-right" lay-filter="layadmin-layout-right">
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;">
                    <cite><?php echo ($a=session('current_hospitalname')); ?></cite>
                </a>
            </li>

            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" layadmin-event="hospitals" id="hospitals" title="管理医院">
                    <i class="tecevicon tecev-zhenliaofuwu" style="font-size: 16px;"></i>
                </a>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" layadmin-event="manual" id="manual" title="操作手册">
                    <i class="tecevicon tecev-shouce1" style="font-size: 16px;"></i>
                </a>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" layadmin-event="screen" id="screen" title="大屏显示">
                    <i class="tecevicon tecev-jiankong02" style="font-size: 16px;"></i>
                </a>
            </li>
            <li class="layui-nav-item" lay-unselect>
                <a href="javascript:;" layadmin-event="cache" id="cache" title="更新缓存">
                    <i class="tecevicon tecev-gengxinhuancun1" style="font-size: 16px;"></i>
                </a>
            </li>

            <li class="layui-nav-item layui-hide-xs systemMessage" lay-unselect>


                <!--<a lay-href="app/message/" layadmin-event="message">-->
                <a href="javascript:;" layadmin-event="message" title="我的消息">
                    <i class="layui-icon layui-icon-notice"></i>
                    <!-- 如果有新消息，则显示小圆点 -->
                    <script type="text/html" template lay-url="/tecev/start/json/message/new.js">
                        <?php if($taskNum != 0): ?><span class="layui-badge-dot"></span><?php endif; ?>
                    </script>
                </a>
                <div class="messageRange">
                    <div class="messageTriangle"></div>
                    <span class="messageText">
                        您有一个新的任务，请注意查看
                    </span>
                </div>
            </li>

            <li class="layui-nav-item layui-hide-xs" lay-unselect>
                <a href="javascript:;" layadmin-event="theme" title="切换主题">
                    <i class="layui-icon layui-icon-theme"></i>
                </a>
            </li>
            <li class="layui-nav-item layui-hide-xs" lay-unselect>
                <a href="javascript:;" layadmin-event="note" title="我的便签">
                    <i class="layui-icon layui-icon-note"></i>
                </a>
            </li>
            <li class="layui-nav-item" lay-unselect style="margin-right: 56px">
                <script type="text/html" template lay-url="/tecev/start/json/user/session.js"
                    lay-done="layui.element.render('nav', 'layadmin-layout-right');">
                    <a href="javascript:;">
                        <cite><?php echo ($a=session('username')); ?></cite>
                    </a>
                    <dl class="layui-nav-child">
                        <dd><a lay-href="User/userInfo.html"><i class="iconfont icon-wo"></i> 基本资料</a></dd>
                        <?php if($showChangHospitalOption): ?><dd><a href="javascript:;" layadmin-event="qiehuan"><i class="iconfont icon-shoushuru"></i>
                                切换医院</a></dd><?php endif; ?>
                        <hr>
                        <dd layadmin-event="logout" style="text-align: center;"><a><i class="iconfont icon-tuichu1"></i>
                            退出登录</a></dd>
                    </dl>
                </script>
            </li>

            <!-- <li class="layui-nav-item layui-hide-xs" lay-unselect>
              <a href="javascript:;" layadmin-event="about"><i class="layui-icon layui-icon-more-vertical"></i></a>
            </li> -->
            <li class="layui-nav-item layui-show-xs-inline-block layui-hide-sm" lay-unselect>
                <a href="javascript:;" layadmin-event="more"><i class="layui-icon layui-icon-more-vertical"></i></a>
            </li>
        </ul>
    </div>

    <!-- 侧边菜单 -->
    <div class="layui-side layui-side-menu">
        <div class="layui-side-scroll">
            <script type="text/html" template lay-url="/A/Login/getMenus"
                lay-done="layui.element.render('nav', 'layadmin-system-side-menu');" id="TPL_layout">

                <div class="layui-logo" style="font-size: 16px;padding: 0 3px;" lay-href="">
                    <span>{{ layui.setter.name || 'layuiAdmin' }}</span>
                </div>

                <ul class="layui-nav layui-nav-tree" lay-shrink="all" id="LAY-system-side-menu"
                    lay-filter="layadmin-system-side-menu">
                    {{#
                    var path = layui.router().path
                    ,pathURL = layui.admin.correctRouter(path.join('/'))
                    ,dataName = layui.setter.response.dataName;
                    layui.each(d[dataName], function(index, item){
                    var hasChildren = typeof item.list === 'object' && item.list.length > 0
                    ,classSelected = function(){
                    var match = item.include.indexOf(path[0])>=0 || (index == 0 && !path[0])
                    || (item.jump && pathURL == layui.admin.correctRouter(item.jump));
                    if(match){
                    return hasChildren ? 'layui-nav-itemed' : 'layui-this';
                    }
                    return '';
                    }
                    ,url = (item.jump && typeof item.jump === 'string') ? item.jump : item.name;
                    }}
                    <li data-name="{{ item.name || '' }}" data-jump="{{ item.jump || '' }}"
                        class="layui-nav-item {{ classSelected() }}">
                        <a href="javascript:;" {{ hasChildren ? '' : 'lay-href="'+ url +'"' }} lay-tips="{{ item.title
                        }}" lay-direction="2">
                        <i class="layui-icon {{ item.icon }}"></i>
                        <cite>{{ item.title }}</cite>
                        </a>
                        {{# if(hasChildren){ }}
                        <dl class="layui-nav-child">
                            {{# layui.each(item.list, function(index2, item2){
                            var hasChildren2 = typeof item2.list == 'object' && item2.list.length > 0
                            ,classSelected2 = function(){
                            var match = (item.include.indexOf(path[0]) >= 0 && item2.include.indexOf(path[1]) >= 0)
                            || (item2.jump && pathURL == layui.admin.correctRouter(item2.jump));
                            if(match){
                            return hasChildren2 ? 'layui-nav-itemed' : 'layui-this';
                            }
                            return '';
                            }
                            ,url2 = (item2.jump && typeof item2.jump === 'string')
                            ? item2.jump
                            : [item.name, item2.name, ''].join('/');
                            }}
                            <dd data-name="{{ item2.name || '' }}" data-jump="{{ item2.jump || '' }}"
                                {{ classSelected2() ? (
                            'class="'+ classSelected2() +'"') : '' }}>
                            <a href="javascript:;" {{ hasChildren2 ? '' : 'lay-href="'+ url2 +'"' }}>{{ item2.title
                            }}</a>
                            {{# if(hasChildren2){ }}
                            <dl class="layui-nav-child">
                                {{# layui.each(item2.list, function(index3, item3){
                                var match = (path[0] == item2.name && path[1] == item3.name)
                                || (item3.jump && pathURL == layui.admin.correctRouter(item3.jump))
                                ,url3 = (item3.jump && typeof item3.jump === 'string')
                                ? item3.jump
                                : [item2.name, item3.name].join('/');
                                }}
                                <dd data-name="{{ item3.name || '' }}" data-jump="{{ item3.jump || match }}"
                                    {{ match ?
                                'class="layui-this"' : '' }}>
                                <a href="javascript:;" lay-href="{{ url3 }}" {{ item3.iframe ? 'lay-iframe="true"' : ''
                                }}>{{ item3.title }}</a>
                                </dd>
                                {{# }); }}
                            </dl>
                            {{# } }}
                            </dd>
                            {{# }); }}
                        </dl>
                        {{# } }}
                    </li>
                    {{# }); }}
                </ul>
            </script>
        </div>
    </div>


    <!-- 页面标签 -->
    <script type="text/html" template lay-done="layui.element.render('nav', 'layadmin-pagetabs-nav')">
        {{# if(layui.setter.pageTabs){ }}
        <div class="layadmin-pagetabs" id="LAY_app_tabs">
            <div class="layui-icon layadmin-tabs-control layui-icon-prev" layadmin-event="leftPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-next" layadmin-event="rightPage"></div>
            <div class="layui-icon layadmin-tabs-control layui-icon-down">
                <ul class="layui-nav layadmin-tabs-select" lay-filter="layadmin-pagetabs-nav">
                    <li class="layui-nav-item" lay-unselect>
                        <a href="javascript:;"></a>
                        <dl class="layui-nav-child layui-anim-fadein">
                            <dd layadmin-event="closeThisTabs"><a href="javascript:;">关闭当前标签页</a></dd>
                            <dd layadmin-event="closeOtherTabs"><a href="javascript:;">关闭其它标签页</a></dd>
                            <dd layadmin-event="closeAllTabs"><a href="javascript:;">关闭全部标签页</a></dd>
                        </dl>
                    </li>
                </ul>
            </div>
            <div class="layui-tab" lay-unauto lay-allowClose="true" lay-filter="layadmin-layout-tabs">
                <ul class="layui-tab-title" id="LAY_app_tabsheader">
                    <li lay-id="/"><i class="layui-icon layui-icon-home"></i></li>
                </ul>
            </div>
        </div>
        {{# } }}
    </script>


    <!-- 主体内容 -->
    <div class="layui-body" id="LAY_app_body">
        <input type="hidden" name="sessionUserid" value="<?php echo ($sessionUserid); ?>">
        <div class="layadmin-tabsbody-item layui-show"></div>
    </div>

    <div class="layui-footer auth-text">
        <!--<a class="fl auth-popup" style="color: red"><?php echo (C("COMPANY_NAME")); ?> 版权所有 © 2015~2024</a>-->
        <a class="fl auth-popup" style="color: red;cursor:pointer">未授权，点我进行授权。</a>
        <a class="fl license-view" href="javascript:;">(查看授权)</a>
    </div>

    <!-- 辅助元素，一般用于移动设备下遮罩 -->
    <div class="layadmin-body-shade" layadmin-event="shade"></div>
    <div id="ID-test-layer-wrapper" style="display: none;">
        <form action="" class="layui-form" style="margin-top: 10px;">
            <div class="layui-form-item">
                <div class="layui-inline">
                    <label class="layui-form-label">授权码：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="auth_code" readonly value="123" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">公司：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="company" value="" class="layui-input">
                    </div>
                </div>
                <div class="layui-inline">
                    <label class="layui-form-label">电话：</label>
                    <div class="layui-input-inline">
                        <input type="text" name="mobile" value="" class="layui-input">
                    </div>
                </div>
            </div>
            <div class="layui-form-item">
                <div class="layui-input-block">
                    <button class="layui-btn" lay-submit lay-filter="demo-login"><i
                            class="layui-icon">&#xe609;</i>立即提交</button>
                </div>
            </div>
        </form>
    </div>
</div>
<script>
    $(document).ready(function () {
        $("#qiehuan").on('click', function () {
            return false;
        });

        //管理医院
        $('#hospitals').on('click', function () {
            var url = '/#/Tool/yy.html';
            window.location.href = url;
        });

        //操作手册
        $('#manual').on('click', function () {
            var url = '/manual/';
            window.open(url, "blank");
        });

        //大屏显示
        $('#screen').on('click', function () {
            //var url = admin_name+'/Tool/screen.html';
            var url = admin_name + '/Tool/scr.html';
            window.open(url, "blank");
        });

        //更新缓存
        $('#cache').on('click', function () {
            var url = admin_name + '/Cache/index.html';
            $.ajax({
                type: "POST",
                dataType: "json",
                url: url,
                //datatype: "html",//"xml", "html", "script", "json", "jsonp", "text".
                beforeSend: function () {
                    layer.msg('正在更新缓存，请稍候...', {
                        icon: 16,
                        time: 14000,
                        shade: 0.01
                    });
                },
                //成功返回之后调用的函数
                success: function (data) {
                    if (data.status == 1) {
                        layer.msg(data.msg, { icon: 1 });
                    } else {
                        layer.msg(data.msg, { icon: 2 });
                    }
                },
                //调用出错执行的函数
                error: function () {
                    //请求出错处理
                    layer.msg('服务器繁忙', { icon: 2 });
                },
                complete: function () {
                    layer.closeAll('msg');
                }
            });
        });


        fetch('/A/Login/getAuth').then(res => {
            return res.json();
        }).then(res => {
            if (res['status'] === 1) {
                $(".auth-text").html('<a class="fl">' + res['msg'] + '</a>');
            }
        })


        $(".auth-popup").on('click', function () {
            var $ = layui.$;
            var layer = layui.layer;
            var form = layui.form;
            layer.open({
                type: 1,
                title: '授权表单',
                shade: false, // 不显示遮罩
                content: $('#ID-test-layer-wrapper'),
                success: function () {
                    fetch('/A/Login/auth').then(res => {
                        return res.text();
                    }).then(res => {
                        $("input[name='auth_code']").val(res);
                        // 对弹层中的表单进行初始化渲染
                        form.render();
                        // 表单提交事件
                        form.on('submit(demo-login)', function (data) {
                            var field = data.field; // 获取表单字段值
                            // 显示填写结果，仅作演示用
                            // layer.alert(JSON.stringify(field), {
                            //     title: '当前填写的字段值'
                            // });
                            // 此处可执行 Ajax 等操作
                            // …
                            // fetch post请求
                            fetch('/A/Login/auth', {
                                method: 'POST', // or 'PUT'
                                headers: {
                                    'Content-Type': 'application/json'
                                },
                                body: JSON.stringify(field) // data can be `string` or {object}!
                            }).then(res => {
                                return res.json();
                            }).then(res => {
                                if (res.status === -1) {
                                    layer.msg(res.msg, { icon: 0 });
                                    return
                                }
                                layer.msg(res.msg, { icon: 1 });
                            });
                            return false; // 阻止默认 form 跳转
                        });
                    })

                },
                end: function () {
                    // layer.msg('关闭后的回调', {icon:6});
                }
            });


        })

        // Add license viewer
        $(".license-view").on('click', function () {
            var layer = layui.layer;
            layer.open({
                type: 2,
                title: '授权信息',
                shade: 0.3,
                maxmin: true,
                shadeClose: true,
                area: ['80%', '80%'],
                content: '/license.txt'
            });
        });

    });
</script>