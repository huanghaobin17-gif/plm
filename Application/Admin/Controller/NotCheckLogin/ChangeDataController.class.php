<?php
namespace Admin\Controller\NotCheckLogin;
use Admin\Model\BusinessControlModel;
use think\Controller;

class ChangeDataController extends Controller
{
    //验证数据签名
    public function getSign($method,$requestTime,$enterCode,$signKey,$data='')
    {
        return MD5($method.$signKey.$enterCode.$data.$requestTime);
    }

    public function index()
    {
        Vendor('TecevApi.TecevApi');
        $api = new \TecevApi();
        //接收参数进行数据校验并跳转到特定方法进行操作
        $get = $_GET;
        //验证数据签名是否匹配
        $signed = $this->getSign($get['method'],$get['requestTime'],$get['enterCode'],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']],$get['data']);
        if($signed != $get['signed']){
            //签名验证不通过
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
            echo json_encode(array('resultCode'=>2004,'resultMsg'=>'无效的签名！','signed'=>$signed,'requestTime'=>$requestTime));
        }
        if(!$get['data']){
            //数据为空
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
            echo json_encode(array('resultCode'=>2004,'resultMsg'=>'无效的数据！','signed'=>$signed,'requestTime'=>$requestTime));
        }
        $method = $get['method'];
        switch($method){
            case 'createBusiness':
                $this->createBusiness($get);
                break;
            case 'followBusiness':
                $this->followBusiness($get);
                break;
            case 'editBusiness':
                $this->editBusinessSync($get);
                break;
            case 'procureInfo':
                $this->procureInfo($get);
                break;
        }
    }

    /**
     * Notes: 同步创建业务
     * @param $get array 要创建的业务数据
     * return json
     */
    private function createBusiness($get)
    {
        Vendor('TecevApi.TecevApi');
        $api = new \TecevApi();
        //对数据进行json解码和base64解码
        $get = $this->formatRes($get);
        //新增业务数据
        $businessModel = new BusinessControlModel();
        $contid = $businessModel->insertData('business_control',$get['data']['business']);
        if(!$contid){
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
            echo json_encode(array('resultCode'=>2005,'resultMsg'=>'同步数据失败！','signed'=>$signed,'requestTime'=>$requestTime));
        }
        //新增物料数据
        foreach ($get['data']['materiel'] as $k=>$v){
            $get['data']['materiel'][$k]['contid'] = $contid;
        }
        $materid = $businessModel->insertDataALL('business_materiel',$get['data']['materiel']);
        $requestTime = $api->getRequestTime();
        $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
        echo json_encode(array('resultCode'=>200,'resultMsg'=>'同步数据成功！','signed'=>$signed,'requestTime'=>$requestTime));
    }

    /**
     * Notes: 同步跟进数据
     * @param $get array 要跟进的数据
     * return json
     */
    private function followBusiness($get)
    {
        Vendor('TecevApi.TecevApi');
        $api = new \TecevApi();
        //对数据进行json解码和base64解码
        $get = $this->formatRes($get);
        //跟进业务数据
        $businessModel = new BusinessControlModel();
        $res = $businessModel->updateData('business_control',$get['data']['business'],$get['data']['where']);
        if($res){
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
            echo json_encode(array('resultCode'=>200,'resultMsg'=>'跟进业务成功！','signed'=>$signed,'requestTime'=>$requestTime));
        }else{
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
            echo json_encode(array('resultCode'=>2006,'resultMsg'=>'跟进业务失败！','signed'=>$signed,'requestTime'=>$requestTime));
        }
    }

    /**
     * Notes: 同步修改数据
     * @param $get array 要修改的数据
     * return json
     */
    private function editBusinessSync($get)
    {
        Vendor('TecevApi.TecevApi');
        $api = new \TecevApi();
        //对数据进行json解码和base64解码
        $get = $this->formatRes($get);
        $businessModel = new BusinessControlModel();
        //查询总部对应维护的contid
        $businessInfo = $businessModel->DB_get_one('business_control','contid',$get['data']['where']);
        //修改业务数据
        $res = $businessModel->updateData('business_control',$get['data']['business'],$get['data']['where']);
        if($res){
            $matid = [];
            foreach ($get['data']['materiel'] as $k=>$v){
                $data['title'] = $v['title'];
                $data['model'] = $v['model'];
                $data['is_depot'] = $v['is_depot'];
                $data['num'] = (int)$v['num'];
                $data['price'] = (float)$v['price'];
                $data['total_price'] = $data['num']*$data['price'];
                $data['unit'] = $v['unit'];
                $data['factory'] = $v['factory'];
                $data['guarantee_date'] = $v['guarantee_date'];
                $data['is_invoice'] = 0;
                if($v['invoice_type']){
                    $data['is_invoice'] = 1;
                }
                $data['invoice_type'] = $v['invoice_type'];
                $data['invoice_rate'] = $v['invoice_rate'];
                //查询该物料是否存在，存在则修改，不存在则新增
                $matInfo = $businessModel->DB_get_one('business_materiel','matid',array('contid'=>$businessInfo['contid'],'project_matid'=>$v['project_matid']));
                if($matInfo['matid']){
                    //存在，修改原有数据
                    $businessModel->updateData('business_materiel',$data,array('contid'=>$businessInfo['contid'],'project_matid'=>$v['project_matid']));
                }else{
                    //不存在，新增数据
                    $data['contid'] = $businessInfo['contid'];
                    $data['project_matid'] = $v['project_matid'];
                    $businessModel->insertData('business_materiel',$data);
                }
                $matid[] = $v['project_matid'];
            }
            //查询原有物料数据，与新数据做对比
            $oldMatids = $businessModel->DB_get_one('business_materiel','group_concat(project_matid) as matid',array('contid'=>$businessInfo['contid']));
            $oldMatidsArr = explode(',',$oldMatids['matid']);
            $delArr = array_diff($oldMatidsArr, $matid);
            if($delArr){
                //删除对应的matid物料
                $businessModel->deleteData('business_materiel',array('contid'=>$businessInfo['contid'],'project_matid'=>array('in',$delArr)));
            }
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
            echo json_encode(array('resultCode'=>200,'resultMsg'=>'修改业务成功！','signed'=>$signed,'requestTime'=>$requestTime));
        }else{
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_ENTER_CODE')[$get['appKey']],C('PROJECT_ACCOUNT_PASSWORD')[$get['appKey']]);
            echo json_encode(array('resultCode'=>2007,'resultMsg'=>'修改业务失败！','signed'=>$signed,'requestTime'=>$requestTime));
        }
    }

    /**
     * Notes: 同步采购数据
     * @param $get array 要跟进的数据
     * return json
     */
    private function procureInfo($get)
    {
        Vendor('TecevApi.TecevApi');
        $api = new \TecevApi();
        //对数据进行json解码和base64解码
        $get = $this->formatRes($get);
        //对接采购信息数据
        $businessModel = new BusinessControlModel();
        $res = $businessModel->updateData('business_control',$get['data']['business'],$get['data']['where']);
        if($res){
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_CODE'),$api->signKey);
            echo json_encode(array('resultCode'=>200,'resultMsg'=>'采购对接同步成功！','signed'=>$signed,'requestTime'=>$requestTime));
        }else{
            $requestTime = $api->getRequestTime();
            $signed = $this->getSign($get['method'],$requestTime,C('PROJECT_CODE'),$api->signKey);
            echo json_encode(array('resultCode'=>2008,'resultMsg'=>'采购对接同步失败！','signed'=>$signed,'requestTime'=>$requestTime));
        }
    }

    //格式化数据
    private function formatRes($res)
    {
        if($res['data']) {
            $data = base64_decode($res['data']);
            $data = json_decode($data, true);
            $res['data'] = $data;
        }else{
            $res['data'] = '';
        }
        return $res;
    }

}