<?php namespace Yfktn\BackendUserRegistration;

use BackendAuth;
use Event;
use Flash;
use System\Classes\PluginBase;
use Yfktn\BackendUserRegistration\Models\BackendUserRegistration;

class Plugin extends PluginBase
{

    public function boot()
    {
        // ada bug pada saat melakukan register, nilai password_confirmation selalu failed
        // jadi karena sudah dimasukkan pada bagian awal untuk validasinya, maka
        // jangan divalidasi lagi pada saat menyimpannya!
        \Backend\Models\User::extend(function ($model) {
            if (!$model instanceof  \Backend\Models\User)
                return;
    
            $model->bindEvent('model.beforeValidate', function() use ($model) {
                $model->rules = [
                  'password_confirmation' => null
                ];
            });
    
        });

        // don't let current registered user login if he/she not approved yet by the management!
        Event::listen('backend.user.login', function (\Backend\Models\User $user) {
            // search backend user 
            $bur = BackendUserRegistration::where('user_id', $user->id)->first();
            if($bur !== null) {
                if( (bool)$bur->is_approved != true) {
                    // force logoff
                    Flash::error("Kami mohon maaf, user anda belum disetujui oleh manajemen.");
                    BackendAuth::logout();
                } else {
                    Event::fire('yfktn.backenduserregistration.user_login');
                }
            }
        });
    }

    public function registerComponents()
    {
        return [
            \Yfktn\BackendUserRegistration\Components\BURegistrationForm::class => 'BURegistrationForm',
        ];
    }

    public function registerMarkupTags()
    {
        return [
            'functions' => [
                'userBackendSignedIn' => function() {
                    return BackendAuth::check();
                },
                'backendUri' => function() {
                    return config('cms.backendUri', config('backend.uri'));
                },
            ]
        ];
    }

    public function registerSettings()
    {
    }
}
