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

        $collection = $this->getCollectionObj(null, ['resource' => $list, 'scene' => $scene, 'repository' => $repository, 'simpleResult' => $simpleResult]);
        return $collection->toResponse($this->request);
    }

    public function listinfoTree()
    {
        $resource = $this->resource->getResourceCode(get_called_class());
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
        $request = $this->getPointRequest('add', $repository);
        $scene = $request->input('point_scene');
        if ($scene == 'get_formelem') {
            return $this->success(['formFields' => $repository->getFormatFormFields('add'), 'fieldNames' => $repository->getAttributeNames('add')]);
        }
        $data = $request->getInputDatas('add');
        $data = $request->filterDirtyData($data);
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
        if (empty($data) && empty($request->allowEmpty)) {
            return $this->resource->throwException(422, '??????????????????');
        }
        $data = $request->validated();
        $data = $request->filterDirtyData($data);
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
        $resource = $this->getResourceObj(null, ['resource' => $info, 'scene' => $scene, 'repository' => $repository, 'simpleResult' => $simpleResult]);
        return $resource->toResponse($request);
    }

    public function detail()
    {
        $repository = $this->getRepositoryObj();
        $request = $this->getPointRequest('', $repository);
        $params = $request->all();
        $info = $this->getPointInfo($repository, $request, false);
        $resource = $this->getResourceObj(null, ['resource' => $info, 'scene' => 'frontDetail', 'repository' => $repository, 'simpleResult' => false]);
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
                $number += $result ? 1 :0;
            }
        } else {
            $result = $repository->deleteInfo($info, $number);
            $number = 1;
            $number = $result ? 1 :0;
        }

        //$result->permissions;
        if ($number) {
            return $this->success(['message' => "???????????????{$number}?????????"]);
        }
        return $this->error(400, '????????????');
    }

    protected function getPointInfo($repository, $request, $routeParam = true, $throw = true)
    {
        $repository = $this->getRepositoryObj();
        $pointKey = $request->input('point_Key', false);
        $key = $pointKey ? $pointKey : $repository->getKeyName();
        //$value = $routeParam ? $request->route($key) : $request->input($key);
        $value = $routeParam ? $request->route($key) : ($request->route($key) ? $request->route($key) : $request->input($key));
        if (empty($key)) {
            return $throw ? $this->resource->throwException(422, '????????????') : false;
        }
        $info = $repository->find($value);
        if (empty($info)) {
            \Log::info('aaaa' . serialize($request->all()));
            return $throw ? $this->resource->throwException(404, '???????????????') : false;
        }

        $limitPriv = $request->get('limitPriv');
        if ($limitPriv) {
            $priv = $info->checkLimitPriv($limitPriv);
            if (empty($priv)) {
                return $throw ? $this->resource->throwException(403, '?????????????????????????????????') : false;
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
