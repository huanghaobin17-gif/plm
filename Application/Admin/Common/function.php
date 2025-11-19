<?php
function exportExcel($expTitle, $expCellName, $expTableData)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $expTitle);
    $fileName = session('loginAccount') . date('_YmdHis');
    $cellNum  = count($expCellName);
    $dataNum  = count($expTableData);
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new PHPExcel();
    $cellName    = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
    ];
    $objPHPExcel->getActiveSheet(0)->mergeCells('A1:' . $cellName[$cellNum - 1] . '1');
    $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', $expTitle . '  Export time:' . date('Y-m-d H:i:s'));
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue($cellName[$i] . '2', $expCellName[$i][1]);
    }
    // Miscellaneous glyphs, UTF-8
    for ($i = 0; $i < $dataNum; $i++) {
        for ($j = 0; $j < $cellNum; $j++) {
            $objPHPExcel->getActiveSheet(0)->setCellValue($cellName[$j] . ($i + 3),
                $expTableData[$i][$expCellName[$j][0]]);
        }
    }

    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xls"');
    header("Content-Disposition:attachment;filename=$fileName.xls");
    $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
    $objWriter->save('php://output');
    exit;
}

function Excel(
    $sheetTitle = 'Worksheet',
    $xlsName,
    $xlsCell,
    $width = [],
    $color = [],
    $FormatListColumn = [],
    $FormatListColumnValue = [],
    $FormatDateColumn = [],
    $FormatDateColumnValue = [],
    $descSheet = []
) {
    $xlsTitle = iconv('utf-8', 'gb2312', $xlsName);//文件名称
    $fileName = $xlsName;//or $xlsTitle 文件名称可根据自己情况设定
    $cellNum  = count($xlsCell);
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $cellName = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
    ];
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        //左右居中对齐
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        if ($width[$xlsCell[$i]]) {
            //设置单元格宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
        }
        if ($color[$xlsCell[$i]]) {
            //设置单元格必填行字体颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->getColor()->setARGB($color[$xlsCell[$i]]);
        }
    }
    if ($FormatListColumn && $FormatListColumnValue) {
        for ($i = 0; $i < $cellNum; $i++) {
            if (in_array($xlsCell[$i], $FormatListColumn)) {
                for ($j = 2; $j <= 2000; $j++) {
                    $str = '"' . $FormatListColumnValue[$xlsCell[$i]] . '"';
                    $objPHPExcel->getActiveSheet()
                        ->getCell($cellName[$i] . $j)
                        ->getDataValidation()
                        ->setType(PHPExcel_Cell_DataValidation::TYPE_LIST)
                        ->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('您输入的值不在下拉框列表内.')
                        ->setPromptTitle($xlsCell[$i])
                        ->setFormula1($str);
                }
            }
        }
    }
    if ($xlsName == 'repair') {
        //日期栏加入批注
        $addDateRemark = ['D1', 'Q1', 'V1'];
        foreach ($addDateRemark as $v) {
            $objPHPExcel->getActiveSheet()->getComment($v)->getText()->createTextRun("\r\n");      //添加更多批注
            $objPHPExcel->getActiveSheet()->getComment($v)->getText()->createTextRun('日期填写格式为：XXXX-XX-XX');
            $objPHPExcel->getActiveSheet()->getComment($v)->setWidth('100pt');      //设置批注显示的宽高
            $objPHPExcel->getActiveSheet()->getComment($v)->setHeight('100pt');
            $objPHPExcel->getActiveSheet()->getComment($v)->setMarginLeft('150pt');
            $objPHPExcel->getActiveSheet()->getComment($v)->getFillColor()->setRGB('EEEEEE');      //设置背景色
        }
        //日期栏加入批注
        $addDateTimeRemark = ['E1', 'J1', 'P1', 'R1'];
        foreach ($addDateTimeRemark as $v) {
            $objPHPExcel->getActiveSheet()->getComment($v)->getText()->createTextRun("\r\n");      //添加更多批注
            $objPHPExcel->getActiveSheet()->getComment($v)->getText()->createTextRun('时间填写格式为：XX:XX');
            $objPHPExcel->getActiveSheet()->getComment($v)->setWidth('100pt');      //设置批注显示的宽高
            $objPHPExcel->getActiveSheet()->getComment($v)->setHeight('100pt');
            $objPHPExcel->getActiveSheet()->getComment($v)->setMarginLeft('150pt');
            $objPHPExcel->getActiveSheet()->getComment($v)->getFillColor()->setRGB('EEEEEE');      //设置背景色
        }
        //故障问题
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun("\r\n");      //添加更多批注
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun('故障问题填写内容请参考：');
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun("\r\n");      //添加更多批注
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun('维修管理 - 模块配置 - 故障类型设置中的故障问题');
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun("\r\n");      //添加更多批注
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun('请注意填写的故障问题必须与系统中的故障问题一致');
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun("\r\n");      //添加更多批注
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun('如多个故障问题参考格式为：');
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun("\r\n");      //添加更多批注
        $objPHPExcel->getActiveSheet()->getComment('G1')->getText()->createTextRun('故障问题1&故障问题2&故障问题3');
        $objPHPExcel->getActiveSheet()->getComment('G1')->setWidth('200pt');      //设置批注显示的宽高
        $objPHPExcel->getActiveSheet()->getComment('G1')->setHeight('200pt');
        $objPHPExcel->getActiveSheet()->getComment('G1')->setMarginLeft('150pt');
        $objPHPExcel->getActiveSheet()->getComment('G1')->getFillColor()->setRGB('EEEEEE');      //设置背景色
    }
    if ($FormatDateColumn && $FormatDateColumnValue) {
        for ($i = 0; $i < $cellNum; $i++) {
            if (in_array($xlsCell[$i], $FormatDateColumn)) {
                for ($j = 2; $j <= 2000; $j++) {
                    $str1 = '"' . $FormatDateColumnValue[$xlsCell[$i]][0] . '"';
                    $str2 = '"' . $FormatDateColumnValue[$xlsCell[$i]][1] . '"';
                    $objPHPExcel->getActiveSheet()->getCell($cellName[$i] . $j)->getDataValidation()
                        ->setType(PHPExcel_Cell_DataValidation::TYPE_DATE)
                        ->setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
                        ->setAllowBlank(false)
                        ->setShowInputMessage(true)
                        ->setShowErrorMessage(true)
                        ->setShowDropDown(true)
                        ->setErrorTitle('输入的值有误')
                        ->setError('请输入合理的日期')
                        ->setPromptTitle($xlsCell[$i])
                        ->setFormula1($str1)
                        ->setFormula2($str2);
                }
            }
        }

    }
    if ($descSheet) {
        $i = 1;
        foreach ($descSheet as $k => $v) {
            //创建一个新的工作空间(sheet)
            $objPHPExcel->createSheet();
            $objPHPExcel->setactivesheetindex($i);
            $i++;
            if ($k == 'category') {
                $objPHPExcel->getActiveSheet()->setTitle('设备分类列表说明');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '分类编号');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '分类名称（红色字体为父分类）');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                //写入多行数据
                foreach ($v as $k1 => $v1) {
                    /* @func 设置列 */
                    if ($v1['parentid'] == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($k1 + 2))->getFont()->getColor()->setARGB('FF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($k1 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    $objPHPExcel->getactivesheet()->setcellvalue('A' . ($k1 + 2), $v1['catenum']);
                    $objPHPExcel->getactivesheet()->setcellvalue('B' . ($k1 + 2), $v1['category']);
                }
            } elseif ($k == 'department') {
                $objPHPExcel->getActiveSheet()->setTitle('科室列表说明');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '科室编号');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '科室名称');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
                //写入多行数据
                foreach ($v as $k2 => $v2) {
                    /* @func 设置列 */
                    $objPHPExcel->getactivesheet()->setcellvalue('A' . ($k2 + 2), $v2['departnum']);
                    $objPHPExcel->getactivesheet()->setcellvalue('B' . ($k2 + 2), $v2['department']);
                }
            } elseif ($k == 'repair') {
                $objPHPExcel->getActiveSheet()->setTitle('配件列表');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '设备编号');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '绑定维修单序号');
                $objPHPExcel->getactivesheet()->setcellvalue('C1', '配件/服务名称');
                $objPHPExcel->getactivesheet()->setcellvalue('D1', '配件型号');
                $objPHPExcel->getactivesheet()->setcellvalue('E1', '单价');
                $objPHPExcel->getactivesheet()->setcellvalue('F1', '数量');
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('G1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('C1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('F1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getComment('B1')->getText()->createTextRun('请输入维修单对应的行数');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(20);
            }
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

function exparray($arr)
{
    //import("@.ORG.UploadFile");
    $config = [
        'exts'     => ['xlsx', 'xls'],
        'rootPath' => "./Public/",
        'savePath' => 'Uploads/',
        //'autoSub'    =>    true,
        'subName'  => ['date', 'Ymd'],
    ];
    $upload = new \Think\Upload($config);
    if (!$info = $upload->upload()) {
        dump($upload->getError());
    } /*else {
        //$info = $upload->getUploadFileInfo();

        }
        */
    vendor("PHPExcel.PHPExcel");
    $file_name     = $upload->rootPath . $info['import']['savepath'] . $info['import']['savename'];
    $objReader     = \PHPExcel_IOFactory::createReader('Excel2007');
    $objPHPExcel   = $objReader->load($file_name, $encode = 'utf-8');
    $sheet         = $objPHPExcel->getSheet(0);
    $highestRow    = $sheet->getHighestRow(); // 取得总行数
    $highestColumn = $sheet->getHighestColumn();// 取得总列数
    if (strlen($highestColumn) == 2) {
        $num = ord(substr($highestColumn, 1)) - 39;
    } else {
        $num = ord($highestColumn) - 65;
    }
    $string = "";
    $array  = [];
    for ($j = 0; $j <= $num; $j++) {
        for ($i = 2; $i <= $highestRow; $i++) {
            if ($j <= 25) {
                $string = chr($j + 65);
            } else {
                $string = 'A' . chr($j + 39);
            }
            $cell = $objPHPExcel->getActiveSheet()->getCell($string . $i)->getValue();
            if (is_object($cell)) {
                $array[$j][$i - 2] = $cell->__toString();
            } else {
                if ($cell != null) {
                    $array[$j][$i - 2] = $objPHPExcel->getActiveSheet()->getCell($string . $i)->getValue();
                } else {
                    $array[$j][$i - 2] = '';
                }
            }
        }
    }
    $data = [];
    foreach ($array as $k => $v) {
        $data[$arr[$k]] = $v;
    }
    return $data;
}


function del($tableName, $idName, $id)
{
    $sql = 'delete from ' . $tableName . ' where ' . $idName . '=' . $id;
    return $sql;
}

include('Wordmake.class.php');

function getWordDocument($content, $absolutePath = "", $isEraseLink = true)
{
    $mht = new Wordmake();
    if ($isEraseLink)
        $content = preg_replace('/<a\s*.*?\s*>(\s*.*?\s*)<\/a>/i', '$1', $content);   //去掉链接

    $images  = [];
    $files   = [];
    $matches = [];
    //这个算法要求src后的属性值必须使用引号括起来
    if (preg_match_all('/<img[.\n]*?src\s*?=\s*?[\"\'](.*?)[\"\'](.*?)\/>/i', $content, $matches)) {
        $arrPath = $matches[1];
        for ($i = 0; $i < count($arrPath); $i++) {
            $path    = $arrPath[$i];
            $imgPath = trim($path);
            if ($imgPath != "") {
                $files[] = $imgPath;
                if (substr($imgPath, 0, 7) == 'http://') {
                    //绝对链接，不加前缀
                } else {
                    $imgPath = $absolutePath . $imgPath;
                }
                $images[] = $imgPath;
            }
        }
    }
    $mht->AddContents("tmp.html", $mht->GetMimeType("tmp.html"), $content);

    for ($i = 0; $i < count($images); $i++) {
        $image = $images[$i];
        if (@fopen($image, 'r')) {
            $imgcontent = @file_get_contents($image);
            if ($content)
                $mht->AddContents($files[$i], $mht->GetMimeType($image), $imgcontent);
        } else {
            echo "file:" . $image . " not exist!<br />";
        }
    }

    return $mht->GetFile();
}


/**
 * PHP Excel导出统计表格数据和图表图片方法
 * @params1 $sheetTitle array 表格标题
 * @params2 $fileName string excel文件名称
 * @params3 $showName array 表格要显示的字段key-value模式，需要提取value出来
 * @params4 $data array 表格要显示的数据
 * author: 邓锦龙
 * 2018-01-09
 */
function exportExcelStatistics(
    $sheetTitle,
    $fileName,
    $showName,
    $data,
    $imageInfo,
    $showLastTotalRow,
    $tableHeader,
    $tips,
    $otherInfo
) {
    $xlsTitle = iconv('utf-8', 'gb2312', $fileName);//文件名称
    //excel显示的表格头$tableheader,对应的字段名称$cell
    $tableheader = [];
    $cell        = [];
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle[0]);
    //设置文本对齐方式
    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $objActSheet = $objPHPExcel->getActiveSheet();
    $cellName    = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
    ];
    //获取表格表头数据
    foreach ($showName as $k => $v) {
        $tableheader[] = $v;
        $cell[]        = $k;
    }
    $headerLength = count($tableheader);
    //合并单元格
    //设置excel顶部表格标题
    $rand = 'A1:' . $cellName[$headerLength - 1] . '1';
    $objActSheet->mergeCells($rand);
    $objActSheet->getRowDimension('1')->setRowHeight($otherInfo['titleRowHeight']);
    //写入表格标题
    $objActSheet->setCellValue('A1', $tableHeader);
    $objActSheet->getStyle('A1')->getFont()->setSize($otherInfo['titleFontSize']);
    $objActSheet->getStyle('A1')->getFont()->setBold(true);
    //数据说明-报表日期，搜索范围等
    $rand = 'A2:' . $cellName[$headerLength - 1] . '2';
    $objActSheet->mergeCells($rand);
    $objActSheet->getRowDimension('2')->setRowHeight(28);
    $objActSheet->getStyle('A2')->getAlignment()->setWrapText(true);
    //写入报表日期 搜索条件等
    $objActSheet->setCellValue('A2', $tips);
    $objActSheet->getStyle('A2')->getFont()->setSize(10);
    $startRows = 3;
    for ($i = 0; $i < $headerLength; $i++) {
        //显示的字段
        $objActSheet->setCellValue($cellName[$i] . $startRows, $tableheader[$i]);
        //设置font
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setName(iconv('gbk', 'utf-8', '宋体'));
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setSize(12);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setBold(true);
        //设置单元格宽度
        $objActSheet->getColumnDimension("$cellName[$i]")->setWidth(20);
        // 设置行高
        $objActSheet->getRowDimension($startRows)->setRowHeight(20);
        // 设置填充颜色
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->getStartColor()->setARGB('F2F2F2');
    }
    //向每行单元格插入数据
    $j = $startRows + 1;
    foreach ($data as $k => $v) {
        foreach ($cell as $key => $val) {
            $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
            if ($showLastTotalRow && $k == (count($data) - 1)) {
                $objActSheet->getStyle('A' . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
                $objActSheet->getStyle($cellName[$key] . $j)->getFont()->setBold(true);
                $objActSheet->getStyle($cellName[$key] . $j)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
            }
        }
        $objActSheet->getRowDimension($j)->setRowHeight(18);
        $j++;
    }
    //导入图片到excel
    //实例化插入图片类
    $objDrawing = new PHPExcel_Worksheet_Drawing();
    //设置图片路径,只能是本地图片
    $objDrawing->setPath($imageInfo['url']);
    //设置图片要插入的单元格
    if ($otherInfo['imagePosition'] == 'bottom') {
        $objDrawing->setCoordinates('A' . ($j + 3));
    } elseif ($otherInfo['imagePosition'] == 'right') {
        $objDrawing->setCoordinates($cellName[$headerLength + 1] . '3');
    } else {
        $objDrawing->setCoordinates('A' . ($j + 3));
    }
    //设置图片高度
    $objDrawing->setWidth($otherInfo['imageWidth']);
    $objDrawing->setHeight($otherInfo['imageHeight']);
    /*设置图片所在单元格的格式*/
    $objDrawing->setOffsetX(10);
    $objDrawing->setOffsetY(10);
    $objDrawing->setRotation(0);
    $objDrawing->getShadow()->setVisible(true);
    $objDrawing->getShadow()->setDirection(50);
    $objDrawing->setWorksheet($objPHPExcel->getActiveSheet());
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    //删除服务器上的图片文件
    unlink($imageInfo['url']);
    exit;
}

