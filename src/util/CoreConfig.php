<?php
namespace core\util;

use think\App;

/**
 * 配置管理类
 * @package think
 */
class CoreConfig
{

    /**
     * 配置文件目录
     * @var string
     */
    protected $path;

    /**
     * 配置文件后缀
     * @var string
     */
    protected $ext;

    /**
     * 构造方法
     * @access public
     */
    public function __construct(string $path = null, string $ext = '.php')
    {
        $this->path = $path ? $path : '';
        $this->ext = $ext;
    }

    public static function __make(App $app)
    {
        $path = $app->getAppPath() . 'config' . DIRECTORY_SEPARATOR;
        $ext = $app->getConfigExt();

        return new static($path, $ext);
    }

    /**
     * 加载配置文件（多种格式）
     * @param string $file
     * @param string $name
     * @return array
     */
    public function load(string $file, string $name = ''): array
    {
        if (is_file($file)) {
            $filename = $file;
        } elseif (is_file($this->path . $file . $this->ext)) {
            $filename = $this->path . $file . $this->ext;
        }

        if (isset($filename)) {
            return $this->parse($filename, $name);
        }

        return [];
    }

    /**
     * 解析配置文件
     * @param string $file
     * @return array
     */
    protected function parse(string $file): array
    {
        $type = pathinfo($file, PATHINFO_EXTENSION);
        $config = [];
        switch ($type) {
            case 'php':
                $config = include $file;
                break;
            case 'yml':
            case 'yaml':
                if (function_exists('yaml_parse_file')) {
                    $config = yaml_parse_file($file);
                }
                break;
            case 'ini':
                $config = parse_ini_file($file, true, INI_SCANNER_TYPED) ?: [];
                break;
            case 'json':
                $config = json_decode(file_get_contents($file), true);
                break;
        }

        return is_array($config) ? $config : [];
    }

    /**
     * 获取配置参数 为空则获取所有配置
     * @access public
     * @param string $name 配置参数名（支持多级配置 .号分割）
     * @param mixed $default 默认值
     * @return mixed
     */
    public function get(string $name = null, $default = null)
    {
        // 无参数时获取所有
        if (empty($name)) {
            return [];
        }

        if (false === strpos($name, '.')) {
            return $this->load($name);
        }

        $name = array_filter(explode('.', $name));
        $file = strtolower(array_shift($name));
        $config = $this->load($file);

        foreach ($name as $val) {
            if (isset($config[$val])) {
                $config = $config[$val];
            } else {
                $config = $default;
            }
        }

        return $config;
    }

}