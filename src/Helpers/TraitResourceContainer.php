<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Helpers;

use Swoolecan\Foundation\Helpers\AgentTool;

/**
 * 系统资源
 */
trait TraitResourceContainer
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
        $module = $elems[0];
        $module = str_replace('Module', '', $module);
        $module = lcfirst($module);

        $type = $this->strOperation($type, 'singular');//Str::singular($type);

        $pos = strripos($code, $type);
        if ($pos !== false) {
            $code = substr($code, 0, $pos);
        }
        //$code = $this->strOperation($code, 'snake', '-');//Str::snake($code, '-');
        $code = lcfirst($code);
        return $module . '-' . $code;
    }

    public function getObject($type, $code, $params = [])
    {
        $class = $this->getClassName($type, $code);
        if (empty($class)) {
            $this->throwException(500, '资源不存在-' . $class . '-' . $type . '==' . $code);
        }

        if (isset($this->objects[$class])) {
            //return $this->objects[$class];
        }
        $obj = $this->getObjectByClass($class);
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
        $class = $info[$type] ?? false;
        /*if (empty($class) && $type == 'service-repo') {
            $class = isset($info['service']) ? $info['service'] : (isset($info['repository']) ? $info['repository'] : '');
        }*/
        return strval($class);
    }

    public function getIp()
    {
        $ip = $this->request->header('x-real-ip');
        if (empty($ip)) {
            return '';
        }
        if (is_string($ip)) {
            return $ip;
        }
        return $ip[0];
    }

    public function initRouteDatas()
    {
        $routes = $this->_routeDatas('routes');
        //$routes = $this->config->get('routes');
        //print_r($routes);
        if (!$routes || !isset($routes[$this->getAppcode()])) {
            $this->throwException(500, '路由信息不存在-' . $this->getAppcode());
        }

        return $routes[$this->getAppcode()];
    }

    public function formatClass($elem, $code, $app = 'app')
    {
        $codeUpper = $this->strOperation($code, 'studly');//Str::studly($code);
        $elemUpper = $this->strOperation($elem, 'studly');//Str::studly($elem);
        $elemPath = $elem == 'repository' ? 'Repositories' : ($elem == 'collection' ? 'Resources' : "{$elemUpper}s");
        $app = $app != 'app' ? 'Module' . ucfirst($app) : 'App';
        $class = "{$app}\\{$elemPath}\\{$codeUpper}";

        if (!in_array($elem, ['model', 'resource'])) {
            $class .= "{$elemUpper}";
        }
        return $class;
    }

    public function strOperation($string, $operation, $params = [])
    {
        return $string;
    }

    public function getPointDomain($code = '')
    {
        return '';
    }

	public function isMobile()
	{
		return AgentTool::isMobile();
	}
}
