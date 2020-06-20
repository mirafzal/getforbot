<?php

require_once 'Telegram.php';
require_once 'User.php';
require_once 'Pages.php';
require_once 'Texts.php';
require_once 'Categories.php';
require_once 'Products.php';
require_once 'ShoppingCart.php';

$bot_token =
    '1116316783:AAEn82g29U2_jf96ctctLvuUAtUcfx2q1i8';

echo "Vse norm";

$rootPath = "https://roboss.uz/getforbot/";

$telegram = new Telegram($bot_token);

$DEVELOPER_CHAT_ID = 635793263;

$ADMINS_CHAT_IDS = [
    635793263,
    742826077
];

$TEXTS = Texts::UZ;

$NUMBERS = ['0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣'];

$callback_query = $telegram->Callback_Query();
$callback_data = $telegram->Callback_Data();

$data = $telegram->getData();
$message = $data['message'];
$text = $message['text'];
$chatID = $telegram->ChatID();

if ($chatID == null) $chatID = $DEVELOPER_CHAT_ID;

global $user, $texts, $categories, $products;

init();

//$TEXTS = [];

//switchLanguage();

// main logic

// callback buttons
if ($callback_query !== null && $callback_query != '') {
    $callback_data = $telegram->Callback_Data();
    $chatID = $telegram->Callback_ChatID();
    $user = new User($chatID);

    if (strpos($callback_data, 'back') !== false) {
        showProduct($products->getProduct(substr($callback_data, 4)), true);
    } elseif (is_numeric($callback_data)) {
        $user->setProductOption($callback_data);
        showChooseCount();
    } elseif (substr($callback_data, 1) == 'count') {
        $content = ['chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'], 'text' => $texts->getText('product_added_to_cart')];
        ShoppingCart::addNewOrder($chatID, $user->getProductId(), $callback_data[0], $user->getProductOption());
        $telegram->editMessageText($content);
        showMainPage();
    } elseif ($callback_data == "closeWindow") {
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id']);
        $telegram->deleteMessage($content);
    } elseif (strpos($callback_data, "id") !== false) {
        $id = substr($callback_data, 2);
        $optionNum = explode(";", $callback_data);
        ShoppingCart::deleteProduct($chatID, $id, $optionNum[1]);
        if (ShoppingCart::getUserProducts($chatID)) {
            showProductsToClear(true);
        } else {
            $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'], 'text' => $TEXTS['cart_is_cleared']);
            $telegram->editMessageText($content);
            showMainPage();
        }
    } elseif (strpos($callback_data, "editProduct") !== false) {
        $optionNum = substr($callback_data, 11);
        $user->setProductOption($optionNum);
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'] - 1);
        $telegram->deleteMessage($content);
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id']);
        $telegram->deleteMessage($content);
        $options = [[$telegram->buildKeyboardButton("❌ Bekor qilish")]];
        $keyb = $telegram->buildKeyBoard($options, $onetime = false, $resize = true);
        $content = ['chat_id' => $chatID, 'text' => "Tovarni narxini kiriting:", 'reply_markup' => $keyb];
        $telegram->sendMessage($content);
        $user->setPage(Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_INPUT_PRICE);
    }

    //answer nothing with answerCallbackQuery, because it is required
    $content = ['callback_query_id' => $telegram->Callback_ID(), 'text' => "", 'show_alert' => false];
    $telegram->answerCallbackQuery($content);

} elseif ($text == "/start") {
    $user->setLanguage('uz');
    showMainPage();
} else {
    switch ($user->getPage()) {
        case Pages::START:
            switch ($text) {
                case "🇺🇿 O'zbekcha":
                    $user->setLanguage('uz');
                    init();
                    showMainPage();
                    break;
                case "🇷🇺 Русский":
                    $user->setLanguage('ru');
                    init();
                    showMainPage();
                    break;
                default:
                    showStart();
                    break;
            }
            break;
        case Pages::PAGE_MAIN:
            switch ($text) {
                case $texts->getText('page_main_btn_1'): // catalog
                    showCategoriesPage();
                    break;
                case $texts->getText('page_main_btn_2'): // korzina
                    showCartPage();
                    break;
                case $texts->getText('page_main_btn_3'): // qayta aloqa
                    showCompanyPhoneNumber();
                    break;
                case $texts->getText('page_main_btn_4'): // bot haqida
                    showAbout();
                    break;
                case $texts->getText('page_main_admin_btn'): // admin panel
                    if (in_array($chatID, $ADMINS_CHAT_IDS)) {
                        showAdminPage();
                    }
                    break;
                default:
                    showMainPage();
                    break;
            }
            break;
        case Pages::PAGE_CATALOG:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showMainPage();
                    break;
                default:
                    if (in_array($text, $categories->getAllNames())) {
                        $categoryId = $categories->getIdByName($text);
                        $user->setCategoryId($categoryId);
                        showProductsPage($categoryId);
                    }
            }
            break;
        case Pages::PAGE_PRODUCTS:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showCategoriesPage();
                    break;
                default:
                    if (in_array($text, $products->getNamesByCategoryId($user->getCategoryId()))) {
                        $productId = $products->getIdByName($text);
                        $user->setProductId($productId);
                        showProduct($products->getProduct($productId));
                    }
                    break;
            }
            break;
        case Pages::PAGE_PRODUCT_OPTIONS:
            // callback
            break;
        case Pages::PAGE_PRODUCT_COUNT:
            // callbackk
            break;
        case Pages::PAGE_SHOPPING_CART:
            switch ($text) {
                case $TEXTS['order_count']:
                    showCartPage();
                    break;
                case $TEXTS['checkout']:
                    showCheckoutPage();
                    break;
                case $TEXTS['change']:
                    showProductsToClear();
                    break;
                case $TEXTS['clear']:
                    showClearProducts();
                    break;
                case $TEXTS['back_btn']:
                    showMainPage();
                    break;
            }
            break;
        case Pages::PAGE_DELIVERY_TYPE:
            switch ($text) {
                case $TEXTS['pickup']:
                    $user->setOrderType('pickupType');
                    showFirstNamePage();
                    break;
                case $TEXTS['delivery']:
                    $user->setOrderType('deliveryType');
                    showFirstNamePage();
                    break;
                case $TEXTS['back_btn']:
                    showCartPage();
                    break;
            }
            break;
        case Pages::PAGE_FIRST_NAME:
            switch ($text) {
                case $TEXTS['back_btn']:
                    showCheckoutPage();
                    break;
                default:
                    $user->setFirstName($text);
                    if ($user->getOrderType() == 'pickupType') {
                        showPhoneNumberPage();
                    } else {
                        showLocationPage();
                    }
                    break;
            }
            break;
        case Pages::PAGE_PHONE_NUMBER:
            switch ($text) {
                case $TEXTS['back_btn']:
                    showFirstNamePage();
                    break;
                default:
                    if ($message['contact']['phone_number'] != "") {
                        $user->setPhoneNumber($message['contact']['phone_number']);
                    } else {
                        $user->setPhoneNumber($text);
                    }
                    showConfirmOrderPage();
                    break;
            }
            break;
        case Pages::PAGE_LOCATION:
            switch ($text) {
                case $TEXTS['back_btn']:
                    showFirstNamePage();
                    break;
                case $TEXTS['cant_send_location']:
                    $user->setLatitude("");
                    $user->setLongitude("");
                    showPhoneNumberPage();
                    break;
                default:
                    if ($message['location']['latitude'] && $message['location']['longitude']) {
                        $user->setLatitude($message['location']['latitude']);
                        $user->setLongitude($message['location']['longitude']);
                        showPhoneNumberPage();
                    }
                    break;
            }
            break;
        // admin page
        case Pages::PAGE_ADMIN:
            switch ($text) {
                case "➕ Tovar qo'shish":
                    $user->setProduct([]);
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_PRODUCT_CATEGORY);
                    break;
                case "➕ Kategoriya qo'shish":
                    showAdminAddCategory();
                    break;
                case "➖ Tovar o'chirish":
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_DELETE_PRODUCT_CATEGORY);
                    break;
                case "➖ Kategoriya o'chirish":
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_DELETE_CATEGORY);
                    break;
                case "Tovar narxini o'zgartirish":
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY);
                    break;
                case $texts->getText("back_btn"):
                    showMainPage();
                    break;
            }
            break;
        // add product
        case Pages::PAGE_ADMIN_PRODUCT_CATEGORY:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminPage();
                    break;
                default:
                    if (in_array($text, $categories->getAllNames())) {
                        $categoryId = $categories->getIdByName($text);
                        $product = $user->getProduct();
                        $product['optionsCount'] = 0;
                        $product['categoryId'] = $categoryId;
                        $user->setProduct($product);
                        $user->setCategoryId($categoryId);
                        showAdminSendProductPhoto();
                    }
            }
            break;
        case Pages::PAGE_ADMIN_PRODUCT_PHOTO:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_PRODUCT_CATEGORY);
                    break;
                default:
                    if (!$message['photo']) {
                        showAdminSendProductPhoto();
                    } else {
                        $photo = end($message['photo']);
                        $result = $telegram->getFile($photo['file_id']);
                        $filePath = $result['result']['file_path'];
                        $product = $user->getProduct();
                        $cnt = file_get_contents('photoCounter.txt');
                        $localFilePath = 'photos/' . $cnt . "." . explode(".", $filePath)[1];
                        $telegram->downloadFile($filePath, $localFilePath);
                        file_put_contents('photoCounter.txt', $cnt + 1);
                        $product['photoUrl'] = $localFilePath;
                        $user->setProduct($product);
                        sendMessage("Rasm muvaffaqiyatli yuklandi.");
                        showAdminSendProductName();
                    }
                    break;
            }
            break;
        case Pages::PAGE_ADMIN_PRODUCT_NAME:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminSendProductPhoto();
                    break;
                default:
                    $product = $user->getProduct();
                    $product['name'] = $text;
                    $user->setProduct($product);
                    showAdminSendProductInfo();
                    break;
            }
            break;
        case Pages::PAGE_ADMIN_PRODUCT_INFO:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminSendProductName();
                    break;
                default:
                    $product = $user->getProduct();
                    $product['info'] = $text;
                    $product['options'] = [];
                    $product['optionsCount'] = 0;
                    $user->setProduct($product);
                    showAdminSendProductOptionsName();
                    break;
            }
            break;
        case Pages::PAGE_ADMIN_PRODUCT_OPTIONS_NAME:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminSendProductInfo();
                    break;
                default:
                    $product = $user->getProduct();
                    $product['options'][$product['optionsCount']]['name'] = $text;
                    $user->setProduct($product);
                    showAdminSendProductOptionsPrice();
                    break;
            }
            break;
        case Pages::PAGE_ADMIN_PRODUCT_OPTIONS_PRICE:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminSendProductOptionsName();
                    break;
                default:
                    $product = $user->getProduct();
                    $product['options'][$product['optionsCount']]['price'] = $text;
                    $user->setProduct($product);
                    showProduct($product);
                    showAdminSendProductOptionsConfirmOrAddMore();
                    break;
            }
            break;
        case Pages::PAGE_ADMIN_PRODUCT_OPTIONS_CONFIRM_OR_ADD_MORE:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminSendProductInfo();
                    break;
                case $texts->getText("btn_add_more"):
                    $product = $user->getProduct();
                    $product['optionsCount'] = ((int)($product['optionsCount'])) + 1;
                    $user->setProduct($product);
                    showAdminSendProductOptionsName();
                    break;
                case $texts->getText('btn_done'):
                    if (Products::addNewProduct($user->getProduct())) {
                        sendMessage("tovar qo'shildi");
                        showAdminPage();
                    } else {
                        sendMessage("tovar qo'shilmadi...:(");
                    }
                    break;
                default:
                    sendChooseButtons();
                    break;
            }
            break;
        // add new category
        case Pages::PAGE_ADMIN_ADD_CATEGORY:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminPage();
                    break;
                default:
                    if (Categories::addNewCategory($text)) {
                        sendMessage("kategoriya qo'shildi.");
                        showAdminPage();
                    }
                    break;
            }
            break;
        // delete product
        case Pages::PAGE_ADMIN_DELETE_PRODUCT_CATEGORY:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminPage();
                    break;
                default:
                    if (in_array($text, $categories->getAllNames())) {
                        $categoryId = $categories->getIdByName($text);
                        $user->setCategoryId($categoryId);
                        showAdminChooseProduct($categoryId);
                    }
            }
            break;
        case Pages::PAGE_ADMIN_DELETE_PRODUCT_NAME:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_DELETE_PRODUCT_CATEGORY);
                    break;
                default:
                    if (in_array($text, $products->getNamesByCategoryId($user->getCategoryId()))) {
                        $productId = $products->getIdByName($text);
                        $user->setProductId($productId);
                        if (Products::deleteProduct($productId)) {
                            sendMessage("Tovar o'chirildi.");
                            showAdminPage();
                        } else {
                            sendMessage("Tovar o'chirilmadi. Qayta urinib ko'ring.");
                        }
                    }
                    break;
            }
            break;
        // delete category
        case Pages::PAGE_ADMIN_DELETE_CATEGORY:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminPage();
                    break;
                default:
                    if (in_array($text, $categories->getAllNames())) {
                        $categoryId = $categories->getIdByName($text);
                        if (Categories::deleteCategory($categoryId)) {
                            sendMessage("Kategoriya o'chirildi.");
                            showAdminPage();
                        } else {
                            sendMessage("Kategoriya o'chirilmadi. Qayta urinib ko'ring.");
                        }
                    }
            }
            break;
        case Pages::PAGE_ORDER_CONFIRMATION:
            switch ($text) {
                case $TEXTS['back_btn']:
                    showPhoneNumberPage();
                    break;
                case $TEXTS['do_order']:
                    showOrderConfirmed();
                    ShoppingCart::clearShoppingCart($user);
                    break;
                case $TEXTS['cancel']:
                    showOrderCanceled();
                    break;

            }
            break;
        // edit price
        case Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminPage();
                    break;
                default:
                    if (in_array($text, $categories->getAllNames())) {
                        $categoryId = $categories->getIdByName($text);
                        $user->setCategoryId($categoryId);
                        showAdminChooseProduct($categoryId, Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_PRODUCT);
                        break;
                    }
            }
            break;
        case Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_PRODUCT:
            switch ($text) {
                case $texts->getText("back_btn"):
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY);
                    break;
                default:
                    if (in_array($text, $products->getNamesByCategoryId($user->getCategoryId()))) {
                        showAdminSendProductNewPrice($text);
                    }
                    break;
            }
            break;
        case Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_INPUT_PRICE:
            switch ($text) {
                case "❌ Bekor qilish":
                    showAdminChooseProductCategory(Pages::PAGE_ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY);
                    break;
                default:
                    $product = $products->getProduct($user->getProductId());
                    $optionNum = $user->getProductOption();
                    $product['options'][$optionNum]['price'] = $text;
                    if (Products::updateProduct($product)) {
                        sendMessage("Tovar narxi o'zgartirildi.");
                        showAdminPage();
                    }
                    break;
            }
            break;
    }
}

