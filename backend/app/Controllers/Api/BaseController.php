<?php

namespace App\Controllers\Api;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;

class BaseController extends ResourceController
{
    use ResponseTrait;

    protected $modelName = '';
    protected $format = 'json';

    public function __construct()
    {
        // Enable CORS
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Origin, Content-Type, Accept, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit(0);
        }
    }

    /**
     * Return an array of resource objects, themselves in array format
     *
     * @return mixed
     */
    public function index()
    {
        $model = model($this->modelName);
        $data = $model->findAll();
        
        return $this->respond([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Return the properties of a resource object
     *
     * @return mixed
     */
    public function show($id = null)
    {
        $model = model($this->modelName);
        $data = $model->find($id);
        
        if (!$data) {
            return $this->failNotFound('Resource not found');
        }
        
        return $this->respond([
            'status' => 'success',
            'data' => $data
        ]);
    }

    /**
     * Create a new resource object, from "posted" parameters
     *
     * @return mixed
     */
    public function create()
    {
        $model = model($this->modelName);
        $data = $this->request->getPost();
        
        if (!$model->insert($data)) {
            return $this->failValidationErrors($model->errors());
        }
        
        return $this->respondCreated([
            'status' => 'success',
            'message' => 'Resource created successfully',
            'id' => $model->getInsertID()
        ]);
    }

    /**
     * Add or update a model resource, from "posted" properties
     *
     * @return mixed
     */
    public function update($id = null)
    {
        $model = model($this->modelName);
        $data = $this->request->getRawInput();
        
        if (!$model->find($id)) {
            return $this->failNotFound('Resource not found');
        }
        
        if (!$model->update($id, $data)) {
            return $this->failValidationErrors($model->errors());
        }
        
        return $this->respond([
            'status' => 'success',
            'message' => 'Resource updated successfully'
        ]);
    }

    /**
     * Delete the designated resource object from the model
     *
     * @return mixed
     */
    public function delete($id = null)
    {
        $model = model($this->modelName);
        
        if (!$model->find($id)) {
            return $this->failNotFound('Resource not found');
        }
        
        $model->delete($id);
        
        return $this->respondDeleted([
            'status' => 'success',
            'message' => 'Resource deleted successfully'
        ]);
    }
}