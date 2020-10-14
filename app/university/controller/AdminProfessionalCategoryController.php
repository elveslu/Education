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
use app\university\model\ProfessionalModel;
use app\university\model\ProfessionalCategoryModel;


class AdminProfessionalCategoryController extends AdminBaseController
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
        $professionalCategoryModel = new ProfessionalCategoryModel();
        $usersQuery = $professionalCategoryModel->where($where)->order("create_time DESC");

        $list = $usersQuery->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }


    //添加类目
    public function add(){
        return $this->fetch();
    }

    public function addPost(){

        $data = $this->request->param();

        $rules = [
            'name'  => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '院校名称必填',
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $data['create_time'] = time();
        $professionalCategoryModel = new ProfessionalCategoryModel();
        $university_id = $professionalCategoryModel->save($data);

        if($university_id){
            $this->success('操作成功！', url('AdminProfessionalCategory/index'));
        }else{
            $this->error('操作失败！');
        }
    }


    public function edit($id){
        $professionalCategoryModel = new ProfessionalCategoryModel();
        $data = $professionalCategoryModel->where(['id'=>$id])->find();

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function editPost(){

        $data = $this->request->param();

        $id = $data['id'];

        unset($data['id']);
        $rules = [
            'name'  => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '院校名称必填',
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $data['create_time'] = time();
        $professionalCategoryModel = new ProfessionalCategoryModel();
        $university_id = $professionalCategoryModel->where(['id'=>$id])->update($data);

        if($university_id){
            $this->success('操作成功！', url('AdminProfessionalCategory/index'));
        }else{
            $this->error('操作失败！');
        }
    }


    //选择所属类目
    public function select()
    {
        $ids                 = $this->request->param('ids');
        $selectedIds         = explode(',', $ids);


        $professionalCategoryModel = new ProfessionalCategoryModel();
        $categoryTree = $professionalCategoryModel->adminCategoryTableTree($selectedIds);

        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }

}
