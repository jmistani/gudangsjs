<?php

namespace App\Models;

class Ability 
{
   protected $action;
   protected $subject;

   public function __construct(array $attributes = [])
   {
       parent::__construct($attributes);
   }
}
