<?php namespace Yfktn\BackendUserRegistration\Classes;

use Mail;

class SendEmailUserActivation 
{
    public function fire($job, $data)
    {
        Mail::send('yfktn.backenduserregistration::mail.user_activation', $data, function($msg) use($data) {
            $msg->to($data['email'], $data['login']);
            $msg->subject('Aktifasi user anda');
        });
        $job->delete();
    }
}