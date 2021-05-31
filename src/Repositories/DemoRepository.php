<?php
declare(strict_types = 1);

namespace Framework\Baseapp\Repositories;

//use App\Contracts\Repositories\UserRepository;
//use App\Models\User;
//use App\Validators\UserValidator;
//use Illuminate\Support\Facades\Hash;
//use Prettus\Repository\Criteria\RequestCriteria;
//use Prettus\Repository\Eloquent\BaseRepository;

/**
 * Class UserRepositoryEloquent.
 */
class DemoRepository//Eloquent extends BaseRepository implements UserRepository
{
    protected $fieldSearchable = [
        'name' => 'like',
        'email', // Default Condition "="
    ];

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return User::class;
    }

    /**
     * Specify Validator class name.
     *
     * @return mixed
     */
    public function validator()
    {
        return UserValidator::class;
    }

    /**
     * Boot up the repository, pushing criteria.
     *
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function boot()
    {
        $this->pushCriteria(app(RequestCriteria::class));
    }

    /**
     * @param $attributes
     *
     * @return \App\Models\User
     *
     * @throws \Throwable
     */
    public function add($attributes)
    {
        $this->model->name = $attributes['name'];
        $this->model->email = $attributes['email'];
        $this->model->password = Hash::make($attributes['password']);
        $this->model->saveOrFail();

        return $this->model;
    }
}
