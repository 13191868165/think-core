<?php
namespace app\util;

class DocParser
{
    private $reflector;

    public function __construct($class)
    {
        if (!class_exists($class)) {
            throw new \Exception("Class $class does not exist.");
        }
        $this->reflector = new \ReflectionClass($class);
    }

    /**
     * 获取方法
     * @return \ReflectionMethod[]
     */
    public function getMethod($filter = null)
    {
        return $this->reflector->getMethods($filter);
    }

    /**
     * 格式化值
     * @param $value
     * @return array|string|string[]
     */
    private function formatValue($value)
    {
        $value = trim($value);
        $start = mb_substr($value, 0, 1);
        $end = mb_substr($value, -1);
        if ($start == '[' && $end == ']') {
            $value = explode(',', mb_substr($value, 1, -1));
            foreach ($value as $key => $val) {
                $value[$key] = trim($val);
            }
        }

        return $value;
    }

    /**
     * 解析文档注释
     * @param $docComment
     * @param $useArr
     * @return array
     */
    public function parseDocComment($docComment, $useArr = true)
    {
        if ($docComment === false) {
            return [];
        }

        $lines = explode("\n", $docComment);
        $parsed = [];
        foreach ($lines as $line) {
            $line = trim(trim($line, "/* \t"));
            $start = mb_substr($line, 0, 1);

            if ($start === '@') {
                $parts = explode(' ', $line, 2);
                $tag = ltrim($parts[0], $start);
                $value = $parts[1] ?? '';
                if (!isset($parsed[$tag])) {
                    $parsed[$tag] = [];
                }
                $parsed[$tag][] = $value;
            } elseif ($useArr && $start === '#') {
                $parts = explode(' ', $line, 2);
                $tag = $parts[0];
                $value = $parts[1] ?? '';
                if (!isset($parsed[$tag])) {
                    $parsed[$tag] = [];
                }
                $parsed[$tag][] = $this->formatValue($value);
            }
        }

        return $parsed;
    }

    /**
     * 获取类注释
     * @return array
     */
    public function getClassDoc()
    {
        return $this->parseDocComment($this->reflector->getDocComment());
    }

    /**
     * 获取方法注释
     * @param $method
     * @return array
     */
    public function getMethodDoc($method, $getName = false)
    {
        $doc = $this->parseDocComment($method->getDocComment());
        return $getName ? ['doc' => $doc, 'name' => $method->getName()] : $doc;
    }

    /**
     * 获取所有方法注释
     * @return array
     */
    public function getMethodDocs($filter = null)
    {
        $methods = $this->reflector->getMethods($filter);
        $docs = [];
        foreach ($methods as $method) {
            $docs[$method->getName()] = $this->getMethodDoc($method);
        }
        return $docs;
    }
}

?>
