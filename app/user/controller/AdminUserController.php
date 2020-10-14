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

namespace app\user\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\user\model\UserModel;
use think\facade\Validate;

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class AdminUserController extends AdminBaseController
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
        $content = hook_one('user_admin_index_view');

        if (!empty($content)) {
            return $content;
        }

        $where   = [];
        $where['user_type'] = '2';
        $request = input('request.');

        if (!empty($request['uid'])) {
            $where['id'] = intval($request['uid']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];

            $keywordComplex['user_login|user_nickname|user_email|mobile']    = ['like', "%$keyword%"];
        }
        $usersQuery = new UserModel();

        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("create_time DESC")->paginate(50);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }


    //添加学员
    public function add(){
        return $this->fetch();
    }

    public function addPost(){

        $data = $this->request->param();

        $rules = [
            'user_nickname'  => 'require',
            'id_card'     => 'require',
            'mobile' => 'require',
            'sex'     => 'require',
            'password'     => 'require|min:6'
        ];

        $validate = new \think\Validate($rules);
        $validate->message([
            'user_nickname.require'     => '姓名不能为空',
            'id_card.require' => '身份证号码不能为空',
            'mobile.require'     => '手机号不能为空',
            'sex.require'     => '请选择性别',
            'password.require'  => '密码不能为空',
            'password.min'  => '密码不能小于6位'
        ]);

        if (!$validate->check($data)) {
            $this->error($validate->getError());
        }

        if(!cmf_check_mobile($data['mobile'])){
            $this->error('请填写正确的手机号');
        }

        $userModel = new UserModel();
        $user_id = $userModel->addUser($data,'2');

        if($user_id){
            $this->success('操作成功！', url('AdminUser/index'));
        }else{
            if($user_id == 0){
                $this->error('操作失败！手机号已存在！');
            }else{
                $this->error('操作失败！');
            }
        }
    }


    //选择所属专业
    public function select()
    {
        $ids                 = $this->request->param('ids');
        $name = $this->request->param('name');
        $university_id                 = $this->request->param('university_id');
        $selectedIds         = explode(',', $ids);


        $userModel = new UserModel();
        $categoryTree = $userModel->adminCategoryTableTree($selectedIds,$name);

        $this->assign('selectedIds', $selectedIds);
        $this->assign('categories_tree', $categoryTree);
        return $this->fetch();
    }


}
