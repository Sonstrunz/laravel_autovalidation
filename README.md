# Laravel Autovalidation

# Installation

composer require raffaele/laravel_autovalidation

# Usage Example

class YourModel extends raffaele\ValidationModel{

}

# Disable automatic validate

Set in your model $autoValidate = false;

# Array notValidate

If you wish exclude from validation some field, you have to add those to $notValidate = [] array.

# Standard/custom validation message

Automatic validation uses the standarrd messages from laravel validation. If you want it's possibile use your custom messages, override
$messages array.

# Password and email fields

This fields have particolar rules, so if you have this fields thats have particolar name, please, help me to populate the  
$passwordLabelCandidate and $emailLabelCandidate array.

Have fun! ;-)

