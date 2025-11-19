<?php
////此处查看链接状态
//header("Content-type:text/html;charset=utf-8");
//if (!fsockopen('tls://pop.163.com', 995, $error, $errorstr, 8)) {
//    echo 'cennect 163 fail!';
//}
//$host = "tls://pop.163.com"; //‘tls：//’为ssl协议加密，端口走加密端口
//$user = "wxdiallon@163.com"; //邮箱
////$user="wangyidj@126.com"; //邮箱
////$user="13084901@qq.com"; //邮箱
//
////$pass = "qwe123163"; //密码
//$pass="ZGDQSOBHGJGKICID"; //密码icexjxkjqdkvbgii

class Pop3
{
    var $hostname = "";// POP主机名
    var $port = 110;// 主机的POP3端口，一般是110号端口
    var $timeout = 5;// 连接主机的最大超时时间
    var $connection = 0; // 保存与主机的连接
    var $state = "DISCONNECTED"; // 保存当前的状态
    var $debug = 0;// 做为标识，是否在调试状态，是的话，输出调试信息
    var $err_str = '';// 如果出错，这里保存错误信息
    var $err_no;//如果出错，这里保存错误号码
    var $resp;// 临时保存服务器的响应信息
    var $apop;// 指示需要使用加密方式进行密码验证，一般服务器不需要
    var $messages; // 邮件数
    var $size;//各邮件的总大小
    var $mail_list; // 一个数组，保存各个邮件的大小及其在邮件服务器上序号
    var $head = array(); // 邮件头的内容，数组
    var $body = array(); // 邮件体的内容，数组;

    public function Pop3($server = "192.100.100.1", $port = 110, $time_out = 5)
    {
        $this->hostname = $server;
        $this->port = $port;
        $this->timeout = $time_out;
        return true;
    }

    public function Open()
    {
        if ($this->hostname == "") {
            $this->err_str = "无效的主机名!!";
            return false;
        }
        if ($this->debug) {
            echo "正在打开 $this->hostname,$this->port,$err_no, $err_str, $this->timeout<BR>";
        }
        if (!$this->connection = @fsockopen($this->hostname, $this->port, $err_no, $err_str, $this->timeout)) {
            $this->err_str = "连接到POP服务器失败，错误信息：" . $err_str . "错误号：" . $err_no;
            return false;
        } else {
            $this->getresp();
            if ($this->debug) {
                $this->outdebug($this->resp);
            }
            if (substr($this->resp, 0, 3) != "+OK") {
                $this->err_str = "服务器返回无效的信息：" . $this->resp . "请检查POP服务器是否正确";
                return false;
            }
            $this->state = "AUTHORIZATION";
            return true;
        }
    }

    public function getresp()
    {
        for ($this->resp = ""; ;) {
            if (feof($this->connection)) {
                return false;
            }
            $this->resp .= fgets($this->connection, 100);
            $length = strlen($this->resp);
            if ($length >= 2 && substr($this->resp, $length - 2, 2) == "\r\n") {
                $this->resp = strtok($this->resp, "\r\n");
                return true;
            }
        }
    }

    //这个方法取得服务器端的返回信息并进行简单的处理：去掉最后的回车换行符，将返回信息保存在resp这个内部变量中。
    //这个方法在后面的多个操作中都将用到。另外，还有个小方法也在后面的多个操作中用到：
    public function outdebug($message)
    {
        echo htmlspecialchars($message) . "<br>\n";
    }

    //它的作用就是把调试信息$message显示出来，并把一些特殊字符进行转换以及在行尾加上<br>标签，
    //这样是为了使其输出的调试信息便于阅读和分析。
    //建立起与服务器的sock连接之后，就要给服务器发送相关的命令了（请参见上面的与服务器对话的过程）从上面对
    // POP对话的分析可以看到，每次都是发送一条命令，然后服务器给予一定的回应，如果命令的执行是对的，回应一般是以+OK开头，
    //后面是一些描述信息，所以，我们可以做一个通过发送命令的方法:
    public function command($command, $return_lenth = 1, $return_code = '+')
    {
        if ($this->connection == 0) {
            $this->err_str = "没有连接到任何服务器，请检查网络连接";
            return false;
        }
        if ($this->debug) {
            $this->outdebug(">>> $command");
        }
        if (!fputs($this->connection, "$command\r\n")) {
            $this->err_str = "无法发送命令" . $command;
            return false;
        } else {
            $this->getresp();
            if ($this->debug) {
                $this->outdebug($this->resp);
            }
            if (substr($this->resp, 0, $return_lenth) != $return_code) {
                $this->err_str = $command . " 命令服务器返回无效:" . $this->resp;
                return false;
            } else {
                return true;
            }
        }
    }

