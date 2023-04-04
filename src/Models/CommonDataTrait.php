<?php

declare(strict_types = 1);

namespace Swoolecan\Foundation\Models;

trait CommonDataTrait
{
    public function getAttachmentInfo($params)
    {
        return $this->getAttachmentInfos($params, true);
    }

    public function getAttachmentUrl($params)
    {
        return $this->getAttachmentInfos($params, true, true);
    }

    public function getAttachmentInfos($params, $isSingle = false, $onlyUrl = false)
    {
        $currentAppcode = $this->getAppcode();
        $params['app'] = $params['app'] ?? $currentAppcode;
        $params['info_table'] = $params['info_table'] ?? $this->getResource()->getResourceCode(get_called_class(), false);
        if (!isset($params['info_id'])) {
            $key = $this->getKeyField();
            $params['info_id'] = $this->$key;
        }

        if ($currentAppcode == 'passport') {
            $attachmentInfo = $this->getResource()->getObject('repository', 'attachmentInfo');
            return $isSingle ? $attachmentInfo->getData($params, $onlyUrl) : $attachmentInfo->getDatas($params);
        }
        $class = "\Framework\Baseapp\RpcClient\PassportRpcClient";
        $client = $this->getResource()->getObjectByClass($class);
        return $isSingle ? $client->getAttachmentInfo($params, $onlyUrl) : $client->getAttachmentInfos($params);
    }

    public function getTagInfoDatas($params)
    {
        $currentAppcode = $this->getAppcode();
        $params['app'] = $params['app'] ?? $currentAppcode;
        $params['info_table'] = $params['info_table'] ?? $this->getResource()->getResourceCode(get_called_class(), false);
        if (!isset($params['info_id'])) {
            $key = $this->getKeyField();
            $params['info_id'] = $this->$key;
        }

        if ($currentAppcode == 'passport') {
            $tagInfo = $this->getResource()->getObject('model', 'tagInfo');
            return $tagInfo->getDatas($params);
        }

        $class = "\Framework\Baseapp\RpcClient\PassportRpcClient";
        $client = $this->getResource()->getObjectByClass($class);
        return $client->getTagInfoDatas($params);
    }

    public function updateTagInfos($params)
    {
        $currentAppcode = $this->getAppcode();
        $params['app'] = $params['app'] ?? $currentAppcode;
        $params['info_table'] = $params['info_table'] ?? $this->getResource()->getResourceCode(get_called_class(), false);
        if (!isset($params['info_id'])) {
            $key = $this->getKeyField();
            $params['info_id'] = $this->$key;
        }

        if ($currentAppcode == 'passport') {
            $tagInfo = $this->getResource()->getObject('model', 'tagInfo');
            return $tagInfo->createTagInfos($params);
        }

        $class = "\Framework\Baseapp\RpcClient\PassportRpcClient";
        $client = $this->getResource()->getObjectByClass($class);
        return $client->createTagInfos($params);
    }

    public function deleteTagInfos($params)
    {
        $currentAppcode = $this->getAppcode();
        $params['app'] = $params['app'] ?? $currentAppcode;
        $params['info_table'] = $params['info_table'] ?? $this->getResource()->getResourceCode(get_called_class(), false);
        if (!isset($params['info_id'])) {
            $key = $this->getKeyField();
            $params['info_id'] = $this->$key;

            $this->getModelObj('passport-tagInfo')->where($params)->delete();
            return true;
        }
        $params['info_id'] = (array) $params['info_id'];
        $this->getModelObj('passport-tagInfo')->where($params)->whereIn('info_id', $params['info_id'])->delete();
        return true;
    }

    public function getCodeTag($code, $onlyCode = true)
    {
        $info = $this->getModelObj('passport-tag')->where('code', $code)->first();

        if (empty($info)) {
            return false;
        }
        return $onlyCode ? $info->code : $info;
    }

    public function findCreateTag($name, $onlyCode = true)
    {
        $info = $this->getModelObj('passport-tag')->findCreate($name);
        return $onlyCode ? $info->code : $info;
    }

    public function formatTagDatas($returnType = null)
    {
        $tagInfos = $this->getTagInfoDatas([]);
        switch ($returnType) {
        case 'string':
        case 'keyvalue':
            $datas = [];
            foreach ($tagInfos as $tagInfo) {
                $datas[$tagInfo['tag_code']] = $tagInfo['name'];
            }
            if ($returnType == 'string') {
                return implode(' ; ', $datas);
            }
            return $datas;
        default:
            return $tagInfos;
        }
    }
}
