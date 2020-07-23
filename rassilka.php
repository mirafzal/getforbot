<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'database/db_connect.php';
require_once 'Telegram.php';
global $db, $telegram;

$ADMINS_CHAT_IDS = [
    635793263,
    742826077
];

$bot_token =
    '1116316783:AAEn82g29U2_jf96ctctLvuUAtUcfx2q1i8';
$telegram = new Telegram($bot_token);

if (!file_exists("rs.tmp")) {
    if ($_GET['text']) {
        file_put_contents("rs.tmp", ".");
        $successCount = 0;
        $failCount = 0;
        $result = $db->query("SELECT chatID FROM users");
        $mtext = base64_decode(str_replace(" ", "+", $_GET['text']));
        while ($arr = $result->fetch_assoc()) {
            $request = $telegram->sendMessage(['chat_id' => $arr['chatID'], 'text' => $mtext]);
            if ($request['ok']) {
                $successCount++;
            } else {
                $failCount++;
            }
            sleep(1);
        }
        file_put_contents('active.txt', $successCount);
        file_put_contents('nonactive.txt', $failCount);
        foreach ($ADMINS_CHAT_IDS as $ADMINS_CHAT_ID) {
            $telegram->sendMessage(['chat_id' => $ADMINS_CHAT_ID, 'text' => "Xabarlar muvaffaqqiyatli yuborildi.\nAktiv foydalanuvchilar soni: $successCount\nBotni bloklagan foydalanuvchilar soni: $failCount"]);
        }
        unlink("rs.tmp");
    } elseif ($_GET['photo']) {
        file_put_contents("rs.tmp", ".");
        $successCount = 0;
        $failCount = 0;
        $result = $db->query("SELECT chatID FROM users");
        while ($arr = $result->fetch_assoc()) {
            $content = [
                'chat_id' => $arr['chatID'],
                'photo' => $_GET['photo']
            ];
            if (isset($_GET['caption'])) $content['caption'] = base64_decode(str_replace(" ", "+", $_GET['caption']));
            $request = $telegram->sendPhoto($content);
            if ($request['ok']) {
                $successCount++;
            } else {
                $failCount++;
            }
            sleep(1);
        }
        file_put_contents('active.txt', $successCount);
        file_put_contents('nonactive.txt', $failCount);
        foreach ($ADMINS_CHAT_IDS as $ADMINS_CHAT_ID) {
            $telegram->sendMessage(['chat_id' => $ADMINS_CHAT_ID, 'text' => "Xabarlar muvaffaqqiyatli yuborildi.\nAktiv foydalanuvchilar soni: $successCount\nBotni bloklagan foydalanuvchilar soni: $failCount"]);
        }
        unlink("rs.tmp");
    } else {
        foreach ($ADMINS_CHAT_IDS as $ADMINS_CHAT_ID)
            $telegram->sendMessage(['chat_id' => $ADMINS_CHAT_ID, 'text' => "Nimadir xatolik ketdi."]);
    }
} else {
    foreach ($ADMINS_CHAT_IDS as $ADMINS_CHAT_ID)
        $telegram->sendMessage(['chat_id' => $ADMINS_CHAT_ID, 'text' => "Hozircha boshqa xabar yuborilmoqda. Keyinroq urinib ko'ring."]);
}