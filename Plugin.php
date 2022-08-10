<?php namespace Yfktn\BackendUserRegistration;

use System\Classes\PluginBase;

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
    }
    public function registerComponents()
    {
        return [
            \Yfktn\BackendUserRegistration\Components\BURegistrationForm::class => 'BURegistrationForm',
        ];
    }

    public function registerSettings()
    {
    }
}
