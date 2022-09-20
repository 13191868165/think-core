<?php
declare (strict_types=1);

namespace app\db;

use app\util\Transform;

class Query extends \think\db\Query
{

    protected $myModel; //模型

    /**
     * 获取执行的SQL语句而不进行实际的查询
     * @param bool $fetch 是否返回sql
     * @return $this|Fetch|\think\db\Fetch|\think\db\Query
     */
    public function fetchSql(bool $fetch = true)
    {
        $this->options['fetch_sql'] = $fetch;

        if ($fetch) {
            return new Fetch($this);
        }

        return $this;
    }

    /**
     * @param mixed $field
     * @param null $op
     * @param null $condition
     * @return \think\db\Query
     */
    public function where($field, $op = null, $condition = null)
    {
        if ($op == null && $condition == null) {
            if (is_numeric($field) && !empty($this->pk)) {
                $op = $field;
                $field = '__TABLE__.' . $this->pk;
            } elseif (gettype($field) == 'string') {
                if (!empty($this->pk)) {
                    $field = str_replace('__PK__', "`{$this->pk}`", $field);
                }

                if (!empty($this->deleteTime)) {
                    $field = str_replace('__DELETETIME__', "`{$this->deleteTime}`", $field);
                }
            }
        }
        if ($op == null && $condition == null && is_numeric($field) && !empty($this->pk)) {
            $op = $field;
            $field = '__TABLE__.' . $this->pk;
        }
        return parent::where($field, $op, $condition);
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
     * 数据转换
     * @param $data
     * @param $type
     * @return mixed
     */
    public function transform($data, $type)
    {

        if (empty($data) || empty($type)) {
            return $data;
        }

        if (is_array($type)) {

            $transform = new Transform();
            foreach ($type as $key => $args) {
                $key = array_filter(explode(':', $key, 2)); //拆分新旧字段
                $field = $key[0]; //字段名
                if (is_string($args)) {
                    $args = [$args];
                }
                $name = $args[0]; //方法名
                $args[0] = isset($data[$field]) ? $data[$field] : null; //字段值

                //移除只读字段
                if ($name == 'readonly' || $name == 'unset') {
                    unset($data[$field]);
                } else {
                    $value = call_user_func_array([$transform, $name], $args);

                    if (!empty($key[1])) { //赋值新字段
                        $data[$key[1]] = $value;
                    } else { //覆盖字段值
                        $data[$field] = $value;
                    }
                }
            }
        }

        return $data;
    }

    /**
     * 获取软删除字段
     * @access protected
     * @param bool $read 是否查询操作 写操作的时候会自动去掉表别名
     * @return string|false
     */
    protected function getDeleteTimeField(bool $read = false)
    {
        $myModel = $this->myModel;
        $field = property_exists($myModel, 'deleteTime') && isset($myModel->deleteTime) ? $myModel->deleteTime : 'delete_time';

        if (empty($field)) {
            return false;
        }

        if (false === strpos($field, '.')) {
            $field = '__TABLE__.' . $field;
        }

        if (!$read && strpos($field, '.')) {
            $array = explode('.', $field);
            $field = array_pop($array);
        }

        return $field;
    }

    /**
     * 移除软删除
     * @return $this
     */
    public function removeSoftDelete()
    {
        return $this->withSoftDelete(0, true);
    }

    public function useSoftDelete(string $field, $condition = null)
    {
        if ($field) {
            $this->options['soft_delete'] = [$field, $condition];
        }

        return $this;
    }

    /**
     * 使用软删除
     * @param int $type 0移除软删除条件 1软删除条件=默认值 2软删除条件<>默认值
     * @return $this
     */
    public function withSoftDelete($type = 1, $isDelete = false)
    {
        //withNoTrashed == true 或 软删除数据已存在则不执行
        if ((isset($this->withNoTrashed) && $this->withNoTrashed == true) ||
            isset($this->options['soft_delete'])) {
            return $this;
        }

        $this->withNoTrashed = true;

        $removeSoftDelete = false; //移除软删除
        $type = intval($type);
        if ($isDelete == true) { //删除
            $field = $this->getDeleteTimeField(true);
            if (!empty($field)) {
                if ($type == 0) {
                    $removeSoftDelete = true;
                } else {
                    $this->useSoftDelete($field, time());
                }
            } else {
                $removeSoftDelete = true;
            }
        } else { //其它
            if ($type > 0) {
                $field = $this->getDeleteTimeField(true);
                if (!empty($field)) {
                    $myModel = $this->myModel;
                    if ($type == 1) { //不含软删除数据
                        $condition = is_null($myModel->defaultSoftDelete) ? ['null', ''] : ['=', $myModel->defaultSoftDelete];
                    } elseif ($type == 2) { //已删除数据
                        $condition = is_null($myModel->defaultSoftDelete) ? ['notnull', ''] : ['<>', $myModel->defaultSoftDelete];
                    }

                    $this->useSoftDelete($field, $condition);
                }
            }
        }

        //移除软删除
        if ($removeSoftDelete == true) {
            $this->removeOption('soft_delete');
        }

        return $this;
    }

    /**
     * 关联执行after
     * @return $this
     */
    public function joinAfter()
    {
        $this->joinAfter = true;
        return $this;
    }

    /**
     * 保存记录 自动判断insert或者update
     * @param array $data 数据
     * @param bool $forceInsert 是否强制insert
     * @return int|string
     * @throws \think\db\exception\DbException
     */
    public function save($data = [], $forceInsert = false)
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

        $this->options['data'] = array_merge(isset($this->options['data']) ? $this->options['data'] : [], $data);

        if (!empty($this->options['where'])) {
            $isUpdate = true;
        } else {
            $isUpdate = $this->parseUpdateData($this->options['data']);
        }

        $before = 'beforeSave';
        if (!empty($myModel) && method_exists($myModel, $before)) {
            $beforeResult = $myModel->$before($this->options['data'], isset($this->options['where']) ? $this->options['where'] : []);
            $this->options['data'] = $beforeResult[0] ?? [];
            $this->options['where'] = $beforeResult[1] ?? [];
        }

        if ($isUpdate) { //更新
            $id = isset($data[$myModel->pk]) ? intval($data[$myModel->pk]) : 0;

            // 只读字段不允许更新
            if (!empty($myModel->readonly)) {
                foreach ($myModel->readonly as $key => $field) {
                    if (array_key_exists($field, $this->options['data'])) {
                        unset($this->options['data'][$field]);
                    }
                }
            }

            $result = $this->update();
        } else { //插入
            $id = $result = $this->insert([], true);
        }

        return $result === false ? false : $id;
    }

