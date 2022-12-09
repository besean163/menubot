<?php

namespace lib\Telegram\Dialogs\GetMenu;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Facades\Log;
use lib\Telegram\Dialogs\DialogHandler;
use lib\Telegram\Dialogs\GetMenu\Actions\DateSelectAction;

class GetMenuHandler extends DialogHandler
{
    use HasFactory;

    public function getActionMap(): array
    {
        return [
            DateSelectAction::class,
        ];
    }

    public function sendResult(): void
    {
    }
}
