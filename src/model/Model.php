<?php
namespace app\core\model;

use think\facade\Db;

class Model
{

    private $db = [];

    //模型名
    public $name = '';
    //表名
    public $table = '';
    //主键
    public $pk = '';
    //是否带前缀
    public $usePrefix = true;

    //软删除字段
    public $deleteTime = '';
    //软删除字段默认值
    public $defaultSoftDelete = 0;

    public function __construct()
    {
        if(empty($this->name)) {
            $this->name = (new \ReflectionClass($this))->getShortName();
        }
        if($this->name != 'Model') {
            //自动绑定表名为模型名
            if(empty($this->table)) {
                //驼峰转下划线
                $this->table = strtolower(\app\core\util\Str::snake($this->name, '_'));
            }

            //自动绑定主键
            if(empty($this->pk) && $this->pk !== false) {
                $this->pk = 'id';
            }

            $this->initDb();
        }
    }

    /**
     * 安装
     */
    public function install()
    {
    }

    /**
     * 升级
     */
    public function upgrade()
    {
    }

    /**
     * 卸载
     */
    public function uninstall()
    {
        return 'DROP TABLE IF EXISTS `' . table($this->name) . '`;';
    }

    /**
     * 数据 类型转换
     * @access protected
     * @param mixed $value 值
     * @param string|array $type 要转换的类型
     * @return mixed
     */
    private function transform($value, $type)
    {
        if(is_null($value)) {
            return;
        }

        if(is_array($type)) {
            $type = $type[0];
            $param = $type[1];
        }elseif(strpos($type, ':')) {
            $type = explode(':', $type, 2);
            $type = $type[0];
            $param = $type[1];
        }

        switch($type) {
            case 'string':
                $value = (string)$value;
                break;
            case 'integer':
                $value = (int)$value;
                break;
            case 'float':
                if(empty($param)) {
                    $value = (float)$value;
                }else {
                    $value = (float)number_format($value, (int)$param, '.', '');
                }
                break;
            case 'boolean':
                $value = (bool)$value;
                break;
            case 'timestamp':
                if(!is_numeric($value)) {
                    $value = strtotime($value);
                }
                break;
            case 'datetime':
                $value = is_numeric($value) ? $value : strtotime($value);
                if(empty($param)) {
                    $value = date('Y-m-d H:i:s', $value);
                }else {
                    $value = date($param, $value);
                }
                break;
            case 'object':
                if(is_object($value)) {
                    $value = json_encode($value, JSON_FORCE_OBJECT);
                }
                break;
            case 'array':
                $value = (array)$value;
            case 'json':
                $option = !empty($param) ? (int)$param : JSON_UNESCAPED_UNICODE;
                $value = json_encode($value, $option);
                break;
            case 'serialize':
                $value = serialize($value);
                break;
            default:
                break;
        }

        return $value;
    }

