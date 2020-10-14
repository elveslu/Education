<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------

namespace app\university\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\user\model\UserModel;
use think\facade\Validate;
use app\university\model\UniversityModel;
use app\university\model\ExaminationProcessModel;
use app\university\model\ProcessLogModel;
use app\university\model\ProfessionalModel;
use app\university\model\FinanceModel;
use app\university\model\FinanceBoundModel;


class AdminExaminationController extends AdminBaseController
{

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '本站用户',
     *     'parent' => 'default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where   = [];
        $request = input('request.');

        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $where['name']    = ['like', "%$keyword%"];
        }
        $examinationProcessModel = new ExaminationProcessModel();
        $usersQuery = $examinationProcessModel->where($where)->order("order");

        $list = $usersQuery->paginate(50);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }


    //添加院校
    public function add(){
        return $this->fetch();
    }

    public function addPost(){

        $data = $this->request->param();

        $rules = [
            'name'  => 'require',
            'order'     => 'require',
            'time'     => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '流程名称必填',
            'type.require' => '排序必填',
            'type.time' => '流程时间必填',

        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $data['create_time'] = time();

        $data['time'] = strtotime($data['time']);

        $examinationProcessModel = new ExaminationProcessModel();
        $university_id = $examinationProcessModel->save($data);

        if($university_id){
            $this->success('操作成功！', url('AdminExamination/index'));
        }else{
            $this->error('操作失败！');
        }
    }


    public function edit($id){
        $examinationProcessModel = new ExaminationProcessModel();
        $data = $examinationProcessModel->where(['id'=>$id])->find();

        $data['time'] = date('Y-m-d H:i:s', $data['time']);

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function editPost(){
        $data = $this->request->param();

        $data['time'] = strtotime($data['time']);

        $id = $data['id'];

        unset($data['id']);

        $rules = [
            'name'  => 'require',
            'order'     => 'require',
            'time'     => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '流程名称必填',
            'type.require' => '排序必填',
            'type.time' => '流程时间必填',

        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $examinationProcessModel = new ExaminationProcessModel();
        $university_id = $examinationProcessModel->where(['id'=>$id])->update($data);

        if($university_id){
            $this->success('操作成功！', url('AdminExamination/index'));
        }else{
            $this->error('操作失败！');
        }
    }


    //选择流程
    public function select()
    {
        $ids                 = $this->request->param('ids');
        $selectedIds         = explode(',', $ids);


        $examinationProcessModel = new ExaminationProcessModel();
        $categoryTree = $examinationProcessModel->adminCategoryTableTree($selectedIds);

        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }

    //流程处理
    public function operate(){
        //获取流程
        $examinationProcessModel = new ExaminationProcessModel();
        $process = $examinationProcessModel->where(['use_status'=>'1'])->order('order')->select();

        $this->assign('process', $process);


        $where   = [];
        $where['user_type'] = '2';
        $request = input('request.');


        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $keywordComplex['user_login|user_nickname|user_email|mobile']    = ['like', "%$keyword%"];
        }

        if (!empty($request['process_id'])) {
            $where['current_coordinates'] = intval($request['process_id']);
            $this->assign('process_id', $request['process_id']);
        }else{
            $where['current_coordinates'] = '1';
            $this->assign('process_id', '1');
        }

        $usersQuery = new UserModel();

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();

    }

    //处理流程
    public function goOperate($id){
        //查询用户，如果是代缴费的话跳转缴费页面
        $usersQuery = new UserModel();
        $current_coordinates = $usersQuery->where(['id'=>$id])->find();
        $this->assign('data', $current_coordinates);
        if($current_coordinates['current_coordinates'] == '2'){
            //跳转缴费页
            //查询客户所选专业的价格
            $professionalModel = new ProfessionalModel();
            $price = $professionalModel->where(['id'=>$current_coordinates['major']])->field('price')->find();
            $this->assign('price', $price['price']);
            return $this->fetch('pay');
        }else{
            //跳转操作处理页
            return $this->fetch();
        }

    }


    //处理操作
    public function doOperate(){
        $data = $this->request->param();
        $processLogModel = new ProcessLogModel();
        $userModel = new UserModel();
        //查询是否已经有记录
        $count = $processLogModel->where(['user_id'=>$data['user_id'],'process_id'=>$data['process_id']])->count();
        if($count == 0){
            //开启事务，新增学员操作流程表
            Db::startTrans();

            $data['create_time'] = time();
            $log_id = $processLogModel->save($data);

            if($log_id){
                //更新学员的流程   如果已完成  则查询下一流程更新   如果处理中   则更新状态  更新memo
                if($data['status'] == '2'){
                    //已完成。查询下一个步骤，更新用户状态
                    $examinationProcessModel = new ExaminationProcessModel();
                    $next_examination = $examinationProcessModel->where(['use_status'=>'1'])->where('id','>',$data['process_id'])->field('id')->order('order')->find();
                    $flg = $userModel->where(['id'=>$data['user_id']])->update(['current_coordinates'=>$next_examination['id']]);
                    if($flg){
                        Db::commit();
                        $this->success('操作成功！', url('AdminExamination/operate'));
                    }
                }else{
                    //处理中。更新当前用户当前步骤的状态
                    $flg = $userModel->where(['id'=>$data['user_id']])->update(['current_coordinates_status'=>'1']);
                    if($flg){
                        Db::commit();
                        $this->success('操作成功！', url('AdminExamination/operate'));
                    }
            }
            }else{
                Db::rollback();
                $this->error('操作失败！', url('AdminExamination/operate'));
            }
        }else{
            $this->error('重复操作！', url('AdminExamination/operate'));
        }

    }


    //处理操作(财务流程)
    public function doPay(){
        $data = $this->request->param();
        $processLogModel = new ProcessLogModel();

        //财务数据单独列出
        $price = $data['price'];
        $real_price = $data['real_price'];
        $amount = $data['amount'];
        unset($data['price']);
        unset($data['real_price']);
        unset($data['amount']);

        $userModel = new UserModel();
        $userinfo = $userModel->where(['id'=>$data['user_id']])->find();
        //查询是否已经有记录
        $count = $processLogModel->where(['user_id'=>$data['user_id'],'process_id'=>$data['process_id']])->count();
        if($count == 0){
            //开启事务，新增学员操作流程表
            Db::startTrans();


            $data['create_time'] = time();
            $log_id = $processLogModel->save($data);

            if($log_id){
                //更新学员的流程   如果已完成  则查询下一流程更新   如果处理中   则更新状态  更新memo
                //财务流程 财务特殊处理
                //生成财务总表，生成财务分表
                $financeModel = new FinanceModel();

                //查询成本价
                $professionalModel = new ProfessionalModel();
                $base_price = $professionalModel->where(['id'=>$userinfo['major']])->field('base_price')->find();
                $finance_data = [];
                $finance_data['price'] = $price;
                $finance_data['real_price'] = $real_price;
                $finance_data['amount'] = 0;
                $finance_data['base_price'] = $base_price['base_price'];
                $finance_data['collection_amount'] = $real_price;
                $finance_data['payment_slip'] = 0;
                $finance_data['user_id'] = $data['user_id'];
                $finance_data['memo'] = $data['memo'];
                $finance_data['create_time'] = time();
                $financeModel->save($finance_data);
                $finance_id = $financeModel->getLastInsID();

                if($finance_id){
                    //插入财务分表
                    $financeBoundModel = new FinanceBoundModel();
                    $flag = $financeBoundModel->doFinance($finance_id,$amount,'inBound',$data['memo']);

                    if($flag){
                        if($data['status'] == '2'){
                            //已完成。查询下一个步骤，更新用户状态
                            $examinationProcessModel = new ExaminationProcessModel();
                            $next_examination = $examinationProcessModel->where(['use_status'=>'1'])->where('id','>',$data['process_id'])->field('id')->order('order')->find();
                            $flg = $userModel->where(['id'=>$data['user_id']])->update(['current_coordinates'=>$next_examination['id']]);
                            if($flg){
                                Db::commit();
                                $this->success('操作成功！', url('AdminExamination/operate'));
                            }
                        }else{
                            //处理中。更新当前用户当前步骤的状态
                            $flg = $userModel->where(['id'=>$data['user_id']])->update(['current_coordinates_status'=>'1']);
                            if($flg){
                                Db::commit();
                                $this->success('操作成功！', url('AdminExamination/operate'));
                            }
                        }
                    }else{
                        Db::rollback();
                        $this->error('操作失败！', url('AdminExamination/operate'));
                    }
                }else{
                    Db::rollback();
                    $this->error('操作失败！', url('AdminExamination/operate'));
                }

            }else{
                Db::rollback();
                $this->error('操作失败！', url('AdminExamination/operate'));
            }
        }else{
            $this->error('重复操作！', url('AdminExamination/operate'));
        }

    }

}
