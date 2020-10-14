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
use app\university\model\FinanceModel;
use app\university\model\FinanceBoundModel;


class AdminFinanceController extends AdminBaseController
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

            $where['memo']    = ['like', "%$keyword%"];
        }
        $financeModel = new FinanceModel();
        $usersQuery = $financeModel->where($where)->order("create_time DESC");

        $list = $usersQuery->paginate(50);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function detail($id){
        $financeModel = new FinanceModel();
        $financeInfo = $financeModel->where(['id'=>$id])->find();

        $financeBoundModel = new FinanceBoundModel();
        $financeBoundInfo = $financeBoundModel->where(['finance_id'=>$id])->order('create_time DESC')->select();

        $this->assign('financeInfo', $financeInfo);
        $this->assign('financeBoundInfo', $financeBoundInfo);

        return $this->fetch();
    }

    public function addDetail($finance_id){
        $financeModel = new FinanceModel();
        $financeInfo = $financeModel->where(['id'=>$finance_id])->find();

        $this->assign('financeInfo', $financeInfo);
        $this->assign('finance_id', $finance_id);

        return $this->fetch();
    }


    public function toAddDetail(){
        $data = $this->request->param();

        $rules = [
            'amount'  => 'require',
            'type'     => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'amount.require'     => '金额必填',
            'type.require' => '类型必填',
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        $financeBoundModel = new FinanceBoundModel();
        $flag = $financeBoundModel->doFinance($data['finance_id'],$data['amount'],$data['type'],$data['memo']);

        if($flag){
            $this->success('操作成功！', url('AdminFinance/detail',['id'=>$data['finance_id']]));
        }else{
            $this->error('操作失败！');
        }
    }


    //添加院校
    public function add(){
        return $this->fetch();
    }

    public function addPost(){

        $data = $this->request->param();

        $rules = [
            'name'  => 'require',
            'type'     => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '院校名称必填',
            'type.require' => '院校类型必填',
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $data['create_time'] = time();

        $data['qizhong_time'] = strtotime($data['qizhong_time']);
        $data['qimo_time'] = strtotime($data['qimo_time']);
        $data['dissertation_time'] = strtotime($data['dissertation_time']);
        $data['finish_time'] = strtotime($data['finish_time']);

        $universityModel = new UniversityModel();
        $university_id = $universityModel->save($data);

        if($university_id){
            $this->success('操作成功！', url('AdminUniversity/index'));
        }else{
            $this->error('操作失败！');
        }
    }


    public function edit($id){
        $universityModel = new UniversityModel();
        $data = $universityModel->where(['id'=>$id])->find();

        $data['qizhong_time'] = date('Y-m-d H:i:s', $data['qizhong_time']);
        $data['qimo_time'] = date('Y-m-d H:i:s', $data['qimo_time']);
        $data['dissertation_time'] = date('Y-m-d H:i:s', $data['dissertation_time']);
        $data['finish_time'] = date('Y-m-d H:i:s', $data['finish_time']);

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function editPost(){

        $data = $this->request->param();

        $data['qizhong_time'] = strtotime($data['qizhong_time']);
        $data['qimo_time'] = strtotime($data['qimo_time']);
        $data['dissertation_time'] = strtotime($data['dissertation_time']);
        $data['finish_time'] = strtotime($data['finish_time']);

        $id = $data['id'];

        unset($data['id']);
        $rules = [
            'name'  => 'require',
            'type'     => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '院校名称必填',
            'type.require' => '院校类型必填',
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $universityModel = new UniversityModel();
        $university_id = $universityModel->where(['id'=>$id])->update($data);

        if($university_id){
            $this->success('操作成功！', url('AdminUniversity/index'));
        }else{
            $this->error('操作失败！');
        }
    }


    //选择所属院校
    public function select()
    {
        $ids                 = $this->request->param('ids');
        $selectedIds         = explode(',', $ids);


        $universityModel = new UniversityModel();
        $categoryTree = $universityModel->adminCategoryTableTree($selectedIds);

        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }

}
