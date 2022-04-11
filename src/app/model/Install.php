<?php
namespace app\model;

use think\facade\Db;

class Install
{

    /**
     * 安装
     * @param array $field
     * @return array
     * @throws \ReflectionException
     */
    public function install($field = ['status'])
    {
        return $this->run($field, __FUNCTION__);
    }

    /**
     * 升级
     * @param array $field
     * @return array
     * @throws \ReflectionException
     */
    public function upgrade($field = ['status'])
    {
        return $this->run($field, __FUNCTION__);
    }

    /**
     * 卸载
     * @param array $field
     * @return array
     * @throws \ReflectionException
     */
    public function uninstall($field = ['status'])
    {
        return $this->run($field, __FUNCTION__);
    }

    /**
     * 运行
     * @param array $field
     * @param $function
     * @return array
     * @throws \ReflectionException
     */
    public function run($field = ['status'], $function)
    {
        $modules = get_config('module');
        $result = [];
        foreach($modules as $value) {
            $sql = m($value)->$function();
            $result[$value] = ['status' => true, 'sql' => ''];
            if($sql) {
                $result[$value]['sql'] = $sql;
                if(Db::query($sql) === false) {
                    $result[$value]['status'] = false;
                }
            }

            if(!empty($field)) {
                if(!in_array('status', $field)) {
                    unset($result[$value]['status']);
                }
                if(!in_array('sql', $field)) {
                    unset($result[$value]['sql']);
                }
            }
        }
        return $result;
    }
}
