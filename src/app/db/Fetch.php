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

namespace app\db;

/**
 * SQL获取类
 */
class Fetch extends \think\db\Fetch
{
    /**
     * 删除记录(修复软删除bug)
     * @param null $data
     * @return string
     * @throws \think\db\exception\DbException
     */
    public function delete($data = null): string
    {
        $options = $this->query->parseOptions();

        if (!is_null($data) && true !== $data) {
            // AR模式分析主键条件
            $this->query->parsePkWhere($data);
        }

        if (!empty($options['soft_delete'])) {
            // 软删除
            [$field, $condition] = $options['soft_delete'];
            if ($condition) {
                $this->query->setOption('soft_delete', null);
                $this->query->setOption('data', [$field => $condition]);
                // 生成删除SQL语句
                $sql = $this->builder->update($this->query);
                return $this->fetch($sql);
            }
        }

        // 生成删除SQL语句
        $sql = $this->builder->delete($this->query);

        return $this->fetch($sql);
    }
}
