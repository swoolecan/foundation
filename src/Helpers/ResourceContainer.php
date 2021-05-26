<?php
declare(strict_types = 1);

namespace Wangcan\Foundation\Helpers;

/**
 * 系统资源
 */
trait TraitResourceManager
{

    protected $resources;
    protected $objects = [];
    public $appCode;
    public $params = [];

    public function setParams($params)
    {
        $this->params = array_merge($this->params, $params);
    }

    public function getResourceCode($class)
    {
        $elems = explode('\\', $class);
        $count = count($elems);
        if ($count < 3) {
            return $class;
        }
        $code = $elems[2];
        $type = $elems[1];

        $type = Str::singular($type);

        $pos = strripos($code, $type);
        if ($pos !== false) {
            $code = substr($code, 0, $pos);
        }
        //$code = Str::snake($code, '-');
        $code = lcfirst($code);
        return $code;
    }

    public function getObject($type, $code, $params = [])
    {
        $class = $this->getClassName($type, $code);
        if (empty($class)) {
            $this->throwException(500, '资源不存在-' . $class . '-' . $type . '==' . $code);
        }

        if (isset($this->objects[$class])) {
            return $this->objects[$class];
        }
        $obj = make($class);
        if (method_exists($obj, 'init')) {
            $obj->init($params);
        }
        //echo get_class($obj) . "\n rrrrrr \n";
        $this->objects[$class] = $obj;
        return $obj;
    }

    public function getClassName($type, $code)
    {
        if (!isset($this->resources[$code])) {
            $code = $this->getResourceCode($code);
        }
        if (!isset($this->resources[$code])) {
            return false;
        }

        $info = $this->resources[$code];
        $class = isset($info[$type]) ? $info[$type] : false;
        if (empty($class) && $type == 'service-repo') {
            $class = isset($info['service']) ? $info['service'] : (isset($info['repository']) ? $info['repository'] : '');
        }
        return strval($class);
    }

    public function getIp()
    {
        $ip = $this->request->getHeader('x-real-ip');
        if (empty($ip)) {
            return '';
        }
        if (is_string($ip)) {
            return $ip;
        }
        return $ip[0];
    }

    public function throwException($code, $message = null)
    {
        throw new BusinessException($code, $message);
    }

    /**
     * @Cacheable(prefix="common-resource")
     */
    protected function getResourceDatas($key = 'resources')
    {
        $datas = $this->config->get('resource');
        return $datas;
    }

    public function initRouteDatas()
    {
        $routes = $this->_routeDatas('routes');
        //$routes = $this->config->get('routes');
        //print_r($routes);
        if (!$routes || !isset($routes[$this->appCode])) {
            $this->throwException(500, '路由信息不存在-' . $this->appCode);
        }
        return $routes[$this->appCode];
    }

    /**
     * @Cacheable(prefix="common-route")
     */
    protected function _routeDatas($key)
    {
        //$routes = require('/data/htmlwww/docker/container/passport/config/autoload/routes.php');
        if ($this->appCode == 'passport') {
            return $this->config->get('routes');
        }
        return null;
    }

    public function formatClass($elem, $code)
    {
        $codeUpper = Str::studly($code);
        $elemUpper = Str::studly($elem);
        $elemPath = $elem == 'repository' ? 'Repositories' : ($elem == 'collection' ? 'Resources' : "{$elemUpper}s");
        $class = "App\\{$elemPath}\\{$codeUpper}";

        if (!in_array($elem, ['model', 'resource'])) {
            $class .= "{$elemUpper}";
        }
        return $class;
    }
}
