<?php

namespace Swoolecan\Foundation\Controllers;

trait OperationTrait
{
    public function listinfoGeneral()
    {
        $params = $this->request->all();
        $scene = $params['point_scene'] ?? 'list';
        $simpleResult = $params['simple_result'] ?? false;
        
        $repository = $this->getRepositoryObj();
        $repository->currentScene = $scene;
        $repository = $this->dealCriteria($scene, $repository, $params);
        if (in_array($scene, $this->pageScenes())) {
            $perPage = $params['per_page'] ?? 25;
            $list = $repository->paginate(intval($perPage));
        } else {
            $list = $repository->all();
        }

        //$collection = $this->getCollectionObj(null, ['resource' => $list, 'scene' => $scene, 'repository' => $repository, 'simpleResult' => $simpleResult]);
        $collection = $this->getCollectionObj($list, $scene, null, $simpleResult);
        return $collection->toResponse($this->request);
    }

    protected function pageScenes()
    {
        return ['list', 'pop'];
    }

    public function listinfoTree()
    {
        $repository = $this->getRepositoryObj();
        $data = $repository->getTreeLists();
        $data['code'] = 200;
        $data['message'] = 'OK';
        return response()->json($data);
        return $this->success();
    }

    public function addGeneral()
    {
        $repository = $this->getRepositoryObj();

        list($pointScene, $sourceData, $data) = $this->_addFormatData($repository);
        $result = $repository->create($data);
        $this->getServiceObj('passport-managerPermission')->writeManagerLog($sourceData);
        return $this->success($result);
    }

    protected function _addFormatData($repository)
    {
        $scene = $this->request->input('point_scene') ?? 'add';
        if ($scene == 'get_formelem') {
            return $this->success(['formFields' => $repository->getFormatFormFields('add'), 'fieldNames' => $repository->getAttributeNames('add')]);
        }

        $request = $this->getPointRequest($scene, $repository);
        $sourceData = $request->getInputDatas($scene);
        $data = $request->filterDirtyData($sourceData);
        return [$scene, $sourceData, $data];
    }

    public function updateGeneral()
    {
        $repository = $this->getRepositoryObj();
        $scene = $this->request->input('point_scene') ?? 'update';
        $request = $this->getPointRequest($scene, $repository);
        if ($scene == 'get_formelem') {
            return $this->success($repository->getFormatFormFields('add'));
        }
        $repository->currentScene = $scene;
        $info = $this->getPointInfo($repository, $request);
        $data = $request->getInputDatas($scene);
        if (empty($data) && empty($request->allowEmpty)) {
            return $this->resource->throwException(422, '没有输入参数');
        }
        $checkInfo = $request->checkInfo($info, $data);
        if ($checkInfo !== true) {
            return $this->resource->throwException(422, $checkInfo['message']);
        }
        $sourceData = $request->validated();
        $data = $request->filterDirtyData($sourceData);
        $result = $repository->updateInfo($info, $data);

        $this->getServiceObj('passport-managerPermission')->writeManagerLog($sourceData, $info->toArray());
        return $this->success([]);
    }

    public function viewGeneral()
    {
        $repository = $this->getRepositoryObj();
        $request = $this->getPointRequest('', $repository);
        $params = $request->all();
        $info = $this->getPointInfo($repository, $request);

        $scene = $params['point_scene'] ?? 'view';
        $simpleResult = $params['simple_result'] ?? false;
        $resource = $this->getResourceObj($info, $scene);
        return $resource->toResponse($request);
    }

    public function detail()
    {
        $repository = $this->getRepositoryObj();
        $request = $this->getPointRequest('', $repository);
        $params = $request->all();
        $info = $this->getPointInfo($repository, $request, false);
        $resource = $this->getResourceObj($info, 'frontDetail');
        return $resource->toResponse($request);
    }

    public function deleteGeneral()
    {
        $repository = $this->getRepositoryObj();
        $request = $this->getPointRequest('', $repository);
        $info = $this->getPointInfo($repository, $request, true, false);

        $number = 0;
        if (empty($info)) {
            $ids = (array) $request->input($repository->getKeyName());
            foreach ($ids as $id) {
                $info = $repository->find($id);
                if (empty($info)){
                    continue;
                }
                //$info->delete();
                $result = $repository->deleteInfo($info, $number);
                if ($result) {
                    $number += 1;
                    $deleteDatas[] = $info->toArray();
                }
            }
        } else {
            $result = $repository->deleteInfo($info, $number);
            $number = 1;
            $number = $result ? 1 :0;
            if ($result) {
                $deleteDatas = $info->toArray();
            }
        }

        //$result->permissions;
        if ($number) {
            $this->getServiceObj('passport-managerPermission')->writeManagerLog($deleteDatas);
            return $this->success(['message' => "成功删除了{$number}条信息"]);
        }
        return $this->error(400, '删除失败');
    }

    protected function getPointInfo($repository, $request, $routeParam = true, $throw = true)
    {
        //$repository = $this->getRepositoryObj();
        $pointKey = $request->input('point_Key', false);
        $key = $pointKey ? $pointKey : $repository->getKeyName();
        //$value = $routeParam ? $request->route($key) : $request->input($key);
        $value = $routeParam ? $request->route($key) : ($request->route($key) ? $request->route($key) : $request->input($key));
        if (empty($key)) {
            return $throw ? $this->resource->throwException(422, '参数有误') : false;
        }
        $info = $repository->find($value);
        if (empty($info)) {
            return $throw ? $this->resource->throwException(404, '信息不存在') : false;
        }

        $limitPriv = $request->get('limitPriv');
        if ($limitPriv) {
            $priv = $info->checkLimitPriv($limitPriv);
            if (empty($priv)) {
                return $throw ? $this->resource->throwException(403, '您没有执行该操作的权限') : false;
            }
        }
        return $info;
        //echo $this->request->path(); print_R($this->request->query()); print_R($this->request->route('id'));
    }

    public function allowMulDelete()
    {
        return true;
    }
}
