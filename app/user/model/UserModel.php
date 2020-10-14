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
namespace app\user\model;

use think\Db;
use think\Model;
use tree\Tree;

class UserModel extends Model
{
    protected $type = [
        'more' => 'array',
    ];

    public function doMobile($user)
    {
        $result = $this->where('mobile', $user['mobile'])->find();


        if (!empty($result)) {
            $comparePasswordResult = cmf_compare_password($user['user_pass'], $result['user_pass']);
            $hookParam             = [
                'user'                    => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                //拉黑判断。
                if ($result['user_status'] == 0) {
                    return 3;
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $this->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user'                    => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    public function doName($user)
    {
        $result = $this->where('user_login', $user['user_login'])->find();
        if (!empty($result)) {
            $comparePasswordResult = cmf_compare_password($user['user_pass'], $result['user_pass']);
            $hookParam             = [
                'user'                    => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {
                //拉黑判断。
                if ($result['user_status'] == 0) {
                    return 3;
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $result->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user'                    => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    public function doEmail($user)
    {

        $result = $this->where('user_email', $user['user_email'])->find();

        if (!empty($result)) {
            $comparePasswordResult = cmf_compare_password($user['user_pass'], $result['user_pass']);
            $hookParam             = [
                'user'                    => $user,
                'compare_password_result' => $comparePasswordResult
            ];
            hook_one("user_login_start", $hookParam);
            if ($comparePasswordResult) {

                //拉黑判断。
                if ($result['user_status'] == 0) {
                    return 3;
                }
                session('user', $result->toArray());
                $data = [
                    'last_login_time' => time(),
                    'last_login_ip'   => get_client_ip(0, true),
                ];
                $this->where('id', $result["id"])->update($data);
                $token = cmf_generate_user_token($result["id"], 'web');
                if (!empty($token)) {
                    session('token', $token);
                }
                return 0;
            }
            return 1;
        }
        $hookParam = [
            'user'                    => $user,
            'compare_password_result' => false
        ];
        hook_one("user_login_start", $hookParam);
        return 2;
    }

    public function register($user, $type)
    {
        switch ($type) {
            case 1:
                $result = Db::name("user")->where('user_login', $user['user_login'])->find();
                break;
            case 2:
                $result = Db::name("user")->where('mobile', $user['mobile'])->find();
                break;
            case 3:
                $result = Db::name("user")->where('user_email', $user['user_email'])->find();
                break;
            default:
                $result = 0;
        }

        $userStatus = 1;

        if (cmf_is_open_registration()) {
            $userStatus = 2;
        }

        if (empty($result)) {
            $data   = [
                'user_login'      => empty($user['user_login']) ? '' : $user['user_login'],
                'user_email'      => empty($user['user_email']) ? '' : $user['user_email'],
                'mobile'          => empty($user['mobile']) ? '' : $user['mobile'],
                'user_nickname'   => '',
                'user_pass'       => cmf_password($user['user_pass']),
                'last_login_ip'   => get_client_ip(0, true),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => $userStatus,
                "user_type"       => 2,//会员
            ];
            $userId = Db::name("user")->insertGetId($data);
            $data   = Db::name("user")->where('id', $userId)->find();
            cmf_update_current_user($data);
            $token = cmf_generate_user_token($userId, 'web');
            if (!empty($token)) {
                session('token', $token);
            }
            return 0;
        }
        return 1;
    }

    /**
     * 通过邮箱重置密码
     * @param $email
     * @param $password
     * @return int
     */
    public function emailPasswordReset($email, $password)
    {
        $result = $this->where('user_email', $email)->find();
        if (!empty($result)) {
            $data = [
                'user_pass' => cmf_password($password),
            ];
            $this->where('user_email', $email)->update($data);
            return 0;
        }
        return 1;
    }

    /**
     * 通过手机重置密码
     * @param $mobile
     * @param $password
     * @return int
     */
    public function mobilePasswordReset($mobile, $password)
    {
        $userQuery = Db::name("user");
        $result    = $userQuery->where('mobile', $mobile)->find();
        if (!empty($result)) {
            $data = [
                'user_pass' => cmf_password($password),
            ];
            $userQuery->where('mobile', $mobile)->update($data);
            return 0;
        }
        return 1;
    }

    public function editData($user)
    {
        $userId = cmf_get_current_user_id();

        if (isset($user['birthday'])) {
            $user['birthday'] = strtotime($user['birthday']);
        }

        $field = 'user_nickname,sex,birthday,user_url,signature,more';

        if ($this->allowField($field)->save($user, ['id' => $userId])) {
            $userInfo = $this->where('id', $userId)->find();
            cmf_update_current_user($userInfo->toArray());
            return 1;
        }
        return 0;
    }

    /**
     * 用户密码修改
     * @param $user
     * @return int
     */
    public function editPassword($user)
    {
        $userId    = cmf_get_current_user_id();
        $userQuery = Db::name("user");
        if ($user['password'] != $user['repassword']) {
            return 1;
        }
        $pass = $userQuery->where('id', $userId)->find();
        if (!cmf_compare_password($user['old_password'], $pass['user_pass'])) {
            return 2;
        }
        $data['user_pass'] = cmf_password($user['password']);
        $userQuery->where('id', $userId)->update($data);
        return 0;
    }

    public function comments()
    {
        $userId               = cmf_get_current_user_id();
        $userQuery            = Db::name("Comment");
        $where['user_id']     = $userId;
        $where['delete_time'] = 0;
        $favorites            = $userQuery->where($where)->order('id desc')->paginate(10);
        $data['page']         = $favorites->render();
        $data['lists']        = $favorites->items();
        return $data;
    }

    public function deleteComment($id)
    {
        $userId              = cmf_get_current_user_id();
        $userQuery           = Db::name("Comment");
        $where['id']         = $id;
        $where['user_id']    = $userId;
        $data['delete_time'] = time();
        $userQuery->where($where)->update($data);
        return $data;
    }

    /**
     * 绑定用户手机号
     */
    public function bindingMobile($user)
    {
        $userId          = cmf_get_current_user_id();
        $data ['mobile'] = $user['username'];
        Db::name("user")->where('id', $userId)->update($data);
        $userInfo = Db::name("user")->where('id', $userId)->find();
        cmf_update_current_user($userInfo);
        return 0;
    }

    /**
     * 绑定用户邮箱
     */
    public function bindingEmail($user)
    {
        $userId              = cmf_get_current_user_id();
        $data ['user_email'] = $user['username'];
        Db::name("user")->where('id', $userId)->update($data);
        $userInfo = Db::name("user")->where('id', $userId)->find();
        cmf_update_current_user($userInfo);
        return 0;
    }

    //后台添加user用户
    public function addUser($user,$type){
        $userModel = new UserModel();
        switch ($type) {
            case 1:
                $result = $userModel->where('user_login', $user['user_login'])->find();
                break;
            case 2:
                $result = $userModel->where('mobile', $user['mobile'])->find();
                break;
            case 3:
                $result = $userModel->where('user_email', $user['user_email'])->find();
                break;
            default:
                $result = 0;
        }
        $userStatus = 1;

        //是否允许开放注册
//        if (cmf_is_open_registration()) {
//            $userStatus = 2;
//        }
        $avatar = '';
        if (!empty($user['photo_path']['thumbnail'])) {
            $avatar = cmf_asset_relative_url($user['photo_path']['thumbnail']);
        }
        if (empty($result)) {
            $data   = [
                'mobile'          => empty($user['mobile']) ? '' : $user['mobile'],
                'user_nickname'   => $user['user_nickname'],
                'user_pass'       => cmf_password($user['password']),
                'create_time'     => time(),
                'last_login_time' => time(),
                'user_status'     => $userStatus,
                "user_type"       => 2,//会员
                'avatar'          => $avatar,
                'sex'             => $user['sex'],
                'school'         =>$user['school'],
                'major'          =>$user['major'],
                'age'            =>$user['age'],
                'degree'            =>$user['degree'],
                'location'            =>$user['location'],
                'highest_education'            =>$user['highest_education'],
                'address'            =>$user['address'],
                'nation'            =>$user['nation'],
                'work_unit'            =>$user['work_unit'],
                'political_outlook'            =>$user['political_outlook'],
                'authentication'            =>$user['authentication'],
                'Recommender'            =>$user['Recommender'],
                'memo'            =>$user['memo'],
                'id_card'            =>$user['id_card'],
            ];
            $user_id = $userModel->insertGetId($data);
            return $user_id;
        }else{
            return 0;
        }
        return -1;
    }

    //后台添加user用户
    public function editUser($user){
        $userModel = new UserModel();

        $avatar = '';
        if (!empty($user['photo_path']['thumbnail'])) {
            $avatar = cmf_asset_relative_url($user['photo_path']['thumbnail']);
        }

        $data   = [
            'mobile'          => empty($user['mobile']) ? '' : $user['mobile'],
            'user_nickname'   => $user['user_nickname'],
            'avatar'          => $avatar,
            'sex'             => $user['sex'],
            'school'         =>$user['school'],
            'major'          =>$user['major'],
            'age'            =>$user['age'],
            'degree'            =>$user['degree'],
            'location'            =>$user['location'],
            'highest_education'            =>$user['highest_education'],
            'address'            =>$user['address'],
            'nation'            =>$user['nation'],
            'work_unit'            =>$user['work_unit'],
            'political_outlook'            =>$user['political_outlook'],
            'authentication'            =>$user['authentication'],
            'Recommender'            =>$user['Recommender'],
            'memo'            =>$user['memo'],
            'id_card'            =>$user['id_card'],
        ];

        if(key_exists('password',$user)){
            $data['user_pass'] = cmf_password($user['password']);
        }

        $user_id = $userModel->where(['id'=>$user['id']])->update($data);
        return $user_id;

    }

    //关联的学校
    public function university()
    {
        return $this->hasOne('app\university\model\UniversityModel','id', 'school');
    }

    //关联的专业
    public function majorm()
    {
        return $this->hasOne('app\university\model\ProfessionalModel','id', 'major');
    }

    //关联的流程
    public function coordinates()
    {
        return $this->hasOne('app\university\model\ExaminationProcessModel','id', 'current_coordinates');
    }

    /**
     * 分类树形结构
     * @param int    $currentIds
     * @param string $tpl
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function adminCategoryTableTree($currentIds = 0,$name = '')
    {
        //店铺状态开启
        $where = [];
        $where['user_status'] = '1';
        if($name){
            $where['user_nickname'] = ['like',"%$name%"];
        }
        $categories = $this->where($where)->select()->toArray();

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

}
