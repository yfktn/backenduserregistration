<?php namespace Yfktn\BackendUserRegistration\Models;

use Backend\Models\User;
use Model;

/**
 * Model
 */
class BackendUserRegistration extends Model
{
    use \October\Rain\Database\Traits\Validation;
    

    /**
     * @var string The database table used by the model.
     */
    public $table = 'yfktn_backenduserregistration_';

    /**
     * @var array Validation rules
     */
    public $rules = [
    ];

    public $belongsTo = [
        'backendUser' => [
            User::class,
            'key' => 'user_id'
        ]
    ];
    
}
