<?php

namespace lib\Telegram\Dialogs;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class Dialog extends Model
{
    use HasFactory;

    const DIALOG_TYPE_GET_MENU = 'get_menu';

    const DIALOG_STATUS_WAIT = 'wait';

    public static function new(int $userId, int $chatId): self
    {
        return self::query()->create([
            'userId' => $userId,
            'chatId' => $chatId,
            'type' => static::type(),
            'status' => self::DIALOG_STATUS_WAIT
        ]);
    }

    abstract static protected function type(): string;

    protected function wait(): void
    {
        $this->status = self::DIALOG_STATUS_WAIT;
    }
}
