<?php

return [
    /*
     |--------------------------------------------------------------
     | Enable / Disable 2FA globally
     |--------------------------------------------------------------
     |
     | When false:
     | - management routes are closed
     | - middleware won't force challenge
     |
     */
    'enabled' => env('ENABLE_2FA', true),

    /*
     | If true, user must confirm password before enabling 2FA
     */
    'confirm_password' => false,
];
