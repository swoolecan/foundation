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
        $repository = $this->dealCriteria($scene, $repository, $params);
        if (in_array($scene, ['list', 'pop'])) {
            $perPage = $params['per_page'] ?? 25;
            $list = $repository->paginate(intval($perPage));
        } else {
            $list = $repository->all();
        }

        //$collectionClass = $repository->getCollectionClass();
        //$collection = new $collectionClass($list, $scene, $repository, $simpleResult);
        $collection = $this->getCollectionObj(null, ['resource' => $list, 'scene' => $scene, 'repository' => $repository, 'simpleResult' => $simpleResult]);
        return $collection->toResponse($this->request);
        //$list = $repository->all();//null, $params, (int) $pageSize);
        //$list = $repository->getByCriteria($criteria)->all();
        return $this->success($datas);
    }

    public function listinfoTree()
    {
        $resource = $this->resource->getResourceCode(get_called_class());
        $repository = $this->getRepositoryObj();
        return $this->success($repository->getTreeLists());
    }

    public function addGeneral()
    {
        $repository = $this->getRepositoryObj();
        $request = $this->getPointRequest('add', $repository);
        $scene = $request->input('point_scene');
        if ($scene == 'get_formelem') {
            return $this->success(['formFields' => $repository->getFormatFormFields('add'), 'fieldNames' => $repository->getAttributeNames('add')]);
        }
        $data = $request->getInputDatas('add');
        $result = $repository->create($data);
        return $this->success($result);
    }

    public function updateGeneral()
    {
        $repository = $this->getRepositoryObj();
        $request = $this->getPointRequest('update', $repository);
        $scene = $request->input('point_scene');
        if ($scene == 'get_formelem') {
            return $this->success($repository->getFormatFormFields('add'));
        }
        $info = $this->getPointInfo($repository, $request);

        $data = $request->getInputDatas('update');
        if (empty($data)) {
            return $this->throwException(422, '没有输入参数');
        }
        $data = $request->validated();
        $result = $repository->updateInfo($info, $data);
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
        //$resourceClass = $repository->getResourceClass();
        //$resource = new $resourceClass($info, $scene, $repository, $simpleResult);
        $resource = $this->getResourceObj(null, ['resource' => $info, 'scene' => $scene, 'repository' => $repository, 'simpleResult' => $simpleResult]);
        return $resource->toResponse($request);

        //$result->permissions;
        return $this->success($info);
    }

    public function deleteGeneral()
    {
        $repository = $this->getRepositoryObj();
        $request = $this->getPointRequest('', $repository);
        $info = $this->getPointInfo($repository, $request, false);

        $number = 0;
        if (empty($info)) {
            $ids = $request->input($repository->getKeyName());
            foreach ($ids as $id) {
                $info = $repository->find($id);
                if (empty($info)){
                    continue;
                }
                //$info->delete();
                $number++;
                $repository->deleteInfo($info, $number);
            }
        } else {
            $result = $repository->deleteInfo($info, $number);
            $number = 1;
        }

        //$result->permissions;
        if ($number) {
            return $this->success(['message' => "成功删除了{$number}条信息"]);
        }
        return $this->success(['message' => '删除失败']);
    }

    protected function getPointInfo($repository, $request, $throw = true)
    {
        $repository = $this->getRepositoryObj();
        $pointKey = $request->input('point_Key', false);
        $key = $pointKey ? $pointKey : $repository->getKeyName();
        $value = $request->route($key);
        if (empty($key)) {
            return $throw ? $this->throwException(422, '参数有误') : false;
        }
        $info = $repository->find($value);
        if (empty($info)) {
            return $throw ? $this->throwException(404, '信息不存在') : false;
        }

        $limitPriv = $request->get('limitPriv');
        if ($limitPriv) {
            $priv = $info->checkLimitPriv($limitPriv);
            if (empty($priv)) {
                return $throw ? $this->throwException(403, '您没有执行该操作的权限') : false;
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