function showStart()
{
    global $user;
    $user->setPage(Pages::START);
    $buttons = ["🇷🇺 Русский", "🇺🇿 O'zbekcha"];
    $textToSend = "Пожалуйста выберите язык. 👇\n\nIltimos, tilni tanlang. 👇";
    sendTextWithKeyboard($buttons, $textToSend);
}

function showMainPage()
{
    global $user, $texts, $chatID, $ADMINS_CHAT_IDS;
    $user->setPage(Pages::PAGE_MAIN);
    $buttons = $texts->getArrayLike("page_main_btn");
    if (in_array($chatID, $ADMINS_CHAT_IDS)) {
        $buttons[] = $texts->getText("page_main_admin_btn");
    }
    $textToSend = $texts->getText("page_main_text");
    sendTextWithKeyboard($buttons, $textToSend);
}

function showCategoriesPage()
{
    global $user, $texts, $categories;
    $buttons = $categories->getAllNames();
    if (!$buttons) {
        sendMessage($texts->getText('page_categories_text_no_category'));
    } else {
        $user->setPage(Pages::PAGE_CATALOG);
        $textToSend = $texts->getText("page_categories_text");
        sendTextWithKeyboard($buttons, $textToSend, true);
    }
}

function showProductsPage($categoryId)
{
    global $user, $texts, $products;
    $buttons = $products->getNamesByCategoryId($categoryId);
    if (!$buttons) {
        sendMessage($texts->getText('page_products_text_no_product'));
    } else {
        $user->setPage(Pages::PAGE_PRODUCTS);
        $textToSend = $texts->getText("page_products_text");
        sendTextWithKeyboard($buttons, $textToSend, true);
    }
}

