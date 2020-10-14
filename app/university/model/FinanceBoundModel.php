<?php
/**
 * Created by PhpStorm.
 * User: ElvesLu
 * Date: 2020/8/27
 * Time: 10:47
 */

namespace app\university\model;

use think\Model;
use tree\Tree;
use app\university\model\FinanceModel;

class FinanceBoundModel extends Model
{
    /**
     * 分类树形结构
     * @param int    $currentIds
     * @param string $tpl
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminCategoryTableTree($currentIds = 0)
    {
        //店铺状态开启
        $categories = $this->select()->toArray();

        return $this->getTree($currentIds,$categories);
    }

    public function getTree($currentIds = 0, $categories){
        $tree       = new Tree();
        $tree->icon = ['&nbsp;&nbsp;│', '&nbsp;&nbsp;├─', '&nbsp;&nbsp;└─'];
        $tree->nbsp = '&nbsp;&nbsp;';
        if (!is_array($currentIds)) {
            $currentIds = [$currentIds];
        }

        foreach ($categories as &$item) {
            $item['checked'] = in_array($item['id'], $currentIds) ? "checked" : "";
            $item['selected'] = in_array($item['id'], $currentIds) ? "selected" : "";
        }
        return $categories;
    }

    //进出账
    public function doFinance($finance_id,$amount,$type,$memo = ''){

        $save_data = [];
        $save_data['bound_id'] = $this->createBoundId();
        $save_data['money'] = $amount;
        $save_data['amount'] = $amount;
        $save_data['finance_id'] = $finance_id;
        $save_data['type'] = $type;
        $save_data['memo'] = $memo;
        $save_data['create_time'] = time();

        $flg = $this->save($save_data);
        if($flg){
            $financeModel = new FinanceModel();
            $finance_info = $financeModel->where(['id'=>$finance_id])->find();
            if($type == 'inBound'){
                //入账
                $update_amount = $finance_info['amount'] + $amount;
                $update_collection_amount = $finance_info['collection_amount'] - $amount;
                $is_update = $financeModel->where(['id'=>$finance_id])->update(['amount'=>$update_amount,'collection_amount'=>$update_collection_amount]);
            }else{
                //出账
                $update_payment_slip = $finance_info['payment_slip'] + $amount;
                $is_update = $financeModel->where(['id'=>$finance_id])->update(['payment_slip'=>$update_payment_slip]);
            }
            if($is_update){
                return true;
            }else{
                return false;
            }
        }else{
            return false;
        }

    }


    public function createBoundId(){

        $bound_id = $this->generatorId();

        if($bound_id){
            return $bound_id;
        }else{
            return $this->createBoundId();
        }
    }

    public function generatorId(){
        //16位支付单号 时间戳+6位随机数
        $bound_id = time();
        $bound_id .= rand(111111,999999);

        $res = $this->where('bound_id',$bound_id)->find();

        if(empty($res)){
            return $bound_id;
        }else{
            return false;
        }
    }
}