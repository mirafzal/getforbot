<?php

require_once 'database/db_connect.php';

class Texts
{
    private $lang;

    public function __construct($lang)
    {
        $this->lang = $lang;
    }

    function getText($keyword)
    {
        global $db;
        $res = "";
        $keyword = $db->real_escape_string($keyword);
        $result = $db->query("SELECT * FROM `texts` WHERE `keyword` = '{$keyword}' LIMIT 1");
        $arr = $result->fetch_assoc();
        if (isset($arr[$this->lang])) {
            $res = $arr[$this->lang];
        }
        return $res;
    }

    function getArrayLike($keyword)
    {
        global $db;
        $res = [];
        $keyword = $db->real_escape_string($keyword);
        $result = $db->query("SELECT * FROM `texts` WHERE `keyword` LIKE '{$keyword}%'");
        while ($arr = $result->fetch_assoc()) {
            if (isset($arr[$this->lang])) {
                $res[] = $arr[$this->lang];
            }
        }
        return $res;
    }

    const LANGS =
        [
            'uz' => "🇺🇿 O'zbekcha",
            'ru' => "🇷🇺 Русский",
        ];

    const UZ =
        [
            // main page
            'what_you_want' => "Sizni qiziqtirgan menyuni tanlang.",

            'pizza' => "🍕 Pitsa",
            'breakfast' => "🥞 Nonushta",
            'pasta' => "🍝 Pasta",
            'sandwich' => "🥪 Sendvich",
            'snacks' => "🍟 Gazak",
            'salads' => "🥗 Salat",
            'callback' => "📞 Qayta aloqa",
            'ask_question' => "❓ Savol berish",
            'shopping_cart' => "🛒 Savat",
            'change_language' => "🇺🇿🔄🇷🇺 Tilni o'zgartirish",

            // menu
            'see_menu' => "Menyuni ko'ring",

            // pizza
            'choose_pizza_category' => "Pitsalar bo’limini tanlang",

            'pizza_categories' => [
                'margarita' => "Margarita",
                'peperoni' => "Peperoni",
                'tarantella' => "Tarantella",
                'tre_gusti' => "Tre Gusti",
                'amerikano_kon_peperoni' => "Amerikano Kon Peperoni",
                'tsezario' => "Tsezario",
                'pollo_de_fungi' => "Pollo de Fungi",
                'with_tuna_and_onions' => "Tuna va piyozli",
                'roma' => "Roma",
                'venice' => "Venetsiya",
                'set_napoli' => "Set Napoli (16 dona)",
            ],

            // breakfast
            'choose_breakfast_category' => "Nonushta bo’limini tanlang",

            'breakfast_categories' => [
                'classic_omelette' => "Klassik omlet",
                'beef_ham' => "Mol go‘shti",
                'baked_chicken' => "Pishirilgan tovuq",
                'spicy_cheese' => "Achchiq pishloq",
                'mushrooms' => "Qo'ziqorinlar",
                'tomatoes' => "Tamatlar",
                'omelette_mix' => "Omlet «Микс»",
            ],

            // paste
            'choose_paste_category' => "Pasta bo’limini tanlang",

            'paste_categories' => [
                'arrabyata' => "Arrabyata",
                'alfredo' => "Alfredo",
                'bolognese' => "Bolonyeze",
            ],

            // sandwich
            'choose_sandwich_category' => "Sendvich bo’limini tanlang",

            'sandwich_categories' => [
                'chicken_and_mayonnaise_sandwich' => "Tovuq va mayonezli sendvich",
                'club_sandwich' => "Klab sendvich",
                'tuna_sandwich' => "Tunali sendvichi",
            ],

            // snacks
            'choose_snacks_category' => "Gazaklar bo’limini tanlang",

            'snacks_categories' => [
                'french_fries' => "Kartoshka fri",
                'cheese_sticks' => "Pishloq tayoqlari",
                'chicken_sticks' => "Tovuq tayoqlari",
            ],

            // salads
            'choose_salads_category' => "Salatlar bo’limini tanlang",

            'salads_categories' => [
                'greek' => "Grecheskiy",
                'caesar_chicken' => "Tsezar tovuqli",
                'insala_ton' => "Insala tonna",
                'vegetable' => "Sabzavotli",
                'del_povero' => "Del Povero",
                'seasonal' => "Mavsumiy",
            ],

            // count

            'enter_count' => "Kerakli miqdorni tanlang va tasdiqlashni bosing.",

            'numbers' => [
                '1' => "1️⃣",
                '2' => "2️⃣",
                '3' => "3️⃣",
                '4' => "4️⃣",
                '5' => "5️⃣",
                '6' => "6️⃣",
                '7' => "7️⃣",
                '8' => "8️⃣",
                '9' => "9️⃣",
            ],

            'confirm' => "✅ Tasdiqlash",
            'back' => "Orqaga",
            'product_added' => "Savatga qo’shildi.",

            // shopping cart
            'your_order' => "Sizning buyurtmangiz",
            'cart_is_empty' => "Savat bo’sh holatda",
            'pieces' => "dona",
            'soum' => "so'm",
            'overall' => "Jami: ",
            'order_count' => "💵 Buyurtmani hisoblash",
            'checkout' => "🛎 Buyurtma berish",
            'change' => "⚙️ O'zgartirish",
            'clear' => "❌ Tozalash",
            'back_btn' => "🔙 Orqaga",
            'press_x_to_clear' => "Mahsulotni savatdan o'chirish uchun ❌ ni bosing.",
            'cart_is_cleared' => "Savat tozalandi.",
            'close_window' => "Oynani yopish",

            // delivery type page
            'choose_delivery_type' => "Xarid turini tanlang:",
            'pickup' => "🚶 Olib ketish",
            'delivery' => "🚗 Yetkazib berish",

            // phone number page
            'send_your_phone_number' => "Iltimos, kontaktingizni yuboring yoki telefon raqamingizni 901234567 ko'rinishida yuboring.",
            'send_contact' => "✅ Kontaktni yuborish",

            // order types
            'pickupType' => "Olib ketish",
            'deliveryType' => "Yetkazib berish",

            // location page
            'send_your_location' => "Iltimos, lokatsiyangizni yuboring. (GPS ni yoqishni unutmang)",
            'send_location' => "✅ Lokatsiya jo'natish",
            'cant_send_location' => "✅ Lokatsiya yubora olmayamman",

            'our_phone_number' => "<b>Bizning telefon raqamimiz:</b>",

            'order' => "<b>BUYURTMA</b>",

            // order confirmation page
            'do_order' => "✅ Buyurtma berish",
            'cancel' => "❌ Bekor qilish",

            // order confirmed
            'order_confirmed' => "Rahmat. Sizning buyurtmangiz qabul qilindi. Tez orada sizga operatorimiz buyurtmani tasdiqlash uchun qo'ng'iroq qiladi.",
            'order_canceled' => "Buyurtma bekor qilindi.",

            'new_order' => "Yangi buyurtma!",

            'choose_count' => "Iltimos, miqdorni tanlang.",
        ];

