<?php

    /**
     * Show a list of all of the application's users.
     *
     * @return Response
     *
    public function listAll()
    {
        $users = DB::select('select * from users where id > 0');

        return view('user.index', ['users' => $users]);
    }
    */
class UserController
{
    private $model;
    /** private $uri = $_SERVER["REQUEST_URI"]; */
    public $actions = ['create', 'update', 'delete', 'showall'];

    public function __construct($model)
    {
        $this->model = $model;
    }

    public function createUser($params)
    {
        return $this->model->createUser($params);
    }

    public function updateUser($params, $id)
    {
        return $this->model->updateUser($params, $id);
    }

    public function deleteUser($id)
    {
        return $this->model->deleteUser($id);
    }
    
    public function showAllUsers()
    {
        return $this->model->showAllUsers();   
    }
}