    //这个方法可以接受三个参数: $command--> 发送给服务器的命令; $return_lenth,$return_code ，
    //指定从服务器的返回中取多长的值做为命令返回的标识以及这个标识的正确值是什么。对于一般的pop操作来说，
    //如果服务器的返回第一个字符为"+"，则可以认为命令是正确执行了。也可以用前面提到过的三个字符"+OK"做为判断的标识。
    //下面介绍的几个方法则可以按照前述收取信件的对话去理解，因为有关的内容已经在前面做了说明，因此下面的方法不做详细的说明，
    //请参考其中的注释：
    //发送用户名及密码，登录到服务器
    public function Login($user, $password)
    {
        if ($this->state != "AUTHORIZATION") {
            $this->err_str = "还没有连接到服务器或状态不对";
            return false;
        }
        //服务器是否采用APOP用户认证
        if (!$this->apop) {
            if (!$this->command("USER $user", 3, "+OK")) return false;
            if (!$this->command("PASS $password", 3, "+OK")) return false;
        } else {
            if (!$this->command("APOP $user " . md5($this->greeting . $password), 3, "+OK")) return false;
        }
        $this->state = "TRANSACTION"; // 用户认证通过，进入传送模式
        return true;
    }

    // 对应着stat命令，取得总的邮件数与总的大小
    public function stat()
    {
        if ($this->state != "TRANSACTION") {
            $this->err_str = "还没有连接到服务器或没有成功登录";
            return false;
        }
        if (!$this->command("STAT", 3, "+OK")) {
            return false;
        } else {
            $this->resp = strtok($this->resp, " ");
            $this->messages = strtok(" "); // 取得邮件总数
            $this->size = strtok(" "); //取得总的字节大小
            return true;
        }
    }

    //对应的是LIST命令，取得每个邮件的大小及序号。一般来说用到的是List命令，如果指定了$uni_id ，则使用UIDL命令，
    //返回的是每个邮件的标识符，事实上，这个标识符一般是没有什么用的。取得的各个邮件的大小返回到类的内部变量mail_list这个二维数组里。
    public function listmail($mess = null, $uni_id = null)
    {
        if ($this->state != "TRANSACTION") {
            $this->err_str = "还没有连接到服务器或没有成功登录";
            return false;
        }
        if ($uni_id) {
            $command = "UIDL ";
        } else {
            $command = "LIST ";
        }
        if ($mess) {
            $command .= $mess;
        }
        if (!$this->command($command, 3, "+OK")) {
            return false;
        } else {
            $i = 0;
            $this->mail_list = array();
            $this->getresp();
            while ($this->resp != ".") {
                $i++;
                if ($this->debug) {
                    $this->outdebug($this->resp);
                }
                if ($uni_id) {
                    $this->mail_list[$i][num] = strtok($this->resp, " ");
                    $this->mail_list[$i][size] = strtok(" ");
                } else {
                    $this->mail_list[$i]["num"] = intval(strtok($this->resp, " "));
                    $this->mail_list[$i]["size"] = intval(strtok(" "));
                }
                $this->getresp();
            }
            return true;
        }
    }

    // 取得邮件的内容，$num是邮件的序号，$line是指定共取得正文的多少行。有些时候，如邮件比较大而我们只想先查看邮件的主题时
    //是必须指定行数的。默认值$line=-1，即取回所有的邮件内容，取得的内容存放到内部变量$head，$body两个数组里，
    //数组里的每一个元素对应的是邮件源代码的一行。
    public function getmail($num = 1, $line = -1)
    {
        if ($this->state != "TRANSACTION") {
            $this->err_str = "不能收取信件，还没有连接到服务器或没有成功登录";
            return false;
        }
        if ($line < 0) {
            $command = "RETR $num";
        } else {
            $command = "TOP $num $line";
        }
        if (!$this->command("$command", 3, "+OK")) {
            return false;
        } else {
            $this->getresp();
            $is_head = true;
            // . 号是邮件结束的标识
            while ($this->resp != ".") {
                if ($this->debug) {
                    $this->outdebug($this->resp);
                }
                if (substr($this->resp, 0, 1) == ".") {
                    $this->resp = substr($this->resp, 1, strlen($this->resp) - 1);
                }
                // 邮件头与正文部分的是一个空行
                if (trim($this->resp) == "") {
                    $is_head = false;
                }
                if ($is_head) {
                    $this->head[] = $this->resp;
                } else {
                    $this->body[] = $this->resp;
                }
                $this->getresp();
            }
            return true;
        }
    }