    /**
     * 获取单行数据
     * @param string $where
     * @return Query|array|mixed|string|\think\db\Query|\think\Model
     * @throws \ReflectionException
     */
    public function getRow($where = '')
    {
        //处理软删除
        $this->withSoftDelete();

        //where条件
        if (!empty($where)) {
            $this->where($where);
        }

        //打印sql
        if (isset($this->options['fetch_sql']) && $this->options['fetch_sql'] == 1) {
            return $this->fetchSql()->findOrEmpty();
        }

        $joinOptions = isset($this->options['join']) ? $this->options['join'] : null;

        $result = $this->findOrEmpty();

        //处理当前模型钩子
        if (!empty($this->myModel)) {
            $after = 'afterGetRow';
            $afterGetInfo = 'afterGetInfo';
            if (method_exists($this->myModel, $after)) {
                $result = $this->myModel->$after($result);
            } elseif ($result && method_exists($this->myModel, $afterGetInfo)) {
                $result = $this->myModel->$afterGetInfo($result);
            }

            //处理关联模型钩子
            if (!empty($joinOptions) && empty($this->joinAfter)) {
                $joinList = [];
                foreach ($joinOptions as $join) {
                    $k = key($join[0]);
                    if (!isset($joinList[$k])) {
                        $prefix = $this->getConfig('prefix');
                        $moduleName = substr_replace($k, '', strpos($k, $prefix), strlen($prefix));
                        $joinList[$k] = f('Str')::camel($moduleName);

                        if (class_exists(app_namespace('model', $joinList[$k])) == true) {
                            $joinModel = m($joinList[$k]);
                            if (method_exists($joinModel, $after)) {
                                $result = $joinModel->$after($result);
                            } elseif ($result && method_exists($joinModel, $afterGetInfo)) {
                                $result = $joinModel->$afterGetInfo($result);
                            }
                        }
                    }
                }
            }
        }

        return $result;
    }

