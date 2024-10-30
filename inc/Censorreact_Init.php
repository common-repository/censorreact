<?php

//Package Name
/**
 *  
 * @package censorREACT
 *  
 */

 namespace Censorreact;
 
 final class Censorreact_Init
 {
     //Store all the classes inside an array
     public static function censorreact_get_services() {
         return [
             Pages\Censorreact_Admin::class,
             Base\Censorreact_Enqueue::class,
             Base\Censorreact_PluginLinks::class
         ];
     }

     //Loop through the classes, initialize them and call the register method if it exists
     public static function censorreact_register_services() {
         foreach (self::censorreact_get_services() as $class) {
             $service = self::censorreact_instantiate( $class );
             if( method_exists($service, 'register')) {
                 $service->register();
             }
         }
     }

     //Initialize the class
     private static function censorreact_instantiate($class) {
         return new $class;
     }
 }
 