    const RU =
        [
            // main page
            'what_you_want' => "Что вас интересует?",

            'pizza' => "🍕 Пицца",
            'breakfast' => "🥞 Завтрак",
            'pasta' => "🍝 Паста",
            'sandwich' => "🥪 Сендвичи",
            'snacks' => "🍟 Закуски",
            'salads' => "🥗 Салаты",
            'callback' => "📞 Обратная связь",
            'ask_question' => "❓ Задать вопрос",
            'shopping_cart' => "🛒 Корзина",
            'change_language' => "🇺🇿🔄🇷🇺 Сменить язык",

            // menu
            'see_menu' => "Просмотрите меню",

            // pizza
            'choose_pizza_category' => "Выберите пиццу",

            'pizza_categories' => [
                'margarita' => "Маргарита",
                'peperoni' => "Пеперони",
                'tarantella' => "Тарантелла",
                'tre_gusti' => "Тре Густи",
                'amerikano_kon_peperoni' => "Американо Кон Пеперони",
                'tsezario' => "Цезарио",
                'pollo_de_fungi' => "Полло дэ Фунги",
                'with_tuna_and_onions' => "С Тунцом и Луком",
                'roma' => "Рома",
                'venice' => "Венеция",
                'set_napoli' => "Сет Наполи (16 кусочков)",
            ],

            // breakfast
            'choose_breakfast_category' => "Выберите завтрак",

            'breakfast_categories' => [
                'classic_omelette' => "Классический омлет",
                'beef_ham' => "Говяжья ветчина",
                'baked_chicken' => "Запеченная курица",
                'spicy_cheese' => "Пикантный сыр",
                'mushrooms' => "Грибы",
                'tomatoes' => "Таматы",
                'omelette_mix' => "Омлет «Микс»",
            ],

            // paste
            'choose_paste_category' => "Выберите пасту",

            'paste_categories' => [
                'arrabyata' => "Аррабьята",
                'alfredo' => "Альфредо",
                'bolognese' => "Болоньезе",
            ],

            // sandwich
            'choose_sandwich_category' => "Выберите сендвич",

            'sandwich_categories' => [
                'chicken_and_mayonnaise_sandwich' => "Сендвич с курицей и майонезом",
                'club_sandwich' => "Клаб сендвич",
                'tuna_sandwich' => "Сендвич с тунцом",
            ],

            // snacks
            'choose_snacks_category' => "Выберите закуску",

            'snacks_categories' => [
                'french_fries' => "Картофель фри",
                'cheese_sticks' => "Сырные палочки",
                'chicken_sticks' => "Куриные палочки",
            ],

            // salads
            'choose_salads_category' => "Выберите салат",

            'salads_categories' => [
                'greek' => "Греческий",
                'caesar_chicken' => "Цезарь куриный",
                'insala_ton' => "Инсала тонна",
                'vegetable' => "Овощной",
                'del_povero' => "Дель Поверо",
                'seasonal' => "Сезонный",
            ],

            // count

            'enter_count' => "Выберите количество, а затем нажмите подтвердить",

            'numbers' => [
                '1' => "1️⃣",
                '2' => "2️⃣",
                '3' => "3️⃣",
                '4' => "4️⃣",
                '5' => "5️⃣",
                '6' => "6️⃣",
                '7' => "7️⃣",
                '8' => "8️⃣",
                '9' => "9️⃣",
            ],

            'confirm' => "✅ Подтвердить",
            'back' => "Назад",
            'product_added' => "Товар добавлен в корзину.",

            // shopping cart page
            'your_order' => "Ваш заказ",
            'cart_is_empty' => "Корзина пуста",
            'pieces' => "штук",
            'soum' => "сум",
            'overall' => "Итого: ",
            'order_count' => "💵 Подсчет заказа",
            'checkout' => "🛎 Оформить заказ",
            'change' => "⚙️ Изменить",
            'clear' => "❌ Очистить",
            'back_btn' => "🔙 Назад",
            'press_x_to_clear' => "Нажмите ❌ для удаления продукта из корзины",
            'cart_is_cleared' => "Корзина очищена",
            'close_window' => "Закрыть окно",

            // delivery type page
            'choose_delivery_type' => "Выберите способ доставки:",
            'pickup' => "🚶 Самовывоз",
            'delivery' => "🚗 Доставка",

            // phone number page
            'send_your_phone_number' => "Пожалуйста, отправьте ваш контакт или введите номер телефона в формате 901234567.",
            'send_contact' => "✅ Отправить контакт",

            // order types
            'pickupType' => "Самовывоз",
            'deliveryType' => "Доставка",

            // location page
            'send_your_location' => "Пожалуйста, отправьте вашу локацию. (Не забудьте включить геоданные GPS)",
            'send_location' => "✅ Отправить локацию",
            'cant_send_location' => "✅ Не могу отправить локацию",

            'our_phone_number' => "<b>Наш номер телефона:</b>",

            'order' => "<b>ЗАКАЗ</b>",

            // order confirmation page
            'do_order' => "✅ Заказать",
            'cancel' => "❌ Отменить",

            // order confirmed
            'order_confirmed' => "Спасибо. Ваш заказ принят. Сейчас Вам перезвонит оператор, чтобы подтвердить Ваш заказ.",
            'order_canceled' => "Заказ отменен.",

            'new_order' => "Новый заказ!",

            'choose_count' => "Пожалуйста, выберите количество.",
        ];
}