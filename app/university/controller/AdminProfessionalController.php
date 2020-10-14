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


class AdminProfessionalController extends AdminBaseController
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
        $professionalModel = new ProfessionalModel();
        $usersQuery = $professionalModel->where($where)->order("create_time DESC");

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
            'price'  => 'require',
            'university_id'  => 'require',
            'category_id'  => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '专业名称必填',
            'price.require'     => '标准售价必填',
            'university_id.require'     => '所属院校必选',
            'category_id.require'     => '所属类目必选',
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $data['create_time'] = time();
        $professionalModel = new ProfessionalModel();
        $university_id = $professionalModel->save($data);

        if($university_id){
            $this->success('操作成功！', url('AdminProfessional/index'));
        }else{
            $this->error('操作失败！');
        }
    }


    public function edit($id){
        $professionalModel = new ProfessionalModel();
        $data = $professionalModel->where(['id'=>$id])->find();

        $this->assign('data', $data);

        return $this->fetch();
    }

    public function editPost(){

        $data = $this->request->param();

        $id = $data['id'];

        unset($data['id']);
        $rules = [
            'name'  => 'require',
            'price'  => 'require',
            'university_id'  => 'require',
            'category_id'  => 'require',
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'name.require'     => '专业名称必填',
            'price.require'     => '标准售价必填',
            'university_id.require'     => '所属院校必选',
            'category_id.require'     => '所属类目必选',
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }
        $data['create_time'] = time();
        $professionalModel = new ProfessionalModel();
        $university_id = $professionalModel->where(['id'=>$id])->update($data);

        if($university_id){
            $this->success('操作成功！', url('AdminProfessional/index'));
        }else{
            $this->error('操作失败！');
        }
    }

    //选择所属专业
    public function select()
    {
        $ids                 = $this->request->param('ids');
        $university_id                 = $this->request->param('university_id');
        $selectedIds         = explode(',', $ids);


        $professionalModel = new ProfessionalModel();
        $categoryTree = $professionalModel->adminCategoryTableTree($selectedIds,$university_id);

        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }


}
