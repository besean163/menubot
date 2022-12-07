<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use lib\Telegram\Dialogs\Dialog;

class GetMenuDialog extends Dialog
{
    use HasFactory;

    const DIALOG_TYPE_GET_MENU = 'get_menu';

    static protected function type(): string
    {
        return 'get_menu';
    }

    public function procces(): bool
    {
        /* 
            нужно определить какой-то список как должны идти вопросы
            проходит по нему и по массиву действий
            например первый элемент списка и первый элемент массива
            создаем объект
            проверяем закончен ли он, если да, то переходим к следующему
            если нет передаем ответ пользователя если ответа нет или он пустой (когда только создаем)
            валидаруем ответ

            если не подходит ответ
            удаляем предыдущее сообщение
            формируем сообщение об предупреждении
            отправляем пользователю
            формируем повторное сообщение  
            отправляем пользователю
            берем id ответа и сохраняем
            выходим в false

            если подходит ответ
            удаляем предыдущее сообщение
            очищаем id предыдущего сообщения
            ставим статус как finish
            выходим с true
        */
        return false;
    }

    public function getActionMap(): array
    {
        return [];
    }
}
