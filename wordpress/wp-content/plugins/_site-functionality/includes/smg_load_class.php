<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class SMG_Load_Class {
    
    private $load_classes=array();
    
    private $prefix;
    
    private $dirs;
    
    public $apg_classes=array();
    
    

    public function __construct() {


        spl_autoload_register(array(&$this, 'autoload')); // autoload classes
        
        add_action('plugins_loaded', array(&$this, 'init'), 1);
        
        
    }
    
    
    
    public function set_prefix($prefix){
        
        $this->prefix=$prefix;
    }
    
    public function set_load_classes($classes){
        $this->load_classes=$classes;
    }
    
    public function set_dirs($dirs){
        $this->dirs=$dirs;
    }
    
    function init(){
        
        
       
        foreach($this->load_classes as $load_class){
            
           $this->apg_classes[$load_class]=new $load_class();
            
            
        }
        
    }

    public function autoload($class) {
        //
        
        // not a Polylang class
       // echo $class;
        
        if (0 !== strncmp($this->prefix , $class, 4)) {
            return;
        }
        


        $class = str_replace('_', '-', strtolower(substr($class, 4)));
        $to_remove = array('post-', 'term-', 'settings-', 'admin-', 'frontend-', '-config', '-compat', '-model', 'advanced-');
        $dir = str_replace($to_remove, array(), $class);

        //echo $dir;

        foreach ($this->dirs as $dir) {
            $file = "$dir/$class.php";
            //echo $file;
            if (file_exists($file))  {
                //echo " EXISTS ";
                require_once( $file );
                return;
            }else{
                
            }
        }
        //require_once DDG_PROP_ADMIN_INC . '/user-profile.php';
    }

}


