<?php

return [
    'settings' => [
        // when this in true then their password will sent through email
        // instead of typed by themeself.
        'need_user_activation' => true,
        // user need approving act from owner?
        'need_approve' => true,
        'default_role_code' => 'user_luar',
        // Visitor only can register another user within register_throotle_minute period of time!
        'register_throotle_minute' => 2,
        // show captcha?
        'show_captcha' => true,
        // show captcha in debug mode?
        'show_captcha_in_debug_mode' => true,
    ]
];