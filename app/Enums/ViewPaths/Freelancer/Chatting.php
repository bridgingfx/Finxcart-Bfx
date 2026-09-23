<?php

namespace App\Enums\ViewPaths\Freelancer;

enum Chatting
{
    const INDEX = [
        URI => 'index',
        VIEW => 'freelancer-views.chatting.index',
    ];
    const MESSAGE = [
        URI => 'message',
        VIEW => 'freelancer-views.chatting.index',
    ];

    const NEW_NOTIFICATION = [
        URI => 'new-notification',
        VIEW => '',
    ];
}