//设备模板导出-新
function exportTemplate($sheetTitle = 'Worksheet', $xlsName, $xlsCell, $width = [], $color = [], $descSheet = [])
{
    $xlsTitle = iconv('utf-8', 'gb2312', $xlsName);//文件名称
    $fileName = $xlsName;//or $xlsTitle 文件名称可根据自己情况设定
    $cellNum  = count($xlsCell);
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $cellName = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
        'BA',
    ];
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        //左右居中对齐
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        if ($width[$xlsCell[$i]]) {
            //设置单元格宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
        }
        if ($color[$xlsCell[$i]]) {
            //设置单元格必填行字体颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->getColor()->setARGB($color[$xlsCell[$i]]);
        }
    }
    if ($descSheet) {
        $i = 1;
        foreach ($descSheet as $k => $v) {
            //创建一个新的工作空间(sheet)
            $objPHPExcel->createSheet();
            $objPHPExcel->setactivesheetindex($i);
            $i++;
            if ($k == 'category') {
                $objPHPExcel->getActiveSheet()->setTitle('设备分类列表说明');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '医院代码');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '分类编号');
                $objPHPExcel->getactivesheet()->setcellvalue('C1', '分类名称（红色字体为父分类，请填写子类编号或名称）');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
                //写入多行数据
                foreach ($v as $k1 => $v1) {
                    /* @func 设置列 */
                    if ($v1['parentid'] == 0) {
                        $objPHPExcel->getActiveSheet()->getStyle('A' . ($k1 + 2))->getFont()->getColor()->setARGB('FF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('B' . ($k1 + 2))->getFont()->getColor()->setARGB('FF0000');
                        $objPHPExcel->getActiveSheet()->getStyle('C' . ($k1 + 2))->getFont()->getColor()->setARGB('FF0000');
                    }
                    $objPHPExcel->getactivesheet()->setcellvalue('A' . ($k1 + 2),
                        $descSheet['hospitals'][$v1['hospital_id']]);
                    $objPHPExcel->getactivesheet()->setcellvalue('B' . ($k1 + 2), $v1['catenum']);
                    $objPHPExcel->getactivesheet()->setcellvalue('C' . ($k1 + 2), $v1['category']);
                }
            } elseif ($k == 'department') {
                $objPHPExcel->getActiveSheet()->setTitle('科室列表说明');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '医院代码');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '科室编号');
                $objPHPExcel->getactivesheet()->setcellvalue('C1', '科室名称（填写时可填写科室编号或名称）');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(10);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(60);
                //写入多行数据
                foreach ($v as $k2 => $v2) {
                    /* @func 设置列 */
                    $objPHPExcel->getactivesheet()->setcellvalue('A' . ($k2 + 2),
                        $descSheet['hospitals'][$v2['hospital_id']]);
                    $objPHPExcel->getactivesheet()->setcellvalue('B' . ($k2 + 2), $v2['departnum']);
                    $objPHPExcel->getactivesheet()->setcellvalue('C' . ($k2 + 2), $v2['department']);
                }
            } elseif ($k == 'repair') {
                $objPHPExcel->getActiveSheet()->setTitle('配件列表');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '设备编号');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '设备原编码');
                $objPHPExcel->getactivesheet()->setcellvalue('C1', '配件/服务名称');
                $objPHPExcel->getactivesheet()->setcellvalue('D1', '配件型号');
                $objPHPExcel->getactivesheet()->setcellvalue('E1', '单价');
                $objPHPExcel->getactivesheet()->setcellvalue('F1', '数量');
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('B1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('C1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('D1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('E1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('F1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('D1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('E1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(20);
            }
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

/**
 * PHP Excel导出设备
 * @params1 $sheetTitle array 表格标题
 * @params2 $fileName string excel文件名称
 * @params3 $showName array 表格要显示的字段key-value模式，需要提取value出来
 * @params4 $data array 表格要显示的数据
 * @params5 $tableHeader string 表格的标题名称
 * @params6 $tips string 筛选条件
 * @params6 $otherInfo array 标题的字体大小
 */
function exportAssets($sheetTitle, $fileName, $showName, $data)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $fileName);//文件名称
    //excel显示的表格头$tableheader,对应的字段名称$cell
    $tableheader = [];
    $cell        = [];
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle[0]);
    //设置文本对齐方式
    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $objActSheet = $objPHPExcel->getActiveSheet();
    $cellName    = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
        'BA',
        'BB',
    ];
    //获取表格表头数据
    foreach ($showName as $k => $v) {
        $tableheader[] = $v;
        $cell[]        = $k;
    }
    $headerLength = count($tableheader);
    $startRows    = 1;
    for ($i = 0; $i < $headerLength; $i++) {
        //显示的字段
        $objActSheet->setCellValue($cellName[$i] . $startRows, $tableheader[$i]);
        //设置font
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setName(iconv('gbk', 'utf-8', '宋体'));
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setSize(12);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setBold(true);
        //设置单元格宽度
        $objActSheet->getColumnDimension("$cellName[$i]")->setWidth(20);
        // 设置行高
        $objActSheet->getRowDimension($startRows)->setRowHeight(20);
        // 设置填充颜色
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->getStartColor()->setARGB('F2F2F2');
    }
    //向每行单元格插入数据
    $j = $startRows + 1;
    foreach ($data as $k => $v) {
        foreach ($cell as $key => $val) {
            if ($val == 'assnum') {
                //处理设备编码过长时变为科学计数法的问题
                $objActSheet->setCellValueExplicit($cellName[$key] . $j, $v[$val],
                    \PHPExcel_Cell_DataType::TYPE_STRING);
            } else {
                $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
            }
//            if($cellName[$key] == 'C'){
//                //处理设备编码过长时变为科学计数法的问题
//                $objActSheet->setCellValueExplicit($cellName[$key] . $j, $v[$val],\PHPExcel_Cell_DataType::TYPE_STRING);
//            }else{
//                $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
//            }
        }
        $j++;
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}


