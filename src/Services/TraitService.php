<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Services;

trait TraitService
{
    /** 
     * @var AbstractRepository
     */
    protected $repository;
    //protected $pointRepository;

    public function init()
    {
        if (empty($this->noRepository)) {
            $this->repository = $this->resource->getObject('repository', get_called_class());
            //$this->pointRepository = empty($pointRepository) ? $this->repository : $resource->getObject('repository', $repositoryCode);
        }
    }

    public function __call($name, $arguments)
    {   
        return $this->repository->{$name}(...$arguments);
    }

    public function getRepositoryObj($code = '', $params = [])
    {
        $code = !empty($code) ? $code : get_called_class();
        return $this->resource->getObject('repository', $code);
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
