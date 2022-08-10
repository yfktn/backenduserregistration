<?php namespace Yfktn\BackendUserRegistration\Controllers;

use Backend\Classes\Controller;
use BackendMenu;

class BackendUserRegistration extends Controller
{
    public $implement = [        'Backend\Behaviors\ListController',        'Backend\Behaviors\FormController'    ];
    
    public $listConfig = 'config_list.yaml';
    public $formConfig = 'config_form.yaml';

    public $requiredPermissions = [
        'yfktn.backenduserregistration.manajer' 
    ];

    public function __construct()
    {
        parent::__construct();
        BackendMenu::setContext('Yfktn.BackendUserRegistration', 'main-menu-bur');
    }
}