    /**
     * 类型转换
     * @param $data
     * @param $fieldType
     * [isset 是否设置, empty 是否为空, default 默认值(isset + empty), intval 取整数值, datetime 时间戳转日期]
     * [implode 数组转字符串, explode 字符串转数组, jsonEncode json编码, jsonDecode json解码]
     * [base64Encode base64编码, base64Decode base64解码, saveHtml 字符串转HTML实体(存储时使用), readHtml
     * [hash 哈希加密字符串, serialize 序列化, unserialize 反序列化]
     * @param array $hideField
     * @return array
     */
    protected function typeConversion($data, $fieldType, $hideField = [])
    {

        if(is_array($data)) {
            foreach($fieldType as $key => $value) {
                if(!empty($value)) {
                    $value = is_array($value) ? $value : [$value, ''];
                    $type = $value[0];
                    if(isset($data[$key])) {
                        if($type == 'datetime') {
                            $data[((empty($value[3])) ? $key : $value[3])] = empty($data[$key]) ? ($value[2] ? $value[2] : '') : date(($value[1] ? $value[1] : 'Y-m-d H:i:s'), $data[$key]);
                        }elseif($type == 'implode') {
                            $data[((empty($value[2])) ? $key : $value[2])] = is_array($data[$key]) ? implode((empty($value[1]) ? ',' : $value[1]), $data[$key]) : '';
                        }elseif($type == 'explode') {
                            if(is_string($data[$key])) {
                                $value[3] = isset($value[3]) && $value[3] == false ? true : false;
                                if($value[3] == false) {
                                    $data[((empty($value[2])) ? $key : $value[2])] = array_filter(explode((empty($value[1]) ? ',' : $value[1]), $data[$key]));
                                }else {
                                    $data[((empty($value[2])) ? $key : $value[2])] = explode((empty($value[1]) ? ',' : $value[1]), $data[$key]);
                                }
                            }else {
                                $data[((empty($value[2])) ? $key : $value[2])] = [];
                            }
                        }elseif($type == 'jsonEncode') {
                            $data[$key] = empty($data[$key]) ? '' : json_encode($data[$key], JSON_UNESCAPED_UNICODE);
                        }elseif($type == 'jsonDecode') {
                            $data[$key] = empty($data[$key]) ? [] : json_decode($data[$key], (isset($value[1]) && $value[1] == false) ? false : true);
                        }elseif($type == 'base64Encode') {
                            $data[$key] = is_array($data[$key]) ? '' : base64_encode($data[$key]);
                        }elseif($type == 'base64Decode') {
                            $data[$key] = is_array($data[$key]) ? '' : base64_decode($data[$key]);
                        }elseif($type == 'saveHtml') {
                            $data[$key] = empty($data[$key]) ? '' : htmlspecialchars($data[$key]);
                        }elseif($type == 'readHtml') {
                            $data[$key] = empty($data[$key]) ? '' : html_entity_decode($data[$key]);
                        }elseif($type == 'hash') {
                            $data[$key] = f('Str')::createHash($value[1], ($value[2] ? $value[2] : ''));
                        }elseif($type == 'serialize') {
                            $data[$key] = empty($data[$key]) ? '' : serialize($data[$key]);
                        }elseif($type == 'unserialize') {
                            $data[$key] = empty($data[$key]) ? '' : unserialize($data[$key]);
                        }
                    }else {
                        if($type == 'isset') {
                            $data[$key] = isset($data[$key]) ? $data[$key] : $value[1];
                        }elseif($type == 'empty') {
                            $data[$key] = empty($data[$key]) ? $value[1] : $data[$key];
                        }elseif($type == 'default') {
                            $data[$key] = isset($data[$key]) && !empty($data[$key]) ? $data[$key] : $value[1];
                        }elseif($type == 'intval') {
                            $data[$key] = isset($data[$key]) ? intval($data[$key]) : intval($value[1]);
                        }
                    }
                }
            }

            //该代码导致软删除失效
            /*if(!empty($this->deleteTime)) {
                $hideField[] = $this->deleteTime;
            }*/

            if(!empty($hideField)) {
                foreach($hideField as $val) {
                    if(isset($data[$val])) {
                        unset($data[$val]);
                    }
                }
            }
        }

        return $data;
    }


    /**
     * 初始化Db
     * @return Db
     */
    private function db()
    {
        return Db::table(table($this->table, $this->usePrefix));
    }

    /**
     * 初始化db
     * @return $this
     */
    private function initDb()
    {
        $this->db = [
            'init' => true,
            'fetchSql' => false, //调试
            'replace' => false,
            'comment' => '',
            'field' => '',
            'alias' => [],
            'join' => [],
            'leftJoin' => [],
            'rightJoin' => [],
            'limit' => [],
            'page' => [],
            'count' => [],
            'cache' => [],
            'where' => [],
            'order' => [],
        ];

        return $this;
    }

    /**
     * 初始化
     * @param string $alias
     * @return $this
     */
    public function init($alias = '')
    {

        $this->initDb();

        if(!empty($alias)) {
            $this->alias([table($this->table, $this->usePrefix) => $alias]);
        }

        return $this;
    }

