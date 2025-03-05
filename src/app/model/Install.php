<?php
namespace app\model;

use app\util\File;

class Install
{

    private $lockFile = 'install.lock';

    /**
     * @param $file
     * @return string|string[]
     */
    private function getInstallPath($file = false)
    {
        $dir = app()->getRuntimePath() . 'install/';
        $data = [$dir, "{$dir}{$this->lockFile}"];
        return $file === false ? $data : $data[$file];
    }

    /**
     * 是否安装
     * @return bool
     */
    public function isInstall()
    {
        return file_exists($this->getInstallPath(1));
    }

    /**
     * 读取模块
     * @param $type 模块类型
     * @return mixed
     */
    public function readModules($type = '')
    {
        $module = get_config('module');
        return empty($type) ? $module : $module[$type];
    }

    public function insModule($name, $function)
    {
        $result = 0;
        $msg = '';
        $sql = '';
        if (class_exists(app_namespace('model', $name))) {
            $sql = m($name)->$function();
            if (!empty($sql)) {
                if (is_array($sql)) {
                    $result = [];
                    $msg = [];
                    foreach ($sql as $key => $value) {
                        if (f('Db')::query($value) !== false) {
                            $result[$key] = 1;
                            $msg[$key] = 'ok';
                        }
                    }
                } else {
                    if (f('Db')::query($sql) !== false) {
                        $result = 1;
                        $msg = 'ok';
                    }
                }
            }
        } else {
            $msg = "{$name}模型不存在";
        }

        return ['status' => $result, 'msg' => $msg, 'sql' => $sql];
    }

    /**
     * 安装
     * @param $modules
     * @return void
     */
    public function install($modules = null)
    {
        if ($this->isInstall()) {
            throw_exception(1, "请删除install.lock文件");
            return false;
        }

        $fun = __FUNCTION__;

        $path = $this->getInstallPath();

        $file = new File();
        $insLogName = $path[0] . date('Y-m-d') . '.log';

        if ($modules === null) {
            $modules = $this->readModules();
        } elseif (is_string($modules)) {
            $modules = [$modules];
        }
        $result = [];
        foreach ($modules as $type => $value) {
            if (is_string($value)) {
                $res = $this->insModule($value, $fun);
                $result[$value] = [
                    'status' => $res['status'],
                    'msg' => $res['msg'],
                ];
                $file->outlog($insLogName, $res, "{$fun}--{$type}--{$value}");
            } else {
                foreach ($value as $val) {
                    $res = $this->insModule($val, $fun);
                    $result[$type][$val] = [
                        'status' => $res['status'],
                        'msg' => $res['msg'],
                    ];
                    $file->outlog($insLogName, $res, "{$fun}--{$type}--{$val}");
                }
            }
        }

        $file->write($path[1], time());

        return $result;
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
}