    // 删除指定序号的邮件，$num 是服务器上的邮件序号
    public function dele($num)
    {
        if ($this->state != "TRANSACTION") {
            $this->err_str = "不能删除远程信件，还没有连接到服务器或没有成功登录";
            return false;
        }
        if (!$num) {
            $this->err_str = "删除的参数不对";
            return false;
        }
        if ($this->command("DELE $num ", 3, "+OK")) {
            return true;
        } else {
            return false;
        }
    }

    //通过以上几个方法，我们已经可以实现邮件的查看、收取、删除的操作，不过别忘了最后要退出，并关闭与服务器的连接，调用下面的这个方法：
    public function Close()
    {
        if ($this->connection != 0) {
            if ($this->state == "TRANSACTION") {
                $this->command("QUIT", 3, "+OK");
            }
            fclose($this->connection);
            $this->connection = 0;
            $this->state = "DISCONNECTED";
        }
    }

    public function decode_mime($string)
    {
        $pos = strpos($string, '=?');
        if (!is_int($pos)) {
            return $string;
        }
        $preceding = substr($string, 0, $pos); // save any preceding text
        $search = substr($string, $pos + 2); /* the mime header spec says this is the longest a single encoded word can be */
        $d1 = strpos($search, '?');
        if (!is_int($d1)) {
            return $string;
        }
        $charset = substr($string, $pos + 2, $d1); //取出字符集的定义部分
        $search = substr($search, $d1 + 1); //字符集定义以后的部分＝>$search;
        $d2 = strpos($search, '?');
        if (!is_int($d2)) {
            return $string;
        }
        $encoding = substr($search, 0, $d2); ////两个?　之间的部分编码方式　：ｑ　或　ｂ　
        $search = substr($search, $d2 + 1);
        $end = strpos($search, '?='); //$d2+1 与 $end 之间是编码了　的内容：=> $endcoded_text;
        if (!is_int($end)) {
            return $string;
        }
        $encoded_text = substr($search, 0, $end);
        $rest = substr($string, (strlen($preceding . $charset . $encoding . $encoded_text) + 6)); //+6 是前面去掉的　=????=　六个字符
        switch ($encoding) {
            case 'Q':
            case 'q':
                //$encoded_text = str_replace(‘_‘, ‘%20‘, $encoded_text);
                //$encoded_text = str_replace(‘=‘, ‘%‘, $encoded_text);
                //$decoded = urldecode($encoded_text);
                $decoded = quoted_printable_decode($encoded_text);
                if (strtolower($charset) == 'windows - 1251') {
                    $decoded = convert_cyr_string($decoded, 'w', 'k');
                }
                break;
            case 'B':
            case 'b':
                $decoded = base64_decode($encoded_text);
                if (strtolower($charset) == 'windows - 1251') {
                    $decoded = convert_cyr_string($decoded, 'w', 'k');
                }
                $decoded = iconv($charset, "utf-8", $decoded);
                break;
            default:
                $decoded = '=?' . $charset . '?' . $encoding . '?' . $encoded_text . '?=';
                break;
        }
        return $preceding . $decoded . $this->decode_mime($rest);
    }
}