    /**
     * 设置操作链
     * @param string $db
     * @param array $data save、delete使用
     * @return array|string|Db
     */
    private function setChain($db = '', $data = [])
    {

        if(empty($db)) {
            $db = $this->db();
        }

        $operation = 'select'; //select save delete insertAll
        if(!empty($data)) {
            if(!is_array($data)) {
                $operation = $data;
            }else {
                $operation = 'save';
            }
        }

        if($operation == 'save') {
            //链式replace
            if(isset($this->db['replace']) && $this->db['replace'] == true) {
                $db = $db->replace($this->db['replace']);
            }

            if(is_array($data)) {
                foreach($data as $key => $value) {
                    if(is_numeric($key)) {
                        if(is_array($value) && count($value) == 3 && $value[0] == 'exp') {
                            $db = $db->exp($value[1], $value[2]);
                            unset($data[$key]);
                        }
                    }
                }
            }
        }elseif($operation == 'select') {
            //链式sql注释
            if(!empty($this->db['comment'])) {
                $db = $db->comment($this->db['comment']);
            }

            //链式字段
            if(!empty($this->db['field'])) {
                $db = $db->field($this->db['field']);
            }

            //链式别名
            if(!empty($this->db['alias'])) {
                $db = $db->alias($this->db['alias']);
            }

            //链式关联
            if(!empty($this->db['join'])) {
                foreach($this->db['join'] as $value) {
                    call_user_func_array([$db, 'join'], $value);
                }
            }

            //链式左关联
            if(!empty($this->db['leftJoin'])) {
                foreach($this->db['leftJoin'] as $value) {
                    call_user_func_array([$db, 'leftJoin'], $value);
                }
            }

            //链式右关联
            if(!empty($this->db['rightJoin'])) {
                foreach($this->db['rightJoin'] as $value) {
                    call_user_func_array([$db, 'rightJoin'], $value);
                }
            }

            //链式group
            if(!empty($this->group)) {
                $db = $db->group($this->group);
            }

            //设置默认主键倒序排序
            if(empty($this->db['order']) && !empty($this->pk) && $this->db['order'] !== false) {
                $this->db['order'] = ["{$this->pk} DESC"];
            }

            //链式cache
            if(!empty($this->db['cache'])) {
                call_user_func_array([$db, 'cache'], $this->db['cache']);
            }
        }

        //链式where
        if(!empty($this->db['where'])) {
            foreach($this->db['where'] as $value) {
                call_user_func_array([$db, 'where'], $value);
            }
        }

        //处理软删除
        if(!empty($this->deleteTime)) {
            if(!empty($this->db['alias']) && $this->db['alias'][table($this->table, $this->usePrefix)]) {
                $db = $db->where("{$this->db['alias'][table($this->table, $this->usePrefix)]}.{$this->deleteTime}", $this->defaultSoftDelete);
            }else {
                $db = $db->where($this->deleteTime, $this->defaultSoftDelete);
            }
        }

        //链式排序
        if(!empty($this->db['order'])) {
            call_user_func_array([$db, 'order'], $this->db['order']);
        }

        //链式limit
        if(!empty($this->db['limit'])) {
            if(empty($this->db['limit'][1])) {
                $db = $db->limit($this->db['limit'][0]);
            }else {
                $db = $db->limit($this->db['limit'][0], $this->db['limit'][1]);
            }
        }

        //打印sql
        if(!empty($this->db['fetchSql'])) {
            $db = $db->fetchSql();
        }

        //重置$this->db
        $this->initDb();

        if($operation == 'save') {
            return [$db, $data];
        }else {
            return $db;
        }

    }

    /**
     * replace写入
     * @param bool $replace
     * @return $this
     */
    public function replace($replace = true)
    {
        $this->db['replace'] = $replace;
        return $this;
    }

    /**
     * sql添加注释内容
     * @param string $comment
     * @return $this
     */
    public function comment($comment = '')
    {
        if(!empty($comment)) {
            $this->db['comment'] = $comment;
        }
        return $this;
    }

    /**
     * 设置字段
     * @param string $field
     * @return $this
     */
    public function field($field = '*')
    {
        $this->db['field'] = $field;
        return $this;
    }

    /**
     * 设置表的别名
     * @param $alias
     * @return $this
     */
    public function alias($alias)
    {
        if(!empty($alias)) {
            if(is_string($alias)) {
                $alias = array_values(array_filter(explode(' ', $alias)));
                $len = count($alias);
                if($len == 2) {
                    $alias = [$alias[0] => $alias[1]];
                }elseif($len == 3) {
                    $alias = [$alias[0] => $alias[2]];
                }
            }

            if(is_array($alias)) {
                $this->db['alias'] = array_merge($this->db['alias'], $alias);
            }
        }

        return $this;
    }

    /**
     * 关联查询
     * @param $table
     * @param $on
     * @param bool $usePrefix
     * @return $this
     */
    public function join($table, $on, $usePrefix = true)
    {
        if(is_string($table)) {
            $table = array_filter(explode(' ', $table));
            $table = [table($table[0], $usePrefix) => $table[1]];
        }

        $this->db['join'][] = [$table, $on];
        return $this;
    }

