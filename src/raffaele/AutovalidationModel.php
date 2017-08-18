<?php

namespace raffaele;

use Illuminate\Support\Facades\Input;

class AutovalidationModel extends \Eloquent {

    /**
	* Enable/disable autovalidating.
	*
	* @var boolean
	*/
    public static $autoValidate = true;
    
    /**
	* Array contains elements which you don't want to validate.
	*
	* @var array
	*/
    public $notValidate = array('id');

    /**
	* Array contains validate's rules.
	*
	* @var array
	*/
    public $rules = array();

    /**
	* Array contains messages.
	*
	* @var array
	*/
    public $messages = array();
    
    /**
	* Array contains string thats can be password label.
	*
	* @var array
	*/
    public $passwordLabelCandidate = array('password', 'passwd');

    /**
	* Array contains string thats can be email label.
	*
	* @var array
	*/
    public $emailLabelCandidate = array('email', 'mail');

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
     * Validate on create or update method
     * @method validate
     * @return validator object
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
     * Get the constraints from database columns mapped by eloquent model
     * @method getConstraints
     * @return void
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
     * Build validation rules
     * @method setRules
     * @param  $constraints
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