/**
 * PHP Excel导出统计表格数据和图表图片方法
 * @params1 $sheetTitle array 表格标题
 * @params2 $fileName string excel文件名称
 * @params3 $showName array 表格要显示的字段key-value模式，需要提取value出来
 * @params4 $data array 表格要显示的数据
 * author: 邓锦龙
 * 2018-01-09
 */
function exportExcelPlanLists($sheetTitle, $fileName, $showName, $data, $tableHeader, $tips, $otherInfo)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $fileName);//文件名称
    //excel显示的表格头$tableheader,对应的字段名称$cell
    $tableheader = [];
    $cell        = [];
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle[0]);
    //设置文本对齐方式
    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $objActSheet = $objPHPExcel->getActiveSheet();
    $cellName    = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
    ];
    //获取表格表头数据
    foreach ($showName as $k => $v) {
        $tableheader[] = $v;
        $cell[]        = $k;
    }
    $headerLength = count($tableheader);
    //合并单元格
    //设置excel顶部表格标题
    $rand = 'A1:' . $cellName[$headerLength - 1] . '1';
    $objActSheet->mergeCells($rand);
    $objActSheet->getRowDimension('1')->setRowHeight($otherInfo['titleRowHeight']);
    //写入表格标题
    $objActSheet->setCellValue('A1', $tableHeader);
    $objActSheet->getStyle('A1')->getFont()->setSize($otherInfo['titleFontSize']);
    $objActSheet->getStyle('A1')->getFont()->setBold(true);
    //数据说明-报表日期，搜索范围等
    $rand = 'A2:' . $cellName[$headerLength - 1] . '2';
    $objActSheet->mergeCells($rand);
    $objActSheet->getRowDimension('2')->setRowHeight(22);
    //写入报表日期 搜索条件等
    $objActSheet->setCellValue('A2', $tips);
    $objActSheet->getStyle('A2')->getFont()->setSize(10);
    //按级别组织数据
    $res = [];
    foreach ($data as $k => $v) {
        if ($v['patrol_level'] == 3) {
            $res[3][] = $v;
        } elseif ($v['patrol_level'] == 2) {
            $res[2][] = $v;
        } elseif ($v['patrol_level'] == 1) {
            $res[1][] = $v;
        }
    }
    $startRows = 3;
    for ($n = 3; $n >= 1; $n--) {
        if (C('PATROL_LEVEL_PM') == $n) {
            $objActSheet->setCellValue('A' . $startRows, C('PATROL_LEVEL_NAME_PM'));
            // 设置填充颜色
            $objActSheet->getStyle('A' . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objActSheet->getStyle('A' . $startRows)->getFill()->getStartColor()->setARGB('C5D9F1');
        } elseif (C('PATROL_LEVEL_XC') == $n) {
            $objActSheet->setCellValue('A' . $startRows, C('PATROL_LEVEL_NAME_XC'));
            // 设置填充颜色
            $objActSheet->getStyle('A' . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objActSheet->getStyle('A' . $startRows)->getFill()->getStartColor()->setARGB('C5D9F1');
        } elseif (C('PATROL_LEVEL_RC') == $n) {
            $objActSheet->setCellValue('A' . $startRows, C('PATROL_LEVEL_NAME_RC'));
            // 设置填充颜色
            $objActSheet->getStyle('A' . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objActSheet->getStyle('A' . $startRows)->getFill()->getStartColor()->setARGB('C5D9F1');
        }
        ++$startRows;
        for ($i = 0; $i < $headerLength; $i++) {
            //显示的字段
            $objActSheet->setCellValue($cellName[$i] . $startRows, $tableheader[$i]);
            //设置font
            $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setName(iconv('gbk', 'utf-8', '宋体'));
            $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setSize(12);
            $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setBold(true);
            if ($cellName[$i] == 'B' || $cellName[$i] == 'E') {
                //设置单元格宽度
                $objActSheet->getColumnDimension("$cellName[$i]")->setWidth(30);
            } elseif ($cellName[$i] == 'H') {
                //设置单元格宽度
                $objActSheet->getColumnDimension("$cellName[$i]")->setWidth(80);
            } else {
                //设置单元格宽度
                $objActSheet->getColumnDimension("$cellName[$i]")->setWidth(15);
            }
            // 设置行高
            $objActSheet->getRowDimension($startRows)->setRowHeight(20);
            // 设置填充颜色
            $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
            $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->getStartColor()->setARGB('F2F2F2');
        }
        //向每行单元格插入数据
        $j = $startRows + 1;
        foreach ($res[$n] as $k => $v) {
            foreach ($cell as $key => $val) {
                if ($val == 'period') {
                    $objActSheet->setcellvalue($cellName[$key] . $j, '第 ' . $v[$val] . ' 期');
                } else {
                    $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
                }
                if ($val == 'abnormalDetail') {
                    $objActSheet->getStyle($cellName[$key] . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                } else {
                    $objActSheet->getStyle($cellName[$key] . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_LEFT);
                }
                if ($v['isNormal'] == '是' && $cellName[$key] == 'G') {
                    $objActSheet->getStyle($cellName[$key] . $j)->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
                }
            }
            $j++;
            $startRows++;
        }
        $startRows = $startRows + 3;
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

//设备明细模板导出
function exportBenefitTemplate($sheetTitle = 'Worksheet', $xlsName, $xlsCell, $data, $width = [], $color = [])
{
    $xlsTitle    = iconv('utf-8', 'gb2312', $xlsName);//文件名称
    $fileName    = $xlsName;//or $xlsTitle 文件名称可根据自己情况设定
    $cell        = [];
    $tableheader = [];
    foreach ($xlsCell as $k => $v) {
        $cell[]        = $k;
        $tableheader[] = $v;
    }
    $cellNum = count($xlsCell);
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $cellName = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
    ];
    $objPHPExcel->getActiveSheet()->getStyle('B')->getNumberFormat()->setFormatCode(PHPExcel_Style_NumberFormat::FORMAT_TEXT);
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $tableheader[$i]);
        //左右居中对齐
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        if ($width[$tableheader[$i]]) {
            //设置单元格宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$tableheader[$i]]);
        }
        if ($color[$tableheader[$i]]) {
            //设置单元格必填行字体颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->getColor()->setARGB($color[$tableheader[$i]]);
        }
    }
    foreach ($data as $k => $v) {
        foreach ($cell as $key => $val) {
            $objPHPExcel->getActiveSheet()->setcellvalue($cellName[$key] . ($k + 2), $v[$val]);
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

/**
 * PHP Excel导出统计表格数据
 * @params1 $sheetTitle array 表格标题
 * @params2 $fileName string excel文件名称
 * @params3 $showName array 表格要显示的字段key-value模式，需要提取value出来
 * @params4 $data array 表格要显示的数据
 * @params5 $tableHeader string 表格的标题名称
 * @params6 $tips string 筛选条件
 * @params6 $otherInfo array 标题的字体大小
 */
function exportExcelData($sheetTitle, $fileName, $showName, $data, $tableHeader, $tips, $otherInfo)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $fileName);//文件名称
    //excel显示的表格头$tableheader,对应的字段名称$cell
    $tableheader = [];
    $cell        = [];
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle[0]);
    //设置文本对齐方式
    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $objActSheet = $objPHPExcel->getActiveSheet();
    $cellName    = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
    ];
    //获取表格表头数据
    foreach ($showName as $k => $v) {
        $tableheader[] = $v;
        $cell[]        = $k;
    }
    //
    $cell[] = 'sum';

    $headerLength = count($tableheader);
    //合并单元格
    //设置excel顶部表格标题
    $rand = 'A1:' . $cellName[$headerLength - 1] . '1';
    $objActSheet->mergeCells($rand);
    $objActSheet->getRowDimension('1')->setRowHeight($otherInfo['titleRowHeight']);
    //写入表格标题
    $objActSheet->setCellValue('A1', $tableHeader);
    $objActSheet->getStyle('A1')->getFont()->setSize($otherInfo['titleFontSize']);
    $objActSheet->getStyle('A1')->getFont()->setBold(true);
    //数据说明-报表日期，搜索范围等
    $rand = 'A2:' . $cellName[$headerLength - 1] . '2';
    $objActSheet->mergeCells($rand);
    $objActSheet->getRowDimension('2')->setRowHeight(22);
    //写入报表日期 搜索条件等
    $objActSheet->setCellValue('A2', $tips);
    $objActSheet->getStyle('A2')->getFont()->setSize(10);
    $startRows = 3;
    for ($i = 0; $i < $headerLength; $i++) {
        //显示的字段
        $objActSheet->setCellValue($cellName[$i] . $startRows, $tableheader[$i]);
        //设置font
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setName(iconv('gbk', 'utf-8', '宋体'));
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setSize(12);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setBold(true);
        //设置单元格宽度
        $objActSheet->getColumnDimension("$cellName[$i]")->setWidth(25);
        // 设置行高
        $objActSheet->getRowDimension($startRows)->setRowHeight(20);
        // 设置填充颜色
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->getStartColor()->setARGB('F2F2F2');
    }
    //向每行单元格插入数据
    $j = $startRows + 1;
    foreach ($data as $k => $v) {
        foreach ($cell as $key => $val) {
            if ($val != 'sum') {
                if ($key == 0) {
                    if ($data[$k]['sum'] != 0) {
                        $objActSheet->mergeCells($cellName[$key] . $j . ':' . $cellName[$key] . ($j + $v['sum'] - 1));
                    }
                    $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
                } else {
                    $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
                }
            }
        }
        $objActSheet->getRowDimension($j)->setRowHeight(18);
        $j++;
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}


//计量模板导出
function exportMeteringTemplate(
    $sheetTitle = 'Worksheet',
    $xlsName,
    $xlsCell,
    $width = [],
    $color = [],
    $descSheet = []
) {
    $xlsTitle = iconv('utf-8', 'gb2312', $xlsName);//文件名称
    $fileName = $xlsName;//or $xlsTitle 文件名称可根据自己情况设定
    $cellNum  = count($xlsCell);
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle);
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $cellName = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
    ];
    for ($i = 0; $i < $cellNum; $i++) {
        $objPHPExcel->getActiveSheet()->setCellValue($cellName[$i] . '1', $xlsCell[$i]);
        //左右居中对齐
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::VERTICAL_CENTER);
        $objPHPExcel->getActiveSheet()->getStyle($cellName[$i])->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
        if ($width[$xlsCell[$i]]) {
            //设置单元格宽度
            $objPHPExcel->getActiveSheet()->getColumnDimension($cellName[$i])->setWidth($width[$xlsCell[$i]]);
        }
        if ($color[$xlsCell[$i]]) {
            //设置单元格必填行字体颜色
            $objPHPExcel->getActiveSheet()->getStyle($cellName[$i] . '1')->getFont()->getColor()->setARGB($color[$xlsCell[$i]]);
        }
    }
    if ($descSheet) {
        $i = 1;
        foreach ($descSheet as $k => $v) {
            //创建一个新的工作空间(sheet)
            $objPHPExcel->createSheet();
            $objPHPExcel->setactivesheetindex($i);
            $i++;
            if ($k == 'departmentAssets') {
                $objPHPExcel->getActiveSheet()->setTitle('设备信息列表说明');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '科室名称');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '设备名称');
                $objPHPExcel->getactivesheet()->setcellvalue('C1', '规格/型号');
                $objPHPExcel->getactivesheet()->setcellvalue('D1', '单位');
                $objPHPExcel->getactivesheet()->setcellvalue('E1', '生产厂商');
                $objPHPExcel->getactivesheet()->setcellvalue('F1', '资产序列号');
                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(22);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(22);
                $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(14);
                $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(8);
                $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(30);
                $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(13);
                //写入多行数据
                foreach ($v as $k1 => $v1) {
                    $objPHPExcel->getactivesheet()->setcellvalue('A' . ($k1 + 2), $v1['department']);
                    $objPHPExcel->getactivesheet()->setcellvalue('B' . ($k1 + 2), $v1['assets']);
                    $objPHPExcel->getactivesheet()->setcellvalue('C' . ($k1 + 2), $v1['model']);
                    $objPHPExcel->getactivesheet()->setcellvalue('D' . ($k1 + 2), $v1['unit']);
                    $objPHPExcel->getactivesheet()->setcellvalue('E' . ($k1 + 2), $v1['factory']);
                    $objPHPExcel->getactivesheet()->setcellvalue('F' . ($k1 + 2), $v1['serialnum']);

                }
            } elseif ($k == 'mCategorys') {
                $objPHPExcel->getActiveSheet()->setTitle('计量分类列表说明');
                $objPHPExcel->getactivesheet()->setcellvalue('A1', '分类编号');
                $objPHPExcel->getactivesheet()->setcellvalue('B1', '分类名称');


                $objPHPExcel->getActiveSheet()->getStyle('A1')->getFont()->getColor()->setARGB('FF0000');
                $objPHPExcel->getActiveSheet()->getStyle('B1')->getFont()->getColor()->setARGB('FF0000');


                $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(15);
                $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                foreach ($v as $k2 => $v2) {
                    //写入多行数据
                    $objPHPExcel->getactivesheet()->setcellvalue('A' . ($k2 + 2), $v2['mcid']);
                    $objPHPExcel->getactivesheet()->setcellvalue('B' . ($k2 + 2), $v2['mcategory']);
                }
            }
        }
    }
    header('pragma:public');
    header('Content-type:application/vnd.ms-excel;charset=utf-8;name="' . $xlsTitle . '.xlsx"');
    header("Content-Disposition:attachment;filename=$fileName.xlsx");//attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $objWriter->save('php://output');
    exit;
}

