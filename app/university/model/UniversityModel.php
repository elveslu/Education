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

class UniversityModel extends Model
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
        $categories = $this->where(['status'=>'1'])->select()->toArray();

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