    /**
     * 获取数据集
     * @param string $type
     * @return array|mixed
     * @throws \ReflectionException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($type = '')
    {
        //处理软删除
        $this->withSoftDelete();

        //where条件
        if (!empty($where)) {
            $this->where($where);
        }

        $options = $this->getOptions();
        if (empty($type) || $type == 'total') {
            $db = $this->myModel->db();
            $db->options = $options;
        }

        $result = [];

        //打印sql
        if (isset($this->options['fetch_sql']) && $this->options['fetch_sql'] == 1) {

            if ($type != 'total') {
                $list = $this->fetchSql()->select();
                $result['list'] = $list;
            }

            if (isset($db)) {
                $total = $db->fetchSql()->getCount();
                $result['total'] = $total;
            }

            return $result;
        }

        if ($type != 'total') {
            $joinOptions = isset($this->options['join']) ? $this->options['join'] : null;

            //查询数据
            $list = $this->select()->toArray();

            //处理当前模型钩子
            if (!empty($this->myModel)) {
                $after = 'afterGetList';
                $afterGetInfo = 'afterGetInfo';
                foreach ($list as $key => $value) {
                    if (method_exists($this->myModel, $after)) {
                        $list[$key] = $this->myModel->$after($value);
                    } elseif ($list && method_exists($this->myModel, $afterGetInfo)) {
                        if (!empty($list) && is_array($list)) {
                            $list[$key] = $this->myModel->$afterGetInfo($value);
                        }
                    }
                }

                //处理关联模型钩子
                if (!empty($joinOptions) && empty($this->joinAfter)) {
                    $joinList = [];
                    foreach ($joinOptions as $join) {
                        $k = key($join[0]);
                        if (!isset($joinList[$k])) {
                            $prefix = $this->getConfig('prefix');
                            $moduleName = substr_replace($k, '', strpos($k, $prefix), strlen($prefix));
                            $joinList[$k] = f('Str')::camel($moduleName);
                            if(class_exists(app_namespace('model', $joinList[$k])) == true) {
                                $joinModel = m($joinList[$k]);
                                foreach ($list as $key => $value) {
                                    if (method_exists($joinModel, $after)) {
                                        $list[$key] = $joinModel->$after($value);
                                    } elseif ($list && method_exists($joinModel, $afterGetInfo)) {
                                        if (!empty($list) && is_array($list)) {
                                            $list[$key] = $joinModel->$afterGetInfo($value);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $result['list'] = $list;
            if (!empty($options['page'])) {
                $result['page'] = $options['page'][0];
                $result['size'] = $options['page'][1];
            }
        }

        //查询数量
        if (isset($db)) {
            $total = $db->getCount();
            $result['total'] = $total;
        }

        return empty($type) ? $result : $result[$type];
    }

    /**
     * 关联获取单行数据
     * @param string $where
     * @return Query|array|bool|mixed|string|\think\db\Query|\think\Model
     * @throws \ReflectionException
     */
    public function getJoinRow($where = '', $type = '')
    {
        $myModel = $this->myModel;
        if (empty($this->myModel) || empty($this->myModel->leftJoin)) {
            return false;
        }

        $modelName = [];
        if ($type == 'left') {
            $leftJoin = $myModel->leftJoin;
            if (count($leftJoin) == 3) {
                $this->alias($leftJoin[0])->leftJoin($leftJoin[1], $leftJoin[2]);
            } else {
                $this->leftJoin($leftJoin[1], $leftJoin[2]);
            }
            $modelName = $leftJoin[1];
        } elseif ($type == 'right') {
            $rightJoin = $myModel->rightJoin;
            if (count($rightJoin) == 3) {
                $this->alias($rightJoin[0])->rightJoin($rightJoin[1], $rightJoin[2]);
            } else {
                $this->rightJoin($rightJoin[1], $rightJoin[2]);
            }
            $modelName = $rightJoin[1];
        } elseif ($type == 'full') {
            $fullJoin = $myModel->fullJoin;
            if (count($fullJoin) == 3) {
                $this->alias($fullJoin[0])->fullJoin($fullJoin[1], $fullJoin[2]);
            } else {
                $this->fullJoin($fullJoin[1], $fullJoin[2]);
            }
            $modelName = $fullJoin[1];
        } else {
            $join = $myModel->join;
            if (count($join) == 3) {
                $this->alias($join[0])->join($join[1], $join[2]);
            } else {
                $this->join($join[1], $join[2]);
            }
            $modelName = $join[1];
        }

        [$table, $alias] = explode(' ', $modelName);
        $joinModel = m(f('Str')::camel($table));
        //处理关联查询时软删除
        if (!empty($joinModel->deleteTime)) {
            $this->where("{$alias}.{$joinModel->deleteTime}", $joinModel->defaultSoftDelete);
        }

        return $this->getRow($where);
    }