function delDir($directory)
{//自定义函数递归的函数整个目录

    if (file_exists($directory)) {//判断目录是否存在，如果不存在rmdir()函数会出错

        if ($dir_handle = @opendir($directory)) {//打开目录返回目录资源，并判断是否成功

            while ($filename = readdir($dir_handle)) {//遍历目录，读出目录中的文件或文件夹

                if ($filename != '.' && $filename != '..') {//一定要排除两个特殊的目录
                    $subFile = $directory . "/" . $filename;//将目录下的文件与当前目录相连
                    if (is_dir($subFile)) {//如果是目录条件则成了
                        delDir($subFile);//递归调用自己删除子目录
                    }
                    if (is_file($subFile)) {//如果是文件条件则成立
                        unlink($subFile);//直接删除这个文件
                    }
                }
            }
            closedir($dir_handle);//关闭目录资源
            rmdir($directory);//删除空目录
        }
    }
}

/**
 * PHP Excel导出巡查保养记录报告excel
 * @params1 $sheetTitle array 表格标题
 * @params2 $fileName string excel文件名称
 * @params3 $showName array 表格要显示的字段key-value模式，需要提取value出来
 * @params4 $data array 表格要显示的数据
 * @params5 $tableHeader string 表格的标题名称
 * @params6 $tips string 筛选条件
 * @params6 $otherInfo array 标题的字体大小
 */
