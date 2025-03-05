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
declare (strict_types=1);

namespace app\db;

use think\db\Query;

/**
 * SQL获取类
 */
class Fetch extends \think\db\Fetch
{
    protected $myModel; //模型

    public function __construct(Query $query)
    {
        parent::__construct($query);
        $myModel = $query->getMyModel();
        if (!empty($myModel)) {
            $this->setMyModel($myModel);
        }
    }

    /**
     * 模型控制
     * @param $model
     * @return $this|\think\db\Query
     */
    public function setMyModel($model)
    {
        $this->myModel = $model;
        return $this;
    }

    /**
     * 获取模型
     * @return mixed
     */
    public function getMyModel()
    {
        return $this->myModel;
    }

    /**
     *
     * @param array $data
     * @param bool $forceInsert
     * @return string
     * @throws \think\db\exception\DbException
     */
    public function save(array $data = [], bool $forceInsert = false): string
    {
        $myModel = $this->myModel;

        //扩展 __PK__ __DELETETIME__
        if (!empty($myModel)) {
            if (!empty($myModel->pk) && isset($data['__PK__'])) {
                $data[$myModel->pk] = $data['__PK__'];
                unset($data['__PK__']);
            }

            if (!empty($myModel->deleteTime) && isset($data['__DELETETIME__'])) {
                $data[$myModel->deleteTime] = $data['__DELETETIME__'];
                unset($data['__DELETETIME__']);
            }
        }

        if ($forceInsert) {
            return $this->insert($data);
        }

        $data = array_merge($this->query->getOptions('data') ?? [], $data);

        $this->query->setOption('data', $data);

        if (!empty($this->query->getOptions('where'))) {
            $isUpdate = true;
        } else {
            $isUpdate = $this->query->parseUpdateData($data);
        }

        $before = 'beforeSave';
        if (!empty($myModel) && method_exists($myModel, $before)) {
            $beforeResult = $myModel->$before(
                $this->query->getOptions('data'),
                $this->query->getOptions('where') ?? []
            );
            $opData = $beforeResult[0] ?? [];
            $opWhere = $beforeResult[1] ?? [];
            $this->query->setOption('data', $opData);
            $this->query->setOption('where', $opWhere);
        }

        if ($isUpdate) { //更新
            // 只读字段不允许更新
            if (!empty($myModel->readonly)) {
                $opData = $this->query->getOptions('data');
                foreach ($myModel->readonly as $key => $field) {
                    if (array_key_exists($field, $opData)) {
                        unset($opData[$field]);
                    }
                }
                $this->query->setOption('data', $opData);
            }

            $result = $this->update();
        } else { //插入
            $result = $this->insert();
        }

        return $result;
    }

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
