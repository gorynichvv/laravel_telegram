<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Telegram\Bot\Keyboard\Keyboard;
use App\TelegramUser;
use Telegram\Bot\Laravel\Facades\Telegram;

class TelegramController extends Controller
{
    public function webhookHandler(){

        $updates = Telegram::getWebhookUpdates();

        if ($updates->isType('callback_query')){ //Нажатие кнопки

            TelegramUser::where('user_id', $updates->callbackQuery->from->id)
                ->update(['button_id' => $updates->callbackQuery->data]);

            $response = Telegram::sendMessage([
                'chat_id' => $updates->callbackQuery->from->id, 
                'text' => "Поздравляю, теперь пришли мне фото"
            ]);      
        }
        elseif ($updates->message->isType('photo')){ //Получения фото

            foreach($updates->message->photo as $photo){
                $file_id = $photo['file_id'];
                break;
            }

            TelegramUser::where('user_id', $updates->message->from->id)
                ->update(['image_id' => $file_id]);

            $response = Telegram::sendMessage([
                'chat_id' => $updates->message->from->id, 
                'text' => "Спасибо!"
            ]);
        }
        elseif ($updates->message->isType('contact') and TelegramUser::where('phone', $updates->message->contact->phone_number)->first() == false){ // Получение контака

            TelegramUser::insert([
                [ 'user_id' => $updates->message->contact->user_id, 
                'name' => $updates->message->contact->first_name, 
                'phone' => $updates->message->contact->phone_number,
                'button_id' => "",
                'image_id' => "" ]
            ]);
    
            $reply_markup = Keyboard::make()
                    ->inline()
                    ->row(
                        Keyboard::inlineButton([
                            'text' => '1',
                            'callback_data' => '1'
                        ]),
                        Keyboard::inlineButton([
                            'text' => '2',
                            'callback_data' => '2'
                        ]),
                        Keyboard::inlineButton([
                            'text' => '3',
                            'callback_data' => '3'
                        ])
                    );

                $response = Telegram::sendMessage([
                    'chat_id' => $updates->message->from->id, 
                    'text' => "Отлично! А теперь выбери к какой категории пользователей тебя отнести:",
                    'reply_markup' => $reply_markup
                ]);
        }
        elseif ($updates->message->text == "/start"){
            if(TelegramUser::where('user_id', $updates->message->from->id)->first()){ //Если пользователь не зарегистрирован
                $response = Telegram::sendMessage([
                    'chat_id' => $updates->message->from->id, 
                    'text' => "Вы уже зарегестрированы!"
                ]);
            }
            else{
                $response = Telegram::sendMessage([
                    'chat_id' => $updates->message->from->id, 
                    'text' => "Привет, добро пожаловать! Я справлюсь с тестовым заданием \xF0\x9F\x98\x83"
                ]);
                sleep(1);
                $response = Telegram::sendMessage([
                    'chat_id' => $updates->message->from->id, 
                    'text' => "Давай познакомимся, как тебя зовут?"
                ]);
            }    
        }

        elseif(TelegramUser::where('user_id', $updates->message->from->id)->first() == false){ //Если пользователь не зарегистрирован
            $reply_keyboard = json_encode([
                'keyboard' => [[[
                    'text'=>'Отправить контакт',
                    'request_contact'=>true,
                ]]],
                'resize_keyboard'=>true,
                'one_time_keyboard'=>true,
            ]);
            $response = Telegram::sendMessage([
                'chat_id' => $updates->message->from->id, 
                'text' => "Для начала использования отправь мне свой контакт",
                'reply_markup' => $reply_keyboard
            ]);
        }
    }
}
