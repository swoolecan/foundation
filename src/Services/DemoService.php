<?php
declare(strict_types = 1);

namespace Framework\Baseapp\Services;

use Framework\Baseapp\Contracts\Repository\RepositoryInterface;
//use Illuminate\Http\Request;

class DemoService
{
    private $repository;

    /**
     * DemoService constructor.
     *
     * @param \App\Repositories\Eloquent\DemoRepositoryEloquent $repository
     */
    public function __construct(DemoRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getDemoList()
    {
        return $this->repository->simplePaginate();
    }

    public function getDemoById($id)
    {
        return $this->repository->find($id);
    }

    /**
     * @param Request $request
     *
     * @return \App\Models\Demo
     *
     * @throws \Throwable
     */
    public function register(Request $request)
    {
        return $this->repository->add($request->all());
    }
}