function showProduct($product, $edit = false, $editPrice = "")
{
    global $telegram, $chatID, $rootPath, $texts, $user, $callback_query;

    if ($product['photoUrl']) {
        $photoUrl = $rootPath . $product['photoUrl'];
    } else {
        $photoUrl = $rootPath . "photos/empty-img.png";
    }
    $caption = "";
    if ($product['name']) $caption .= $product['name'] . "\n\n";
    if ($product['info']) {
        $caption .= $product['info'] . "\n";
    } else {
        $caption .= "no info\n";
    }
//    $content = ['chat_id' => $chatID, 'photo' => $photoUrl, 'caption' => $caption];
    $content = ['chat_id' => $chatID, 'text' => "<a href=\"{$photoUrl}\"> </a>" . $caption, 'parse_mode' => "HTML"];
    if ($product['options']) {
        $option = [];
        for ($i = 0; $i < count($product['options']); $i++) {
            $name = $product['options'][$i]['name'];
            $price = number_format($product['options'][$i]['price'], 0, "", " ") . " " . $texts->getText('soum');
            $option[] = [$telegram->buildInlineKeyboardButton($name . " - " . $price, '', $editPrice . $i . "")];
        }
        $keyb = $telegram->buildInlineKeyBoard($option);
        $content['reply_markup'] = $keyb;
    }
//    $telegram->sendPhoto($content);
    if ($edit) {
        $content['message_id'] = $callback_query['message']['message_id'];
        $telegram->editMessageText($content);
    } else {
        $telegram->sendMessage($content);
    }
}

