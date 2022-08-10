<?php namespace Yfktn\BackendUserRegistration\Components;

use ApplicationException;
use Backend\Facades\BackendAuth;
use Backend\Models\UserRole;
use Carbon\Carbon;
use Cms\Classes\ComponentBase;
use Exception;
use Illuminate\Support\Facades\DB;
use Log;
use October\Rain\Support\Facades\Flash;
use Queue;
use ValidationException;
use Validator;
use Yfktn\BackendUserRegistration\Classes\SendEmailUserActivation;
use Yfktn\BackendUserRegistration\Models\BackendUserRegistration;

/**
 * BURegistrationForm Component
 */
class BURegistrationForm extends ComponentBase
{
    public $dataBUR = [];
    protected $settings = [];

    public function componentDetails()
    {
        return [
            'name' => 'Backend User Registration Form Component',
            'description' => 'Provide registration form for user.'
        ];
    }

    public function defineProperties()
    {
        return [];
    }
    
    protected function prepareTheVariable()
    {
        $this->dataBUR['settings'] = $this->settings = config('yfktn.backenduserregistration::settings');
        $this->dataBUR['post'] = post();  
        $this->dataBUR['backendurl'] = config('cms.backendUri', config('backend.uri')); 
    }

    public function onRun()
    {
        $this->prepareTheVariable();
    }

    public function onRegister()
    {
        $this->prepareTheVariable();
        $rules = [
            'terms' => 'accepted',
            'email' => 'required|between:6,255|email|unique:backend_users',
            'login' => 'required|between:2,255|unique:backend_users',
            'password' => (!$this->settings['need_user_activation'] ? 'required:create|between:4,255|confirmed': null),
            'password_confirmation' => (!$this->settings['need_user_activation'] ? 'required_with:password|between:4,255': null)
        ];
        $messages = [
            'accepted' => 'Setujui dahulu ketentuan.',
            'email' => 'Email dibutuhkan, tidak valid atau sudah ada yang menggunakan',
            'required' => ':attribute dibutuhkan untuk diisi!',
            'confirmed' => ':attribute tidak sama, coba check.',
            'password' => (!$this->settings['need_user_activation'] ? 'Membutuhkan Pengisian Password': null),
            'password_confirmation' => (!$this->settings['need_user_activation'] ? 'Konfirmasi Password tidak sama': null)
        ];
        
        $validator = Validator::make($this->dataBUR['post'], $rules, $messages);
        
        if($validator->fails()) {
            throw new ValidationException($validator);
        }

        
        if($this->settings['need_user_activation']) {
            $password = $password_confirmation = str_random(10);
        } else {
            $password = post('password');
            $password_confirmation = post('password_confirmation');
        }

        $defaultUserRole = UserRole::where('code', $this->settings['default_role_code'])->first();
        if($defaultUserRole == null) {
            throw new ApplicationException("Gagal mendapatkan default role!");
        }
        
        if(config('app.debug')) {
            trace_log("User dibuatkan:", post(), $password, $password_confirmation, $this->settings, $defaultUserRole->id);
        }

        // check throotle
        $backendUserRegistrationLast = BackendUserRegistration::where('ip_addr', request()->ip())->first();
        if($backendUserRegistrationLast != null) {
            // yes check last 
            $lastCreated = $backendUserRegistrationLast->created_at;
            if(Carbon::now()->diffInMinutes($lastCreated) 
                <= $this->settings['register_throotle_minute']) {
                    throw new ApplicationException("Please wait another minutes!");
            }
        }
        $failed = false;
        DB::beginTransaction();
        try {
            /** @var \Backend\Models\User $user */
            $user = BackendAuth::register([
                // 'first_name' => 'Some',
                // 'last_name' => 'User',
                'login' => post('login'),
                'email' => post('email'),
                'password' => $password,
                'password_confirmation' => $password_confirmation,
                // 'role_id' => $defaultUserRole->id
            ]);

            $user->role()->add($defaultUserRole);
            $user->save();

            $newBackendUser = new BackendUserRegistration();
            $newBackendUser->user_id = $user->id;
            $newBackendUser->ip_addr = request()->ip;
            $newBackendUser->is_approved = !(bool)$this->settings['need_approve'];
            $newBackendUser->save();
        } catch(Exception $e) {
            Log::error($e->getMessage() . "\n" . $e->getTraceAsString(), ['BURegistrationForm', 'onRegister']);
            Flash::error("Gagal melakukan registrasi!");
            $failed = true;
        }

        if($failed) {
            DB::rollBack();
        } else {
            DB::commit();
        }

        if(!$failed && $this->settings['need_user_activation']) {
            $dateTrigger = Carbon::now()->addMinutes(1);
            Queue::later($dateTrigger, SendEmailUserActivation::class, 
                array_merge(post(), ['backendurl' => $this->dataBUR['backendurl']])
            );
        }

        if(!$failed) {
            // Flash::success("Berhasil melakukan registrasi");
        }  else {
            throw new ApplicationException("Gagal melakukan registrasi!");
        }
    }
}