    /**
     * 左关联获取单行数据
     * @param string $where
     * @return Query|array|bool|mixed|string|\think\db\Query|\think\Model
     * @throws \ReflectionException
     */
    public function getLeftJoinRow($where = '')
    {
        return $this->getJoinRow($where, 'left');
    }

    /**
     * 右关联获取单行数据
     * @param string $where
     * @return Query|array|bool|mixed|string|\think\db\Query|\think\Model
     * @throws \ReflectionException
     */
    public function getRightJoinRow($where = '')
    {
        return $this->getJoinRow($where, 'right');
    }

    /**
     * 全关联获取单行数据
     * @param string $where
     * @return Query|array|bool|mixed|string|\think\db\Query|\think\Model
     * @throws \ReflectionException
     */
    public function getFullJoinRow($where = '')
    {
        return $this->getJoinRow($where, 'full');
    }

    /**
     * 关联获取数据集
     * @param string $where
     * @param string $type
     * @return array|bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getJoinList($where = '', $type = '')
    {
        $myModel = $this->myModel;
        if (empty($this->myModel) || empty($this->myModel->leftJoin)) {
            return false;
        }

        $leftJoin = $myModel->leftJoin;

        if ($type == 'left') {
            if (count($leftJoin) == 3) {
                $this->alias($leftJoin[0])->leftJoin($leftJoin[1], $leftJoin[2]);
            } else {
                $this->leftJoin($leftJoin[1], $leftJoin[2]);
            }
        } elseif ($type == 'right') {
            if (count($leftJoin) == 3) {
                $this->alias($leftJoin[0])->rightJoin($leftJoin[1], $leftJoin[2]);
            } else {
                $this->rightJoin($leftJoin[1], $leftJoin[2]);
            }
        } elseif ($type == 'full') {
            if (count($leftJoin) == 3) {
                $this->alias($leftJoin[0])->fullJoin($leftJoin[1], $leftJoin[2]);
            } else {
                $this->fullJoin($leftJoin[1], $leftJoin[2]);
            }
        } else {
            if (count($leftJoin) == 3) {
                $this->alias($leftJoin[0])->join($leftJoin[1], $leftJoin[2]);
            } else {
                $this->join($leftJoin[1], $leftJoin[2]);
            }
        }

        return $this->getList($where);
    }

    /**
     * 左关联获取数据集
     * @param string $where
     * @return array|bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getLeftJoinList($where = '')
    {
        return $this->getJoinList($where, 'left');
    }

    /**
     * 右关联获取数据集
     * @param string $where
     * @return array|bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getRightJoinList($where = '')
    {
        return $this->getJoinList($where, 'right');
    }

    /**
     * 全关联获取数据集
     * @param string $where
     * @return array|bool|mixed
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getFullJoinList($where = '')
    {
        return $this->getJoinList($where, 'full');
    }

    /**
     * 统计数量
     * @param string $field
     * @return int|string
     */
    public function getCount($field = '*')
    {
        unset($this->options['order'], $this->options['page'], $this->options['limit']);
        if (isset($this->options['fetch_sql']) && $this->options['fetch_sql'] == 1) {
            return $this->fetchSql()->count($field);
        } else {
            return $this->count($field);
        }
    }

    /**
     * 删除记录
     * @param null $data
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function delete($data = null): int
    {
        //处理软删除
        $this->withSoftDelete(1, true);

        return parent::delete($data);
    }
}