function exportPatrolReport($sheetTitle, $fileName, $showName, $data, $path)
{
    $xlsTitle = iconv('utf-8', 'gb2312', $fileName);//文件名称
    //excel显示的表格头$tableheader,对应的字段名称$cell
    $tableheader = [];
    $cell        = [];
    vendor("PHPExcel.PHPExcel");
    $objPHPExcel = new \PHPExcel();
    $objPHPExcel->getDefaultStyle()->getFont()->setName('宋体');
    $objPHPExcel->getActiveSheet()->setTitle($sheetTitle[0]);
    //设置文本对齐方式
    $objPHPExcel->getDefaultStyle()->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_CENTER);
    $objPHPExcel->getDefaultStyle()->getAlignment()->setVertical(PHPExcel_Style_Alignment::VERTICAL_CENTER);
    $objActSheet = $objPHPExcel->getActiveSheet();
    $cellName    = [
        'A',
        'B',
        'C',
        'D',
        'E',
        'F',
        'G',
        'H',
        'I',
        'J',
        'K',
        'L',
        'M',
        'N',
        'O',
        'P',
        'Q',
        'R',
        'S',
        'T',
        'U',
        'V',
        'W',
        'X',
        'Y',
        'Z',
        'AA',
        'AB',
        'AC',
        'AD',
        'AE',
        'AF',
        'AG',
        'AH',
        'AI',
        'AJ',
        'AK',
        'AL',
        'AM',
        'AN',
        'AO',
        'AP',
        'AQ',
        'AR',
        'AS',
        'AT',
        'AU',
        'AV',
        'AW',
        'AX',
        'AY',
        'AZ',
        'BA',
        'BB',
    ];
    //获取表格表头数据
    foreach ($showName as $k => $v) {
        $tableheader[] = $v;
        $cell[]        = $k;
    }
    $headerLength = count($tableheader);
    $startRows    = 1;
    for ($i = 0; $i < $headerLength; $i++) {
        //显示的字段
        $objActSheet->setCellValue($cellName[$i] . $startRows, $tableheader[$i]);
        //设置font
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setName(iconv('gbk', 'utf-8', '宋体'));
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setSize(12);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFont()->setBold(true);
        //设置单元格宽度
        $objActSheet->getColumnDimension("$cellName[$i]")->setWidth(20);
        // 设置行高
        $objActSheet->getRowDimension($startRows)->setRowHeight(20);
        // 设置填充颜色
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->setFillType(PHPExcel_Style_Fill::FILL_SOLID);
        $objActSheet->getStyle($cellName[$i] . $startRows)->getFill()->getStartColor()->setARGB('F2F2F2');
    }
    //向每行单元格插入数据
    $j = $startRows + 1;
    foreach ($data as $k => $v) {
        foreach ($cell as $key => $val) {
            if ($cellName[$key] == 'C') {
                //处理设备编码过长时变为科学计数法的问题
                $objActSheet->setCellValueExplicit($cellName[$key] . $j, $v[$val],
                    \PHPExcel_Cell_DataType::TYPE_STRING);
            } else {
                $objActSheet->setcellvalue($cellName[$key] . $j, $v[$val]);
            }
            if ($cellName[$key] == 'P') {
                $objPHPExcel->getActiveSheet()->setCellValue($cellName[$key] . $j, $v[$val]);
                $objPHPExcel->getActiveSheet()->getCell($cellName[$key] . $j)->getHyperlink()->setUrl($v[$val]);
                $objPHPExcel->getActiveSheet()->getCell($cellName[$key] . $j)->getHyperlink()->setTooltip('测试一下超链接能不能用咯');
                $objPHPExcel->getActiveSheet()->getStyle($cellName[$key] . $j)->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
            }
        }
        $j++;
    }
    //attachment新窗口打印inline本窗口打印
    $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
    $xlsTitle  = iconv('gb2312', 'utf-8', $xlsTitle);
    if (file_exists('./Public/uploads/patrol/' . $xlsTitle . '.zip')) {
        chmod('./Public/uploads/patrol/' . $xlsTitle . '.zip', 0777);
        unlink('./Public/uploads/patrol/' . $xlsTitle . '.zip');
    }
    $objWriter->save($path . '/' . $xlsTitle . '.xlsx');
    zip($path, './Public/uploads/patrol/' . $xlsTitle . '.zip');
    delDir($path);
    header("Location: http://" . C('HTTP_HOST') . '/Public/uploads/patrol/' . $xlsTitle . '.zip');
    exit;
}