    /**
     * 左关联查询
     * @param $table
     * @param $on
     * @param bool $usePrefix
     * @return $this
     */
    public function leftJoin($table, $on, $usePrefix = true)
    {
        if(is_string($table)) {
            $table = array_filter(explode(' ', $table));
            $table = [table($table[0], $usePrefix) => $table[1]];
        }

        $this->db['leftJoin'][] = [$table, $on];
        return $this;
    }

    /**
     * 右关联查询
     * @param $table
     * @param $on
     * @param bool $usePrefix
     * @return $this
     */
    public function rightJoin($table, $on, $usePrefix = true)
    {
        if(is_string($table)) {
            $table = array_filter(explode(' ', $table));
            $table = [table($table[0], $usePrefix) => $table[1]];
        }

        $this->db['rightJoin'][] = [$table, $on];
        return $this;
    }

    /**
     * where查询
     * @return $this
     */
    public function where()
    {
        $args = func_get_args();
        if(count($args) == 1 && is_numeric($args[0]) && $args[0] > 0) {
            $where = [$this->pk, $args[0]];
        }else {
            $where = $args;
        }

        if(!empty($where)) {
            $this->db['where'][] = $where;
        }
        return $this;
    }

    /**
     * 处理软删除
     * @return $this
     */
    public function softDelete()
    {
        $args = func_get_args();

        if(!empty($args)) {
            if(count($args) == 1) {
                if($args[0] === false) {
                    $this->deleteTime = '';
                }else {
                    $this->defaultSoftDelete = $args[0];
                }
            }
        }
        return $this;
    }

    /**
     * 合计函数
     * @param $data
     * @return $this
     */
    public function group($data)
    {
        $this->group = $data;
        return $this;
    }

    /**
     * order排序
     * @return $this
     */
    public function order()
    {
        $args = func_get_args();
        if(!empty($args)) {
            $this->db['order'] = $args;
        }
        return $this;
    }

    /**
     * limit查询
     * @param int $limit
     * @param int $offset
     * @return $this
     */
    public function limit($limit = 0, $offset = 0)
    {
        $this->db['limit'] = [$limit, $offset];
        return $this;
    }

    /**
     * 分页
     * @param int $page
     * @param int $size
     * @return $this
     */
    public function page($page = 0, $size = 20)
    {
        $page = max(1, intval($page));
        $size = intval($size);

        $this->db['page'] = [$page, $size];
        $this->limit(($page - 1) * $size, $size);
        return $this;
    }

    /**
     * 统计查询
     * @param string $field
     * @return $this
     */
    public function count($field = '*')
    {
        $this->db['count'] = $field;
        return $this;
    }

    /**
     * 缓存数据
     * @return $this
     */
    public function cache()
    {
        $args = func_get_args();
        if(!empty($args)) {
            $this->db['cache'] = $args;
        }
        return $this;
    }

    /**
     * 返回sql
     * @return $this
     */
    public function fetchSql()
    {
        $this->db['fetchSql'] = true;
        return $this;
    }

    /**
     * fetchSql别名
     * @return $this
     */
    public function debug()
    {
        return $this->fetchSql();
    }

    /**
     * 获取db属性
     * @param $name
     * @return mixed|string
     */
    public function getAttribute($name)
    {
        return $name ? $this->db[$name] : $this->db;
    }

    /**
     * 设置db属性
     * @param $key
     * @param $value
     */
    public function setAttribute($key, $value)
    {
        $this->db[$key] = $value;
    }


    /**
     * 添加
     * @param $data
     * @return bool|int|string
     * @throws \think\db\exception\DbException
     */
    public function add($data)
    {
        if(isset($data[$this->pk])) {
            unset($data[$this->pk]);
        }
        $this->db['where'] = [];
        return $this->save($data);
    }

    /**
     * 修改
     * @param $data
     * @return bool|int|string
     * @throws \think\db\exception\DbException
     */
    public function edit($data)
    {
        return $this->save($data);
    }

    /**
     * 保存前
     * @param $data
     * @param $where
     * @return array
     */
    public function beforeSave($data, $where)
    {
        return [$data, $where];
    }

