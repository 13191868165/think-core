<?php
namespace app\util;

use app\facade\Validate;

class Routes
{
    //用户信息
    //id 用户主键，如果不存在主键则 filterUserRules 永远返回true
    //admin_type 管理员类型
    //admin_rules 管理员权限
    private $user = [];
    //路由信息
    private $routes = [];

    //输出配置
    private $output = [
        'dir' => 'config', //输出文件夹
        'file' => 'adminPermission', //输出文件名
    ];

    public function setUser($user)
    {
        $this->user = $user;
        return $this;
    }

    /**
     * 获取路由数据
     * @return mixed
     */
    public function getRoutesData()
    {
        return get_config('routes');
    }

    /**
     * 过滤隐藏菜单
     * @param $route
     * @return bool
     */
    private function filterHidden($route)
    {
        return !(isset($route['meta']) && !empty($route['meta']['hidden']));
    }

    /**
     * 过滤角色组权限
     * @param $route
     * @return bool
     */
    private function filterTypeRules($route)
    {
        //路由可用管理员类型，true所有管理员可见
        $types = isset($route['meta']) && isset($route['meta']['type']) && !empty($route['meta']['type'])
            ? $route['meta']['type']
            : false;

        if ($types === false) {
            return true;
        } else {
            if (is_numeric($types)) {
                $types = [$types];
            }

            //管理员类型：1:创始人 2:管理员 3:商户
            $type = intval($this->user['admin_type']);
            if (in_array($type, $types)) {
                return true;
            }
        }

        return false;
    }

    /**
     * 过滤用户授权权限
     * @param $route
     * @return bool 如果用户主键不存在，则永远返回true
     */
    private function filterUserRules($route)
    {
        //用户存在主键则过滤用户授权权限
        if (!empty($this->user['id'])) {
            $rules = $this->user['admin_rules'];
            //创始人 || 存在子路由 || 不处理隐藏的路由 || 用户授权列表存在数据且路由已授权
            if (
                $this->user['admin_type'] == 1 ||
                (isset($route['children']) && count($route['children'])) ||
                (is_array($rules) && in_array($route['name'], $rules))
            ) {
                return true;
            }

            return false;
        } else {
            return true;
        }
    }

    /**
     * 过滤路由
     * @param $routes
     * @return array|mixed|null
     */
    public function filterRoutes($routes = null)
    {
        foreach ($routes as $key => $route) {
            if (isset($route['children']) && count($route['children']) > 0) {
                if ($this->filterHidden($route) && $this->filterTypeRules($route)) {
                    $children = $this->filterRoutes($route['children'], $route['path']);
                    if (!empty($children)) {
                        $routes[$key]['children'] = array_values($children);
                    } else {
                        unset($routes[$key]);
                    }
                } else {
                    unset($routes[$key]);
                }
            } else {
                if (
                    !($this->filterHidden($route) &&
                        $this->filterTypeRules($route) &&
                        $this->filterUserRules($route))
                ) {
                    unset($routes[$key]);
                }
            }
        }
        $routes = array_values($routes);

        return $routes;
    }

    /**
     * 获取路由
     * @return array|mixed|null
     */
    public function getRoutes()
    {
        $routes = $this->getRoutesData();

        return $this->filterRoutes($routes);
    }

    /**
     * 根据用户获取路由
     * @param $user
     * @return array|mixed|null
     */
    public function getRoutesByUser($user)
    {
        if (empty($user) || empty($user['admin_type'])) {
            return ['code' => 1, 'msg' => '用户不存在'];
        }

        $this->setUser($user);

        return $this->getRoutes();
    }

    /**
     * 获取输出目录
     * @param $api
     * @return string
     */
    public function getOutputPath()
    {
        return app_path() . "{$this->output['dir']}/{$this->output['file']}.php";
    }

    public function getAdminPermission()
    {
        return get_config($this->output['file']);
    }

    /**
     * 写入管理员权限
     * @param $data
     * @return void
     */
    public function writeAdminPermission($data)
    {
        $path = $this->getOutputPath();
        $tab = '    ';

        $all = $this->getAdminPermission();
        foreach ($data as $key => $value) {
            if (!isset($all[$key])) {
                $all[$key] = [];
            }
            if (!in_array($value, $all[$key])) {
                $all[$key][] = $value;
            }
        }

        $content = [];
        foreach ($all as $key => $value) {
            $content[] = "'{$key}' => [";
            if (!empty($value)) {
                $text = [];
                foreach ($value as $val) {
                    if (!empty($val)) {
                        $text[] = "'{$val}'";
                    }
                }
                $text = $tab . join("," . PHP_EOL . "{$tab}{$tab}", $text);
                $content[] = $text;
            }
            $content[] .= "],";
        }
        $content = join(' ' . PHP_EOL . $tab, $content);

        $output = <<<EOF
<?php

return [
{$tab}{$content}
];
EOF;

        return file_put_contents($path, $output);
    }

    public function removePermission()
    {
        $path = $this->getOutputPath();
        if (file_exists($path)) {
            unlink($path);
        }
    }
}
