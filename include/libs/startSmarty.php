<?php
// Reference Smarty library
require_once SMARTY_DIR . 'Smarty.class.php';
/* Class that extends Smarty, used to process and display Smarty
files */
class startSmarty extends Smarty
{
    // Class constructor
    public function __construct()
    {
        // Call Smarty's constructor
        parent::__construct();
        // Change the default template directories
        $this->template_dir = TEMPLATE_DIR;
        //echo  'template_dir:' . TEMPLATE_DIR . '<br />';
        $this->compile_dir = COMPILE_DIR;
        //echo 'compile_dir:' . COMPILE_DIR . '<br />';
        $this->config_dir = CONFIG_DIR;
        //echo 'config_dir:' . CONFIG_DIR . '<br />';
    }
}  
?>