function showChooseCount()
{
    global $user, $NUMBERS, $chatID, $texts, $telegram, $callback_query;
    $options = [];
    for ($cnt = 0, $i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            $cnt++;
            $options[$i][$j] = $telegram->buildInlineKeyboardButton($NUMBERS[$cnt], '', $cnt . 'count');
        }
    }
    $options[] = [$telegram->buildInlineKeyboardButton($texts->getText('back_btn'), "", 'back' . $user->getProductId())];
    $keyb = $telegram->buildInlineKeyBoard($options);
    $content = [
        'chat_id' => $chatID,
        'message_id' => $callback_query['message']['message_id'],
        'text' => $texts->getText('page_choose_count_text'),
        'reply_markup' => $keyb
    ];
    $telegram->editMessageText($content);
}

function showCartPage()
{
    global $user, $telegram, $chatID, $TEXTS, $products;

    if (!ShoppingCart::getUserProducts($chatID)) {
        $content = [
            'chat_id' => $telegram->ChatID(),
            'text' => $TEXTS['cart_is_empty']
        ];
        $telegram->sendMessage($content);
    } else {
        $user->setPage(Pages::PAGE_SHOPPING_CART);
        $content = [
            'chat_id' => $telegram->ChatID(),
            'text' => $TEXTS['your_order']
        ];
        $telegram->sendMessage($content);

        $orderText = "";
        $overallPrice = 0;

        foreach (ShoppingCart::getUserProducts($chatID) as $productArr) {
            $productCount = $productArr['count'];
            $optionNum = $productArr['optionNum'];
            $product = $products->getProduct($productArr['id']);
            $price = ((int)($product['options'][$optionNum]['price'])) * ((int)($productCount));
            $overallPrice += $price;
            $orderText .= $product['name'] . " " . $product['options'][$optionNum]['name'] . ", " . $productCount . " " . $TEXTS['pieces'] . " - " . number_format($price, 0, "", " ") . " " . $TEXTS['soum'] . "\n\n";
        }

        $orderText .= "--------------------\n";
        $orderText .= $TEXTS['overall'] . number_format($overallPrice, 0, "", " ") . " " . $TEXTS['soum'];

        $option = [
            [$telegram->buildKeyboardButton($TEXTS['order_count']), $telegram->buildKeyboardButton($TEXTS['checkout'])],
            [$telegram->buildKeyboardButton($TEXTS['change']), $telegram->buildKeyboardButton($TEXTS['clear'])],
            [$telegram->buildKeyboardButton($TEXTS['back_btn'])],
        ];
        $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
        $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $orderText, 'parse_mode' => "HTML");
        $telegram->sendMessage($content);
    }
}

