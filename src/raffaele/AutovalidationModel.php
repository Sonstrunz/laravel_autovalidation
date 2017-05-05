<?php

namespace raffaele;

use Illuminate\Support\Facades\Input;

class AutovalidationModel extends \Eloquent {

    public static $autoValidate = true;

    protected $notValidate = array('id');

    protected $rules = array();

    protected $redirectTo = '/user/create';

    protected $messages = array();

    protected $passwordLabelCandidate = array('password', 'passwd');

    protected $emailLabelCandidate = array('email', 'mail');

    /**
     * [boot description]
     * @method boot
     * @return [type] [description]
     */
    protected static function boot() {
        // This is an important call, makes sure that the model gets booted
        // properly!
        parent::boot();

        // You can also replace this with static::creating or static::updating
        // if you want to call specific validation functions for each case.
        static::saving(function($model) {
          // If autovalidate is true, validate the model on create
          // and update.
          if($model::$autoValidate) {
              $model->getConstraints();
              $validator = $model->validate();
              //dd($model->rules);
              if($validator != null){
                redirect($model->redirectTo)->withInput()->withErrors($validator);
                return false;
              }else{
                return true;
              }
          }
        });
    }

    /**
     * [validate description]
     * @method validate
     * @return [type]   [description]
     */
    public function validate(){
      $data = Input::all();
      $validator = \Validator::make($data, $this->rules);

      if ($validator->fails()) {
        // get the error messages from the validator or set your custom messages.
        if(empty($this->messages)){
          $messages = $validator->messages();
        }
        else {
          $messages = $this->messages;
        }
       return $validator;
     }else{
       return null;
     }
    }

    /**
     * [getConstraints description]
     * @method getConstraints
     * @return [type]         [description]
     */
    public function getConstraints() {
      $table = $this->getTable();
      $columns = $this->getConnection()->getSchemaBuilder()->getColumnListing($table);

      foreach ($columns as $column) {
        $constraints = \DB::connection()->getDoctrineColumn($table, $column);
        $this->setRules($constraints);
      }
    }

    /**
     * [setRules description]
     * @method setRules
     * @param  [type]   $constraints [description]
     */
    public function setRules($constraints) {
      $table = $this->getTable();

      if(!in_array($constraints->getName(), $this->notValidate)){
        $this->rules[$constraints->getName()] = "";
        switch ($constraints->getType()->getName()) {
          case 'integer':
          case 'smallint':
          case 'bigint':
          case 'float':
            $this->rules[$constraints->getName()] .= "numeric";
            break;
          case 'decimal':
            $this->rules[$constraints->getName()] .= "numeric|between:0,99.99";
            break;
          case 'boolean':
            $this->rules[$constraints->getName()] .= "boolean";
            break;
          case 'date':
          case 'time':
          case 'datetime':
            $this->rules[$constraints->getName()] .= "date";
            break;
          case 'json':
            $this->rules[$constraints->getName()] .= "json";
            break;
        }

        if($constraints->getNotNull()) {
          $this->rules[$constraints->getName()] .= "|required";
        }
        if(isset($this->emailLabelCandidate) && in_array($constraints->getName(), $this->emailLabelCandidate)){
          $this->rules[$constraints->getName()] .= "|email|unique:".$table;
        }else if(isset($this->passwordLabelCandidate) && in_array($constraints->getName(), $this->passwordLabelCandidate)){
          $this->rules[$constraints->getName()] .= "|confirmed";
        }
        if($constraints->getLength() != null){
          $this->rules[$constraints->getName()] .= "|max:".$constraints->getLength();
        }
      }
    }
}
