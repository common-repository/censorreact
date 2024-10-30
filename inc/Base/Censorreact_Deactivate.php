<?php 

//Package Name
/**
 *  
 * @package censorREACT
 *  
 */

 namespace Censorreact\Base;

//Deactivate Plugin 
class Censorreact_Deactivate 
{
    public static function censorreact_deactivate() { 

        remove_filter('title_save_pre', 'modifyAllPostData');  
        
        remove_filter('content_save_pre', 'modifyAllPostData');   

    }
} 