function showCompanyPhoneNumber()
{
    sendMessage("Bizning telefon raqamimiz:
+998977141171");
}

function showAbout()
{
    sendMessage("👏 Assalamu alaykum. Getfor kompaniyasi botiga xush kelibsiz!

🚗 Getfor kompaniyasi oziq-ovqatlarni Toshkent shahri bo'ylab uyingizgacha yetkazib beradi.
Yetkazib berish kun bo'yi amalga oshiriladi.🥯🍞🥖🥩🍗🍫🍿☕️

⏰ Ish vaqti: 8:00 dan 20:00 gacha

💵 Yetkazib berish narhi: 10 000 so'm

@getforbot");
}

function showAdminPage()
{
    global $user;
    $user->setPage(Pages::PAGE_ADMIN);
    $buttons = ["➕ Tovar qo'shish", "➕ Kategoriya qo'shish", "➖ Tovar o'chirish", "➖ Kategoriya o'chirish",
        "Tovar narxini o'zgartirish"];
    $textToSend = "Admin Panelga xush kelibsiz!";
    sendTextWithKeyboard($buttons, $textToSend, true);
}

function showAdminChooseProductCategory($page)
{
    global $user, $texts, $categories;
    $buttons = $categories->getAllNames();
    if (!$buttons) {
        sendMessage("Iltimos, avval kategoriya qo'shing.");
    } else {
        $user->setPage($page);
        $textToSend = $texts->getText("page_categories_text");
        sendTextWithKeyboard($buttons, $textToSend, true);
    }
}


function showAdminSendProductPhoto()
{
    global $user;
    $user->setPage(Pages::PAGE_ADMIN_PRODUCT_PHOTO);
    sendTextWithKeyboard([], "Iltimos, tovar rasmini yuboring.", true);
}

function showAdminSendProductName()
{
    global $user;
    $user->setPage(Pages::PAGE_ADMIN_PRODUCT_NAME);
    sendTextWithKeyboard([], "Iltimos, tovar nomini yuboring.", true);
}

function showAdminSendProductInfo()
{
    global $user;
    $user->setPage(Pages::PAGE_ADMIN_PRODUCT_INFO);
    sendTextWithKeyboard([], "Iltimos, tovar haqidagi ma'lumotni yuboring.", true);
}

function showAdminSendProductOptionsName()
{
    global $user;
    $user->setPage(Pages::PAGE_ADMIN_PRODUCT_OPTIONS_NAME);
    sendTextWithKeyboard([], "Iltimos, tovar turlarini yuboring.", true);
    sendMessage("Tovar turining nomini yuboring.");
}

function showAdminSendProductOptionsPrice()
{
    global $user;
    $user->setPage(Pages::PAGE_ADMIN_PRODUCT_OPTIONS_PRICE);
    sendMessage("Tovar turining narxini yuboring.");
}


function showAdminSendProductOptionsConfirmOrAddMore()
{
    global $user, $texts;
    $user->setPage(Pages::PAGE_ADMIN_PRODUCT_OPTIONS_CONFIRM_OR_ADD_MORE);
    sendTextWithKeyboard([$texts->getText("btn_add_more"), $texts->getText('btn_done')], "Tur qo'shildi. {$texts->getText("btn_add_more")} yoki {$texts->getText('btn_done')} tugmasini bosing.", true);
}

function showAdminAddCategory()
{
    global $user;
    $user->setPage(Pages::PAGE_ADMIN_ADD_CATEGORY);
    sendTextWithKeyboard([], "Kategoriya nomini kiriting.", true);
}

function showAdminChooseProduct($categoryId, $page = Pages::PAGE_ADMIN_DELETE_PRODUCT_NAME)
{
    global $user, $texts, $products;
    $buttons = $products->getNamesByCategoryId($categoryId);
    if (!$buttons) {
        sendMessage($texts->getText('page_products_text_no_product'));
    } else {
        $user->setPage($page);
        $textToSend = $texts->getText("page_products_text");
        sendTextWithKeyboard($buttons, $textToSend, true);
    }
}

function sendTextWithKeyboard($buttons, $text, $backBtn = false)
{
    global $telegram, $chatID, $texts;
    $option = [];
    if (count($buttons) % 2 == 0) {
        for ($i = 0; $i < count($buttons); $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
    } else {
        for ($i = 0; $i < count($buttons) - 1; $i += 2) {
            $option[] = array($telegram->buildKeyboardButton($buttons[$i]), $telegram->buildKeyboardButton($buttons[$i + 1]));
        }
        $option[] = array($telegram->buildKeyboardButton(end($buttons)));
    }
    if ($backBtn) {
        $option[] = [$telegram->buildKeyboardButton($texts->getText("back_btn"))];
    }
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function init()
{
    global $chatID, $user, $texts, $categories, $products;
    $user = new User($chatID);
    $texts = new Texts($user->getLanguage());
    $categories = new Categories($user->getLanguage());
    $products = new Products($user->getLanguage());
}

function sendMessage($text)
{
    global $telegram, $chatID;
    $telegram->sendMessage(['chat_id' => $chatID, 'text' => $text]);
}

function answerCallbackEmpty()
{
    global $telegram;
    $content = ['callback_query_id' => $telegram->Callback_ID(), 'text' => "", 'show_alert' => false];
    $telegram->answerCallbackQuery($content);
}

function sendChooseButtons()
{
    sendMessage("Iltimos, quyidagi tugmalardan birini tanlang.");
}

//function switchLanguage()
//{
//    global $user, $TEXTS;
//    switch ($user->getLanguage()) {
//        case 'uz':
//            $TEXTS = Texts::UZ;
//            break;
//        case 'ru':
//            $TEXTS = Texts::RU;
//            break;
//    }
//}

function showCheckoutPage()
{
    global $user, $telegram, $chatID, $TEXTS;
    $user->setPage(Pages::PAGE_DELIVERY_TYPE);

    $option = [
        [$telegram->buildKeyboardButton($TEXTS['pickup']), $telegram->buildKeyboardButton($TEXTS['delivery'])],
        [$telegram->buildKeyboardButton($TEXTS['back_btn'])],
    ];
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $TEXTS['choose_delivery_type'], 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showProductsToClear($change = false)
{
    global $user, $telegram, $chatID, $TEXTS, $callback_query, $products;

    $option = [];
    foreach (ShoppingCart::getUserProducts($chatID) as $productArr) {
        $product = $products->getProduct($productArr['id']);
        $optionNum = $productArr['optionNum'];
        $option[] = [$telegram->buildInlineKeyboardButton($product['name'] . " " . $product['options'][$optionNum]['name'], "", "ggg"), $telegram->buildInlineKeyboardButton("❌", "", "id" . $product['id'] . ";" . $productArr['optionNum'])];
    }
    $option[] = [$telegram->buildInlineKeyboardButton($TEXTS['close_window'], "", "closeWindow")];
    $keyboard = $telegram->buildInlineKeyBoard($option);
    if ($change) {
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'], 'reply_markup' => $keyboard, 'text' => $TEXTS['press_x_to_clear'], 'parse_mode' => "HTML");
        $telegram->editMessageText($content);
    } else {
        $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $TEXTS['press_x_to_clear'], 'parse_mode' => "HTML");
        $telegram->sendMessage($content);
    }
}

function showClearProducts()
{
    global $user, $telegram, $chatID, $TEXTS;
    ShoppingCart::clearShoppingCart($user);
    $content = [
        'chat_id' => $telegram->ChatID(),
        'text' => $TEXTS['cart_is_cleared']
    ];
    $telegram->sendMessage($content);
    showMainPage();

}

function showPhoneNumberPage()
{
    global $user, $telegram, $chatID, $TEXTS;
    $user->setPage(Pages::PAGE_PHONE_NUMBER);

    $option = [
        [$telegram->buildKeyboardButton($TEXTS['send_contact'], true, false)],
        [$telegram->buildKeyboardButton($TEXTS['back_btn'])],
    ];
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $TEXTS['send_your_phone_number'], 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showLocationPage()
{
    global $user, $telegram, $chatID, $TEXTS;
    $user->setPage(Pages::PAGE_LOCATION);

    $option = [
        [$telegram->buildKeyboardButton($TEXTS['send_location'], false, true)],
        [$telegram->buildKeyboardButton($TEXTS['cant_send_location'])],
        [$telegram->buildKeyboardButton($TEXTS['back_btn'])],
    ];
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $TEXTS['send_your_location'], 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showConfirmOrderPage()
{
    global $user, $telegram, $chatID, $TEXTS, $products;
    $user->setPage(Pages::PAGE_ORDER_CONFIRMATION);

    $orderText = "<b>" . strtoupper($TEXTS[$user->getOrderType()]) . "</b>";
    $orderText .= "\n-------------\n\n";
    $orderText .= $TEXTS['order'] . ":\n";

    $overallPrice = 0;

    foreach (ShoppingCart::getUserProducts($chatID) as $productArr) {
        $productCount = $productArr['count'];
        $optionNum = $productArr['optionNum'];
        $product = $products->getProduct($productArr['id']);
        $price = ((int)($product['options'][$optionNum]['price'])) * ((int)($productCount));
        $overallPrice += $price;
        $orderText .= $product['name'] . " " . $product['options'][$optionNum]['name'] . ", " . $productCount . " " . $TEXTS['pieces'] . " - " . number_format($price, 0, "", " ") . " " . $TEXTS['soum'] . "\n\n";
    }

    $orderText .= "--------------------\n";
    $orderText .= $TEXTS['overall'] . number_format($overallPrice, 0, "", " ") . " " . $TEXTS['soum'];
    $orderText .= "\n\nIsm: " . $user->getFirstName();
    $orderText .= "\nTelefon raqam: " . $user->getPhoneNumber();

    $option = [
        [$telegram->buildKeyboardButton($TEXTS['do_order']), $telegram->buildKeyboardButton($TEXTS['cancel'])],
        [$telegram->buildKeyboardButton($TEXTS['back_btn'])],
    ];
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $orderText, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);

    if ($user->getLongitude() != "" && $user->getLatitude() != "") {
        $content = array('chat_id' => $chatID, 'latitude' => str_replace(",", ".", $user->getLatitude()), 'longitude' => str_replace(",", ".", $user->getLongitude()));
        $telegram->sendLocation($content);
    }

    $user->setOrderText($orderText);
}

function showOrderConfirmed()
{
    global $user, $telegram, $chatID, $TEXTS, $ADMINS_CHAT_IDS;

    $content = array('chat_id' => $chatID, 'text' => $TEXTS['order_confirmed'], 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
    showMainPage();

    foreach ($ADMINS_CHAT_IDS as $admin_chat_id) {
        $content = array('chat_id' => $admin_chat_id, 'text' => $TEXTS['new_order'], 'parse_mode' => "HTML");
        $telegram->sendMessage($content);
        $content = array('chat_id' => $admin_chat_id, 'text' => $user->getOrderText(), 'parse_mode' => "HTML");
        $telegram->sendMessage($content);
        if ($user->getLongitude() != "" && $user->getLatitude() != "") {
            $content = array('chat_id' => $admin_chat_id, 'latitude' => $user->getLatitude(), 'longitude' => $user->getLongitude());
            $telegram->sendLocation($content);
        }
    }
}

function showOrderCanceled()
{
    global $user, $telegram, $chatID, $TEXTS;

    $content = array('chat_id' => $chatID, 'text' => $TEXTS['order_canceled'], 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
    showMainPage();
}

function showFirstNamePage()
{
    global $user, $telegram, $chatID, $TEXTS;
    $user->setPage(Pages::PAGE_FIRST_NAME);

    $option = [
        [$telegram->buildKeyboardButton($TEXTS['back_btn'])],
    ];
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => "Iltimos, ismingizni kiriting:", 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showAdminSendProductNewPrice($productName)
{
    global $user, $telegram, $chatID, $products;

    $productId = $products->getIdByName($productName);
    $user->setProductId($productId);

    sendMessage("Tovar turini tanlang");
    showProduct($products->getProduct($productId), false, "editProduct");
}