/*
转编码
 */
function characet($data)
{
    if (!empty($data)) {
        $fileType = mb_detect_encoding($data, ['UTF-8', 'GBK', 'LATIN1', 'BIG5']);
        if ($fileType != 'UTF-8') {
            $data = mb_convert_encoding($data, 'utf-8', $fileType);
        }
    }
    return $data;
}

/**
 * 如果数组的某个键存在则删除这个键，并返回这个键对应的值
 *
 * @param $arr
 * @param $key
 *
 * @return mixed|null
 */
function array_pop_key(&$arr, $key)
{
    $val = null;
    if (array_key_exists($key, $arr)) {
        $val = $arr[$key];
        unset($arr[$key]);
    }
    return $val;
}

function dd(...$mixed)
{
    dump($mixed);
    exit;
}

function array_keep_keys($array, $keys)
{
    $result = [];
    foreach ($keys as $key) {
        if (isset($array[$key])) {
            $result[$key] = $array[$key];
        }
    }
    return $result;
}

/**
 * 接收文件流
 *
 * @return void
 */
function json_post()
{
    $put = file_get_contents('php://input');
    $put = json_decode($put, true);
    foreach ($put as $key => $value) {
        $_POST[$key] = $value;
    }
}

//保留中文json
function str_json_encode($data)
{
    return json_encode($data, JSON_UNESCAPED_UNICODE);
}

function has_feature($feature, $allFeature)
{
    return ($feature & $allFeature) > 0;
}

function is_mobile()
{
    if (empty($_SERVER['HTTP_USER_AGENT'])) {
        $is_mobile = false;
    } elseif (strpos($_SERVER['HTTP_USER_AGENT'], 'Mobile') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Android') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Silk/') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Kindle') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'BlackBerry') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mini') !== false
        || strpos($_SERVER['HTTP_USER_AGENT'], 'Opera Mobi') !== false) {
        $is_mobile = true;
    } else {
        $is_mobile = false;
    }
    return $is_mobile;
}