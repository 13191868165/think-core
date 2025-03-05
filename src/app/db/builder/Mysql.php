<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2019 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
declare (strict_types = 1);

namespace app\db\builder;

use think\db\Query;

/**
 * mysql数据库驱动
 */
class mysql extends \think\db\builder\Mysql
{
    /**
     * 字段和表名处理
     * @param Query $query
     * @param mixed $key
     * @param bool $strict
     * @return string
     * @throws \think\db\exception\DbException
     */
    public function parseKey(Query $query, $key, bool $strict = false): string
    {
        $key = parent::parseKey($query, $key, $strict);

        //扩展 __PK__ __DELETETIME__
        if($key != '*') {
            if(property_exists($query, 'myModel')) {
                $myModel = $query->getMyModel();
                if(!empty($myModel->pk)) {
                    $key = str_replace('__PK__', $myModel->pk, $key);
                }
                if(!empty($myModel->deleteTime)) {
                    $key = str_replace('__DELETETIME__', $myModel->deleteTime, $key);
                }
            }
        }

        return $key;
    }
}
