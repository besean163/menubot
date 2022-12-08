<?php

namespace lib\Telegram\Dialogs\GetMenu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use lib\Telegram\Dialogs\DialogHandler;

class GetMenuHandler extends DialogHandler
{
    use HasFactory;

    public function run(): void
    {
        Log::debug("Dialog handled.");
    }
}