    /**
     * 保存
     * @param $data
     * @return bool|int
     */
    public function save($data)
    {

        $pk = $this->pk;

        $id = isset($data[$pk]) ? intval($data[$pk]) : 0;
        if($id > 0) {
            $this->where([$pk => $id]);
            unset($data[$pk]);
        }

        $before = $this->beforeSave($data, $this->db['where'] ? $this->db['where'] : []);
        if($before == false) {
            return false;
        }else {
            list($data, $this->db['where']) = $before;
        }

        $where = $this->db['where'] ? $this->db['where'] : [];
        list($db, $data) = $this->setChain('', $data);

        $fetch_sql = $db->getOptions('fetch_sql');
        if(!empty($where)) {
            $result = $db->update($data);
        }else {
            $id = $result = $db->insertGetId($data);
        }
        if($fetch_sql == true) {
            return $result;
        }

        if($this->afterSave($id) == false) {
            return false;
        }

        return $result === false ? false : $id;
    }

    /**
     * 保存后
     * @param $id
     * @return mixed
     */
    public function afterSave($id)
    {
        return true;
    }

    /**
     * 添加多条
     * @param $data
     * @return int
     */
    public function insertAll($data)
    {
        return $this->setChain('', 'insertAll')->insertAll($data);
    }


    /**
     * 获取单个数据
     * @param string $id
     * @return mixed
     */
    public function getRow($id = '')
    {
        if(!empty($id)) {
            $this->where($id);
        }
        $result = $this->setChain()->findOrEmpty();

        $after = 'afterGetRow';
        if(method_exists($this, $after)) {
            $result = $this->$after($result);
        }else {
            $afterGetInfo = 'afterGetInfo';
            if($result && method_exists($this, $afterGetInfo)) {
                $result = $this->$afterGetInfo($result);
            }
        }

        return $result;
    }

    /**
     * 获取数据集
     * @param string $type list
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\DbException
     * @throws \think\db\exception\ModelNotFoundException
     */
    public function getList($type = '')
    {
        $data = $this->db;
        $db = $this->db();
        $result = $this->setChain($db)->select();

        //关联查询时此处存在bug
        //查询数量时应移除掉order、limit
        //需要重构此处和getCount()代码
        //调整 order 默认值，默认是false，查询时重置默认值为 主键倒序
        //此处查询数量调整为 getCount()
        if(empty($type)) {
            $total = $db->count(empty($data['count']) ? '*' : $data['count']);
        }

        if($db->getOptions('fetch_sql') == true) {
            return isset($total) ? [$result, $total] : [$result];
        }

        $result = $result->toArray();

        $after = 'afterGetList';
        if(method_exists($this, $after)) {
            $result = $this->$after($result);
        }else {
            $afterGetInfo = 'afterGetInfo';
            if($result && method_exists($this, $afterGetInfo)) {
                if(!empty($result) && is_array($result)) {
                    foreach($result as $key => $value) {
                        $result[$key] = $this->$afterGetInfo($value);
                    }
                }
            }
        }

        if($type == 'list') {
            return $result;
        }else {
            if(empty($data['page'])) {
                return [
                    'list' => $result,
                    'total' => $total ? $total : 0,
                ];
            }else {
                return [
                    'list' => $result,
                    'total' => $total ? $total : 0,
                    'page' => $data['page'][0],
                    'size' => $data['page'][1],
                ];
            }
        }
    }

    /**
     * 获取信息后(getRow、getList、getAllList通用)
     * @param $data
     * @return mixed
     */
    public function afterGetInfo($data)
    {
        return $data;
    }

    /**
     * 统计数量
     * @param string $field
     * @return int
     */
    public function getCount($field = '*')
    {
        return $this->setChain()->count($field);
    }

    /**
     * 删除
     * @return int
     * @throws \think\db\exception\DbException
     */
    public function delete()
    {
        $args = func_get_args();

        if(!empty($args)) {
            call_user_func_array([$this, 'where'], $args);
        }

        if(empty($this->deleteTime)) {
            return $this->setChain('', 'delete')->delete();
        }else {
            return $this->save([$this->deleteTime => time()]);
        }
    }

    /**
     * 原生查询
     * @param $sql
     * @return mixed
     */
    public function query($sql)
    {
        return Db::query($sql);
    }

    /**
     * 原生更新/写入
     * @param $sql
     * @return mixed
     */
    public function execute($sql)
    {
        return Db::execute($sql);
    }


    /**
     * 事务
     * @param $callback
     * @return mixed
     */
    public function transaction($callback)
    {
        return Db::transaction($callback);
    }

}
