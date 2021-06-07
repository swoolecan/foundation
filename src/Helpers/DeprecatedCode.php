<?php

declare(strict_types = 1);

namespace Framework\Baseapp\Helpers;

use Hyperf\Utils\Str;
use EasyWeChat\Factory;

/**
 * 系统操作相关功能
 */
Class DeprecatedCode
{
    /**
     * init resources
     */
    public static function initResourceDatas()
    {
        $resourceFile = self::getCachePath('resource');
        if (!file_exists($resourceFile)) {
            $dResources = self::getDefaultResources();
            self::cacheResources($dResources);
        }
        return require($resourceFile);
    }

    public static function cacheResources($datas)
    {
        $results = [];
        foreach ($datas as $rCode => $rData) {
            $code = $rCode;//empty($rData['module']) ? $rCode : "{$rCode}-{$rData['module']}";
            $results[$code] = self::_cacheResource($rCode, $rData);
        }
        $resourceFile = self::getCachePath('resource');
        $str = "<?php\nreturn " . var_export($results, true) . ' ;';
        file_put_contents($resourceFile, $str);
        return true;
    }

    public static function _cacheResource($rCode, $rData)
    {
        $data = [];
        $code = '';//!empty($rData['module']) ? self::toUpper($rData['module']) . '\\' : '';
        $code .= self::toUpper($rCode);

        foreach (['request', 'resource', 'model', 'service', 'repository'] as $elem) {
            if (isset($rData[$elem]) && !empty($rData[$elem]) && !is_array($rData[$elem])) {
                $data[$elem] = $rData[$elem];
                continue;
            }
            $elemCode = self::toUpper($elem);
            $elemCodes = $elem == 'repository' ? 'Repositories' : "{$elemCode}s";
            if ($elem == 'model') {
                $class = "App\\{$elemCodes}\\{$code}";
            } else {
                $class = "App\\{$elemCodes}\\{$code}{$elemCode}";
            }
            if (class_exists($class)) {
                $data[$elem] = $class;
            } else {
                //echo $class . "====\n";
            }

            if (in_array($elem, ['request', 'resource']) && isset($rData[$elem]) && is_array($rData[$elem])) {
                foreach ($rData[$elem] as $key => $value) {
                    $codeExt = $code . ucfirst($key);
                    $class = !empty($value) ? $value : "App\\{$elemCodes}\\{$codeExt}{$elemCode}";
                    if (class_exists($class)) {
                        $data[$elem . '-' . $key] = $class;
                    } else {
                        //echo $class . "\n";
                    }
                }
            }

        }
        return $data;
    }

    /**
     * init routes
     */
    public static function initRouteDatas()
    {
        $routeFile = self::getCachePath('route');
        if (!file_exists($routeFile)) {
            $dResources = self::getDefaultRoutes();
            self::cacheRoutes($dResources);
        }
        return require($routeFile);
    }

    public static function cacheRoutes($datas)
    {
        $results = [];
        foreach ($datas as $rData) {
            $rCode = $rData['resource'];
            $routes = self::_cacheRoute($rCode, $rData);
            $results[$rCode] = isset($results[$rCode]) ? array_merge($results[$rCode], $routes) : $routes;
        }
        $routeFile = self::getCachePath('route');
        $str = "<?php\nreturn " . var_export($results, true) . ' ;';
        file_put_contents($routeFile, $str);
        return true;
    }

    public static function _cacheRoute($rCode, $rData)
    {
        $actionMethods = [
            'index' => 'get',
            'put' => 'put',
            'store' => 'post',
            'show' => 'get',
            'delete' => 'post'
        ];
        $data = [];
        $baseCallback = 'App\Controllers';
        $baseCallback .= !empty($rData['controller_pre']) ? '\\' . self::toUpper($rData['controller_pre']) . '\\' : '\\';
        $baseCallback .= self::toUpper($rCode) . 'Controller@';
        $actions = isset($rData['action']) ? (array) $rData['action'] : array_keys($actionMethods);
        foreach ($actions as $action) {
            $method = !empty($rData['method']) ? $rData['method'] : (isset($actionMethods[$action]) ? $actionMethods[$action] : 'get');
            $method = (array) explode(',', $method);
            foreach ($method as & $value) {
                $value = strtoupper($value);
            }
            $data[$action] = [
                'method' => $method,
                'path' => self::_getRoutePath($action, $rData),
                'callback' => $baseCallback . $action,
            ];
        }
        return $data;
    }

    public static function _getRoutePath($action, $rData)
    {
        if (isset($rData['path'])) {
            return $rData['path'];
        }
        $basePath = !empty($rData['path_pre']) ? "/{$rData['path_pre']}" : '';
        $path = $basePath . "/{$rData['resource']}s";
        if (in_array($action, ['put', 'delete', 'show'])) {
            return $path . "/{id:\d+}";
        }
        if (in_array($action, ['index', 'store'])) {
            return $path;
        }
        return $path . "/{$action}";
    }

    public static function _getRouteMethod($action, $rData)
    {
    }

    public static function getApp($config)
    {
        $config = [
            'app_id' => $config['app_id'],
            'secret' => $config['secret'],
            'response_type' => 'array',
            'log' => [
                'level' => 'debug',
                'file' => '/tmp/wechat.log',
            ],
        ];
        return Factory::officialAccount($config);
    }

    public static function sendNotice($exception)
    {
        $config = \common\helpers\InitFormat::getBaseParams('config/params-local.php');
        $config = $config['errorNotice'];
        $class = get_class($exception);
        if ($config['noNotice'] || in_array($class, $config['ignore']) || $exception->statusCode == '200') {
            return ;
        }
        $app = self::getApp($config);

        $url = isset($_SERVER['REQUEST_URI']) ? $_SERVER['REQUEST_URI'] : '';
        $url = substr($url, 0, 50);
        $params = [
            'template_id' => $config['template_id'],
            'data' => [
                'first' => '测试错误通知-' . $exception->getMessage(),
                'time' => date('Y-m-d H:i:s'),
                'ip_list' => Yii::$app->id,
                'sec_type' => get_class($exception),
                'remark' => $exception->getLine() . "--{$url}"  . "\n" . $exception->getFile(),
            ],
        ];
        foreach ($config['touser'] as $touser) {
            $params['touser'] = $touser;
            $app->template_message->send($params);
        }
    }

    protected static function getCachePath($type)
    {
        return BASE_PATH . '/runtime/cache/' . $type . '.php';
    }

    protected static function getDefaultRoutes()
    {
        return [
            ['resource' => 'user', 'module' => 'passport'], 
            ['resource' => 'permission', 'module' => 'passport'], 
            ['resource' => 'permission', 'module' => 'passport', 'action' => 'tree'], 
            ['resource' => 'role', 'module' => 'passport'], 
            ['resource' => 'resource', 'module' => 'passport'], 
            ['resource' => 'manager', 'module' => 'passport'], 
        ];
    }

    protected static function getDefaultResources()
    {
        return [
            'entrance' => [
                'request' => [
                    'signupin' => 'App\\Requests\\EntranceSignupinRequest',
                    'token' => 'App\\Requests\\EntranceTokenRequest',
                ],
            ],
            'userPermission' => ['service' => 'App\\Services\\UserPermissionService'],
            'easysms' => ['service' => 'Framework\\Baseapp\\Services\\EasysmsService'],
            'user' => ['module' => 'passport'], 
            'permission' => ['module' => 'passport'], 
            'role' => ['module' => 'passport'], 
            'resource' => ['module' => 'passport'], 
            'manager' => ['module' => 'passport'], 
        ];
    }

    public static function toUpper($str)
    {
        return Str::studly($str);
    }

    public static function setCacheElems($type, $datas)
    {
        $cacheFile = self::getCachePath('elem-' . $type);
        $str = "<?php\nreturn " . var_export($datas, true) . ' ;';
        file_put_contents($cacheFile, $str);
        return true;
    }

    public static function getCacheElems($type)
    {
        $cacheFile = self::getCachePath('elem-' . $type);
        if (!file_exists($cacheFile)) {
            return [];
        }
        return require($cacheFile);
    }

    protected function _writeLog($return, $mobile, $content, $sort, $startTime)
    {
        $endTime = microtime(true);
        $timeUsed = number_format($endTime - $startTime, 3);
        $currentDate = date('Y-m-d H:i:s');
        $content = "==={$mobile}==={$currentDate}===\r\n"
            . "---{$timeUsed}---{$return['message']}---{$return['extinfo']}---\r\n"
            . "---{$content}---\r\n\r\n";

        $logFile = \Yii::getAlias('@runtime') . '/logs/sms/' . date('Y-m-d') . '/' . $sort;
        $logFile .= $return['status'] ? '_success.log' : '_error.log';
        $path = dirname($logFile);
        if (!is_dir($path)) {
            \yii\helpers\FileHelper::createDirectory($path);
        }
        file_put_contents($logFile, $content, FILE_APPEND);

        return true;
    }
}