////参数1：为链接地址，参数2：为端口号，参数3为过载时间
//$rec = new pop3($host, 995, 2);
//
////打开
//if (!$rec->open()) {
//    die($rec->err_str);
//}
////登录
//if (!$rec->login($user, $pass)) {
//    die($rec->err_str);
//}
////读取
//if (!$rec->stat()) {
//    die($rec->err_str);
//}
////邮件数量
//if ($rec->messages > 0) {
//    if (!$rec->listmail()) {
//        die($rec->err_str);
//    }
//    //定义邮件头内容--邮件主体内容数组
//    $head_data = $body_data = $mail_head = $mail_content = [];
//    //读取10封邮件
//    $n = $m = 0;
//    for ($j = $rec->messages; $j > $rec->messages - 10; $j--) {
//        $have_mail = $rec->getmail($j);
//        if ($have_mail) {
//            $head_data[] = $rec->head;
//            $body_data[] = $rec->body;
//            $rec->head = null;
//            $rec->body = null;
//        }
//    }
//    //邮件主题列表
//    foreach ($head_data as $key => $value) {
//        foreach ($value as $k => $v) {
//            //邮件发送时间
//            if (strpos($v, '+0800 (CST)') !== false) {
//                $time = str_replace('+0800 (CST)', '', $v);
//                $time = trim($time);
//                $time = explode(',', $time);
//                $time = trim($time[1]);
//                $time = explode(' ', $time);
//                switch ($time[1]) {
//                    case 'Jan':
//                        $time[1] = '01';
//                        break;
//                    case 'Feb':
//                        $time[1] = '02';
//                        break;
//                    case 'Mar':
//                        $time[1] = '03';
//                        break;
//                    case 'Apr':
//                        $time[1] = '04';
//                        break;
//                    case 'May':
//                        $time[1] = '05';
//                        break;
//                    case 'Jun':
//                        $time[1] = '06';
//                        break;
//                    case 'Jul':
//                        $time[1] = '07';
//                        break;
//                    case 'Aug':
//                        $time[1] = '08';
//                        break;
//                    case 'Sep':
//                        $time[1] = '09';
//                        break;
//                    case 'Oct':
//                        $time[1] = '10';
//                        break;
//                    case 'Nov':
//                        $time[1] = '11';
//                        break;
//                    case 'Dec':
//                        $time[1] = '12';
//                        break;
//                }
//                $time[0] = $time[0] < 10 ? '0' . $time[0] : $time[0];
//                $mail_head[$n]['send_time'] = $time[2] . '-' . $time[1] . '-' . $time[0] . ' ' . $time[3];
//            }
//            //邮件发送人
//            if (strpos($v, 'From: ') !== false) {
//                $from = str_replace('From: ', '', $v);
//                //$from = $rec->decode_mime($from);
//                $from = trim($from, '>');
//                $from_arr = explode('<', $from);
//                if ($from_arr) {
//                    $mail_head[$n]['form'] = $from_arr[1];
//                } else {
//                    $mail_head[$n]['form'] = $from;
//                }
//            }
//            //邮件主题
//            if (strpos($v, "Subject: ") !== false) {
//                $subjec = '';
//                $subjec = str_replace('Subject: ', '', $v);
//                $subjec = $rec->decode_mime($subjec);
//                if ($value[$k + 1] && strpos($value[$k + 1], ": ") === false && strpos($value[$k + 1], '=?')) {
//                    $subjec1 = trim($value[$k + 1]);
//                    $subjec1 = $rec->decode_mime($subjec1);
//                    $subjec .= $subjec1;
//                }
//                if ($value[$k + 2] && strpos($value[$k + 2], ": ") === false && strpos($value[$k + 2], '=?')) {
//                    $subjec2 = trim($value[$k + 2]);
//                    $subjec2 = $rec->decode_mime($subjec2);
//                    $subjec .= $subjec2;
//                }
//                if ($value[$k + 3] && strpos($value[$k + 3], ": ") === false && strpos($value[$k + 3], '=?')) {
//                    $subjec3 = trim($value[$k + 3]);
//                    $subjec3 = $rec->decode_mime($subjec3);
//                    $subjec .= $subjec3;
//                }
//                if ($value[$k + 4] && strpos($value[$k + 4], ": ") === false && strpos($value[$k + 4], '=?')) {
//                    $subjec4 = trim($value[$k + 4]);
//                    $subjec4 = $rec->decode_mime($subjec4);
//                    $subjec .= $subjec4;
//                }
//                if ($value[$k + 5] && strpos($value[$k + 5], ": ") === false && strpos($value[$k + 5], '=?')) {
//                    $subjec5 = trim($value[$k + 5]);
//                    $subjec5 = $rec->decode_mime($subjec5);
//                    $subjec .= $subjec5;
//                }
//                if ($value[$k + 6] && strpos($value[$k + 6], ": ") === false && strpos($value[$k + 6], '=?')) {
//                    $subjec6 = trim($value[$k + 6]);
//                    $subjec6 = $rec->decode_mime($subjec6);
//                    $subjec .= $subjec6;
//                }
//                $mail_head[$n]['subject'] = $subjec;
//            }
//        }
//        $n++;
//    }
//    //邮件内容列表
//    foreach ($body_data as $key => $value) {
//        $start = $end = 0;
//        foreach ($value as $k => $v) {
//            if (strpos($v, 'Content-Transfer-Encoding: base64') !== false) {
//                $start = $k + 1;
//            }
//            if (strpos($v, '------=') !== false && $start > 0) {
//                $end = $k;
//                break;
//            }
//        }
//        $body_str = '';
//        for ($i = $start; $i < $end; $i++) {
//            $body_str .= $value[$i];
//        }
//        if ($body_str) {
//            $content = base64_decode($body_str);
//            $content = iconv('gb18030', "utf-8", $content);
//            $mail_content[$m]['content'] = $content;
//        } else {
//            $mail_content[$m]['content'] = '';
//        }
//        $m++;
//    }
//
//    $wx_url = "https://api.weixin.qq.com/cgi-bin/message/custom/send?access_token=ACCESS_TOKEN";
//    foreach ($mail_content as $k=>$v){
//
//    }
//    var_dump($mail_head);
//    var_dump($mail_content);
//    exit;
//} else {
//    $rec->close();
//}
