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

class ProfessionalModel extends Model
{

    //关联的大类
    public function category()
    {
        return $this->hasOne('ProfessionalCategoryModel','id', 'category_id');
    }

    //关联的学校
    public function university()
    {
        return $this->hasOne('UniversityModel','id', 'university_id');
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
    public function adminCategoryTableTree($currentIds = 0,$university_id = 0)
    {
        //店铺状态开启
        $categories = $this->where(['status'=>'1','university_id'=>$university_id])->select()->toArray();

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