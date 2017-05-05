<?php

namespace Autovalidation;

use Illuminate\Support\Facades\Input;

class AutovalidationModel extends \Eloquent {

    public static $autoValidate = true;

    protected $notValidate = array('id');

    protected $rules = array();

    protected $messages = array();

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

              if($validator != null){
                redirect()->back()->withInput()->withErrors($validator);
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
      if(!in_array($constraints->getName(), $this->notValidate)){
        if($constraints->getNotNull()) {
          $this->rules[$constraints->getName()] = "required";
        }
      }
    }

    /**
     * [getRules description]
     * @method getRules
     * @return [type]   [description]
     */
    public function getRules(){
      return $this->rules;
    }

    /**
     * [getMessages description]
     * @method getMessages
     * @return [type]      [description]
     */
    public function getMessages(){
      return $this->messages;
    }
}
