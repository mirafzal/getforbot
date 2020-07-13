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

$NUMBERS = ['0️⃣', '1️⃣', '2️⃣', '3️⃣', '4️⃣', '5️⃣', '6️⃣', '7️⃣', '8️⃣', '9️⃣'];

$callback_query = $telegram->Callback_Query();
$callback_data = $telegram->Callback_Data();

$data = $telegram->getData();
$message = $data['message'];
$text = $message['text'];
$chatID = $telegram->ChatID();

if ($chatID == null) $chatID = $DEVELOPER_CHAT_ID;

global $user, $texts, $categories, $products;

$user = new User($chatID);
$texts = new Texts($user->getLanguage());
$categories = new Categories($user->getLanguage());
$products = new Products($user->getLanguage());

init();

ini_set('precision', 100);

// main logic

// callback buttons
if ($callback_query !== null && $callback_query != '') {
    $callback_data = $telegram->Callback_Data();
    $chatID = $telegram->Callback_ChatID();
    $user = new User($chatID);

    if (strpos($callback_data, "editProduct") !== false) {
        $optionNum = substr($callback_data, 11);
        $optionNum = explode(',', $optionNum);
        $optionNum = substr($optionNum[0], 6);
        $user->setProductOption($optionNum);
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'] - 1);
        $telegram->deleteMessage($content);
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id']);
        $telegram->deleteMessage($content);
        sendTextWithKeyboard(["❌ Bekor qilish"], "Tovarni narxini kiriting:");
        $user->setPage(Pages::ADMIN_EDIT_PRODUCT_PRICE_INPUT_PRICE);
    } elseif (strpos($callback_data, 'back') !== false) {
        showProduct($products->getProduct(substr($callback_data, 4)), true);
    } elseif (strpos($callback_data, 'count') !== false) {
        $mdata = explode(',', $callback_data);
        $optionNum = substr($mdata[1], 6);
        $productId = substr($mdata[2], 9);
        $content = ['chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'], 'text' => $texts->get('product_added_to_cart')];
        ShoppingCart::addNewOrder($chatID, $productId, $callback_data[0], $optionNum);
        $options = [[$telegram->buildInlineKeyboardButton($texts->get('go_to_cart'), "", "shoppingcart")]];
        $keyb = $telegram->buildInlineKeyBoard($options);
        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID(), 'text' => $texts->get('product_added_to_cart'), 'reply_markup' => $keyb];
        $telegram->editMessageText($content);
    } elseif (strpos($callback_data, 'option') !== false) {
        $mdata = explode(',', $callback_data);
        $optionNum = substr($mdata[0], 6);
        $productId = substr($mdata[1], 9);
        $user->setProductOption($optionNum);
        showChooseCount($productId, $optionNum);
    } elseif ($callback_data == "closeWindow") {
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id']);
        $telegram->deleteMessage($content);
    } elseif ($callback_data == "shoppingcart") {
        $content = ['chat_id' => $chatID, 'message_id' => $telegram->MessageID()];
        $telegram->deleteMessage($content);
        showCartPage();
    } elseif (strpos($callback_data, 'id') !== false) {
        $mdata = explode(",", $callback_data);
        $id = substr($callback_data, 2);
        $optionNum = $mdata[1];
        ShoppingCart::deleteProduct($chatID, $id, $optionNum);
        if (ShoppingCart::getUserProducts($chatID)) {
            showProductsToClear(true);
        } else {
            $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'], 'text' => "Savat bo'sh holatda");
            $telegram->editMessageText($content);
            showMainPage();
        }
    }

    //answer nothing with answerCallbackQuery, because it is required
    $content = ['callback_query_id' => $telegram->Callback_ID(), 'text' => "", 'show_alert' => false];
    $telegram->answerCallbackQuery($content);

} elseif ($text == "/start") {
    $user->setLanguage('uz');
    showStart();
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
        case Pages::MAIN:
            switch ($text) {
                case $texts->get('page_main_btn_1'): // catalog
                    showCategoriesPage();
                    break;
                case $texts->get('page_main_btn_2'): // korzina
                    showCartPage();
                    break;
                case $texts->get('page_main_btn_3'): // qayta aloqa
                    showCompanyPhoneNumber();
                    break;
                case $texts->get('page_main_btn_4'): // bot haqida
                    showAbout();
                    break;
                case $texts->get('page_main_btn_5'): // bot haqida
                    $user->setLanguage($user->getLanguage() == 'uz' ? 'ru' : 'uz');
                    init();
                    showMainPage();
                    break;
                case $texts->get('page_main_admin_btn'): // admin panel
                    if (in_array($chatID, $ADMINS_CHAT_IDS)) {
                        showAdminPage();
                    }
                    break;
                default:
                    showMainPage();
                    break;
            }
            break;
        case Pages::CATALOG:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::PRODUCTS:
            switch ($text) {
                case $texts->get("back_btn"):
                    showCategoriesPage();
                    break;
                case $texts->get('show_all'):
                    $productNames = $products->getNamesByCategoryId($user->getCategoryId());
                    foreach ($productNames as $productName) {
                        $productId = $products->getIdByName($productName);
                        showProduct($products->getProduct($productId));
                    }
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
        case Pages::SHOPPING_CART:
            switch ($text) {
                case $texts->get('order_count'):
                    showCartPage();
                    break;
                case $texts->get('checkout'):
                    $user->setOrderType('deliveryType');
                    showFirstNamePage();
                    break;
                case $texts->get('change'):
                    showProductsToClear();
                    break;
                case $texts->get('clear'):
                    showClearProducts();
                    break;
                case $texts->get('back_btn'):
                    showMainPage();
                    break;
            }
            break;
        case Pages::DELIVERY_TYPE:
            switch ($text) {
                case $texts->get('pickup'):
                    $user->setOrderType('pickupType');
                    showFirstNamePage();
                    break;
                case $texts->get('delivery'):
                    $user->setOrderType('deliveryType');
                    showFirstNamePage();
                    break;
                case $texts->get('back_btn'):
                    showCartPage();
                    break;
            }
            break;
        case Pages::FIRST_NAME:
            switch ($text) {
                case $texts->get('back_btn'):
                    showCartPage();
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
        case Pages::PHONE_NUMBER:
            switch ($text) {
                case $texts->get('back_btn'):
                    showLocationPage();
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
        case Pages::LOCATION:
            switch ($text) {
                case $texts->get('back_btn'):
                    showFirstNamePage();
                    break;
//                case $texts->get('cant_send_location'):
//                    $user->setLatitude("");
//                    $user->setLongitude("");
//                    showPhoneNumberPage();
//                    break;
                default:
                    if ($message['location']['latitude'] && $message['location']['longitude']) {
                        $user->setLatitude($message['location']['latitude']);
                        $user->setLongitude($message['location']['longitude']);
                        showPhoneNumberPage();
                    }
                    break;
            }
            break;
        case Pages::ORDER_CONFIRMATION:
            switch ($text) {
                case $texts->get('back_btn'):
                    showPhoneNumberPage();
                    break;
                case $texts->get('do_order'):
                    showOrderConfirmed();
                    ShoppingCart::clearShoppingCart($user);
                    break;
                case $texts->get('cancel'):
                    showOrderCanceled();
                    break;

            }
            break;
        // admin page
        case Pages::ADMIN:
            switch ($text) {
                case "➕ Tovar qo'shish":
                    $user->setProduct([]);
                    showAdminChooseProductCategory(Pages::ADMIN_PRODUCT_CATEGORY);
                    break;
                case "➕ Kategoriya qo'shish":
                    showAdminAddCategory();
                    break;
                case "➖ Tovar o'chirish":
                    showAdminChooseProductCategory(Pages::ADMIN_DELETE_PRODUCT_CATEGORY);
                    break;
                case "➖ Kategoriya o'chirish":
                    showAdminChooseProductCategory(Pages::ADMIN_DELETE_CATEGORY);
                    break;
                case "Tovar narxini o'zgartirish":
                    showAdminChooseProductCategory(Pages::ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY);
                    break;
                case $texts->get("back_btn"):
                    showMainPage();
                    break;
            }
            break;
        // add product
        case Pages::ADMIN_PRODUCT_CATEGORY:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::ADMIN_PRODUCT_PHOTO:
            switch ($text) {
                case $texts->get("back_btn"):
                    showAdminChooseProductCategory(Pages::ADMIN_PRODUCT_CATEGORY);
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
        case Pages::ADMIN_PRODUCT_NAME:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::ADMIN_PRODUCT_INFO:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::ADMIN_PRODUCT_OPTIONS_NAME:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::ADMIN_PRODUCT_OPTIONS_PRICE:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::ADMIN_PRODUCT_OPTIONS_CONFIRM_OR_ADD_MORE:
            switch ($text) {
                case $texts->get("back_btn"):
                    showAdminSendProductInfo();
                    break;
                case $texts->get("btn_add_more"):
                    $product = $user->getProduct();
                    $product['optionsCount'] = ((int)($product['optionsCount'])) + 1;
                    $user->setProduct($product);
                    showAdminSendProductOptionsName();
                    break;
                case $texts->get('btn_done'):
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
        case Pages::ADMIN_ADD_CATEGORY:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::ADMIN_DELETE_PRODUCT_CATEGORY:
            switch ($text) {
                case $texts->get("back_btn"):
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
        case Pages::ADMIN_DELETE_PRODUCT_NAME:
            switch ($text) {
                case $texts->get("back_btn"):
                    showAdminChooseProductCategory(Pages::ADMIN_DELETE_PRODUCT_CATEGORY);
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
        case Pages::ADMIN_DELETE_CATEGORY:
            switch ($text) {
                case $texts->get("back_btn"):
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
        // edit price
        case Pages::ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY:
            switch ($text) {
                case $texts->get("back_btn"):
                    showAdminPage();
                    break;
                default:
                    if (in_array($text, $categories->getAllNames())) {
                        $categoryId = $categories->getIdByName($text);
                        $user->setCategoryId($categoryId);
                        showAdminChooseProduct($categoryId, Pages::ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_PRODUCT);
                        break;
                    }
            }
            break;
        case Pages::ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_PRODUCT:
            switch ($text) {
                case $texts->get("back_btn"):
                    showAdminChooseProductCategory(Pages::ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY);
                    break;
                default:
                    if (in_array($text, $products->getNamesByCategoryId($user->getCategoryId()))) {
                        showAdminSendProductNewPrice($text);
                    }
                    break;
            }
            break;
        case Pages::ADMIN_EDIT_PRODUCT_PRICE_INPUT_PRICE:
            switch ($text) {
                case "❌ Bekor qilish":
                    showAdminChooseProductCategory(Pages::ADMIN_EDIT_PRODUCT_PRICE_CHOOSE_CATEGORY);
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

// user pages

function showStart()
{
    $page = Pages::START;
    $sendText = "Пожалуйста выберите язык. 👇\n\nIltimos, tilni tanlang. 👇";
    $buttons = ["🇷🇺 Русский", "🇺🇿 O'zbekcha"];
    showPage($page, $sendText, $buttons);
}

function showMainPage()
{
    global $texts, $chatID, $ADMINS_CHAT_IDS;
    $page = Pages::MAIN;
    $buttons = $texts->getArrayLike("page_main_btn");
    if (in_array($chatID, $ADMINS_CHAT_IDS))
        $buttons[] = $texts->get("page_main_admin_btn");
    $textToSend = $texts->get("page_main_text");
    showPage($page, $textToSend, $buttons);
}

function showCategoriesPage()
{
    global $texts, $categories;
    $buttons = $categories->getAllNames();
    if (!$buttons) {
        sendMessage($texts->get('page_categories_text_no_category'));
    } else {
        $page = Pages::CATALOG;
        $textToSend = $texts->get("page_categories_text");
        showPage($page, $textToSend, $buttons, true);
    }
}

// categories sub pages

function showProductsPage($categoryId)
{
    global $texts, $products;
    $buttons = $products->getNamesByCategoryId($categoryId);
    if (!$buttons) {
        sendMessage($texts->get('page_products_text_no_product'));
    } else {
        $page = Pages::PRODUCTS;
        $textToSend = $texts->get("page_products_text");
        showPage($page, $textToSend, $buttons, true, true);
    }
}

function showProduct($product, $edit = false, $editPrice = "")
{
    global $telegram, $chatID, $rootPath, $texts, $callback_query;

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
    $content = ['chat_id' => $chatID, 'text' => "<a href=\"{$photoUrl}\"> </a>" . $caption, 'parse_mode' => "HTML"];
    if ($product['options']) {
        $option = [];
        for ($i = 0; $i < count($product['options']); $i++) {
            $name = $product['options'][$i]['name'];
            $price = number_format($product['options'][$i]['price'], 0, "", " ") . " " . $texts->get('soum');
            $option[] = [$telegram->buildInlineKeyboardButton($name . " - " . $price, '', $editPrice . "option" . $i . ",productId" . $product['id'])];
        }
        $keyb = $telegram->buildInlineKeyBoard($option);
        $content['reply_markup'] = $keyb;
    }
    if ($edit) {
        $content['message_id'] = $callback_query['message']['message_id'];
        $telegram->editMessageText($content);
    } else {
        $telegram->sendMessage($content);
    }
}

function showChooseCount($productId, $optionNum)
{
    global $NUMBERS, $chatID, $texts, $telegram, $callback_query;
    $options = [];
    for ($cnt = 0, $i = 0; $i < 3; $i++) {
        for ($j = 0; $j < 3; $j++) {
            $cnt++;
            $options[$i][$j] = $telegram->buildInlineKeyboardButton($NUMBERS[$cnt], '', $cnt . 'count,option' . $optionNum . ",productId" . $productId);
        }
    }
    $options[] = [$telegram->buildInlineKeyboardButton($texts->get('back_btn'), "", 'back' . $productId)];
    $keyb = $telegram->buildInlineKeyBoard($options);
    $content = [
        'chat_id' => $chatID,
        'message_id' => $callback_query['message']['message_id'],
        'text' => $texts->get('page_choose_count_text'),
        'reply_markup' => $keyb
    ];
    $telegram->editMessageText($content);
}

// end categories sub pages

function showCartPage()
{
    global $user, $telegram, $chatID, $texts, $products;

    if (!ShoppingCart::getUserProducts($chatID)) {
        $content = [
            'chat_id' => $telegram->ChatID(),
            'text' => $texts->get('cart_is_empty')
        ];
        $telegram->sendMessage($content);
    } else {
        $user->setPage(Pages::SHOPPING_CART);
        $content = [
            'chat_id' => $telegram->ChatID(),
            'text' => $texts->get('your_order')
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
            $orderText .= $product['name'] . " " . $product['options'][$optionNum]['name'] . ", " . $productCount . " " . $texts->get('pieces') . " - " . number_format($price, 0, "", " ") . " " . $texts->get('soum') . "\n\n";
        }

        $orderText .= "--------------------\n";
        $orderText .= $texts->get('overall') . " " . number_format($overallPrice, 0, "", " ") . " " . $texts->get('soum');

        $buttons = [$texts->get('order_count'), $texts->get('checkout'),
            $texts->get('change'), $texts->get('clear')];
        sendTextWithKeyboard($buttons, $orderText, true);
    }
}

// shopping cart sub pages

function showProductsToClear($change = false)
{
    global $telegram, $chatID, $texts, $callback_query, $products;

    $option = [];
    foreach (ShoppingCart::getUserProducts($chatID) as $productArr) {
        $product = $products->getProduct($productArr['id']);
        $optionNum = $productArr['optionNum'];
        $option[] = [$telegram->buildInlineKeyboardButton($product['name'] . " " . $product['options'][$optionNum]['name'], "", "ggg"), $telegram->buildInlineKeyboardButton("❌", "", "id" . $product['id'] . "," . $productArr['optionNum'])];
    }
    $option[] = [$telegram->buildInlineKeyboardButton($texts->get('close_window'), "", "closeWindow")];
    $keyboard = $telegram->buildInlineKeyBoard($option);
    if ($change) {
        $content = array('chat_id' => $chatID, 'message_id' => $callback_query['message']['message_id'], 'reply_markup' => $keyboard, 'text' => $texts->get('press_x_to_clear'), 'parse_mode' => "HTML");
        $telegram->editMessageText($content);
    } else {
        $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $texts->get('press_x_to_clear'), 'parse_mode' => "HTML");
        $telegram->sendMessage($content);
    }
}

function showClearProducts()
{
    global $user, $texts;
    ShoppingCart::clearShoppingCart($user);
    sendMessage($texts->get('cart_is_cleared'));
    showMainPage();
}

function showCheckoutPage()
{
    global $texts;
    $page = Pages::DELIVERY_TYPE;
    $textToSend = $texts->get('choose_delivery_type');
    $buttons = [$texts->get('pickup'), $texts->get('delivery')];
    showPage($page, $textToSend, $buttons, true);
}

function showFirstNamePage()
{
    global $texts;
    $page = Pages::FIRST_NAME;
    $textToSend = $texts->get('enter_your_name');
    showPage($page, $textToSend, [], true);
}

function showPhoneNumberPage()
{
    global $user, $telegram, $chatID, $texts;
    $user->setPage(Pages::PHONE_NUMBER);
    $option = [
        [$telegram->buildKeyboardButton($texts->get('send_contact'), true, false)],
        [$telegram->buildKeyboardButton($texts->get('back_btn'))],
    ];
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $texts->get('send_your_phone_number'), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showLocationPage()
{
    global $user, $telegram, $chatID, $texts;
    $user->setPage(Pages::LOCATION);
    $option = [
        [$telegram->buildKeyboardButton($texts->get('send_location'), false, true)],
//        [$telegram->buildKeyboardButton($texts->('cant_send_location'))],
        [$telegram->buildKeyboardButton($texts->get('back_btn'))],
    ];
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $texts->get('send_your_location'), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showConfirmOrderPage()
{
    global $user, $telegram, $chatID, $texts, $products;
    $page = Pages::ORDER_CONFIRMATION;

    // start building order text
    $orderText = "<b>" . strtoupper($texts->get($user->getOrderType())) . "</b>";
    $orderText .= "\n-------------\n\n";
    $orderText .= $texts->get('order') . ":\n";

    $overallPrice = 0;

    foreach (ShoppingCart::getUserProducts($chatID) as $productArr) {
        $productCount = $productArr['count'];
        $optionNum = $productArr['optionNum'];
        $product = $products->getProduct($productArr['id']);
        $price = ((int)($product['options'][$optionNum]['price'])) * ((int)($productCount));
        $overallPrice += $price;
        $orderText .= $product['name'] . " " . $product['options'][$optionNum]['name'] . ", " . $productCount . " " . $texts->get('pieces') . " - " . number_format($price, 0, "", " ") . " " . $texts->get('soum') . "\n\n";
    }

    $orderText .= "--------------------\n";
    $orderText .= $texts->get('overall') . " " . number_format($overallPrice, 0, "", " ") . " " . $texts->get('soum');
    $orderText .= "\n\n{$texts->get('name')} " . $user->getFirstName();
    $orderText .= "\n{$texts->get('phone_number')}: " . $user->getPhoneNumber();
    // end building order text
    $user->setOrderText($orderText);
    // save ready order text

    $buttons = [$texts->get('do_order'), $texts->get('cancel')];
    showPage($page, $orderText, $buttons, true);

    if ($user->getLongitude() != "" && $user->getLatitude() != "") {
        $latitude = str_replace(",", ".", $user->getLatitude());
        $longitude = str_replace(",", ".", $user->getLongitude());
        $content = array('chat_id' => $chatID, 'latitude' => $latitude, 'longitude' => $longitude);
        $telegram->sendLocation($content);
    }
}

function showOrderConfirmed()
{
    global $user, $telegram, $chatID, $texts, $ADMINS_CHAT_IDS;

    $content = array('chat_id' => $chatID, 'text' => $texts->get('order_confirmed'), 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
    showMainPage();

    foreach ($ADMINS_CHAT_IDS as $admin_chat_id) {
        $content = array('chat_id' => $admin_chat_id, 'text' => $texts->get('new_order'), 'parse_mode' => "HTML");
        $telegram->sendMessage($content);
        $content = array('chat_id' => $admin_chat_id, 'text' => $user->getOrderText(), 'parse_mode' => "HTML");
        $telegram->sendMessage($content);
        if ($user->getLongitude() != "" && $user->getLatitude() != "") {
            $latitude = str_replace(",", ".", $user->getLatitude());
            $longitude = str_replace(",", ".", $user->getLongitude());
            $content = array('chat_id' => $admin_chat_id, 'latitude' => $latitude, 'longitude' => $longitude);
            $telegram->sendLocation($content);
        }
    }
}

function showOrderCanceled()
{
    global $texts;
    sendMessage($texts->get('order_canceled'));
    showMainPage();
}

// end shopping cart sub pages

function showCompanyPhoneNumber()
{
    global $texts;
    $phoneNumber = "+998977141171";
    sendMessage($texts->get('our_phone_number') . "\n" . $phoneNumber);
}

function showAbout()
{
    global $texts;
    sendMessage($texts->get('about_text'));
}

// admin pages

function showAdminPage()
{
    $page = Pages::ADMIN;
    $buttons = ["➕ Tovar qo'shish", "➕ Kategoriya qo'shish", "➖ Tovar o'chirish", "➖ Kategoriya o'chirish",
        "Tovar narxini o'zgartirish"];
    $textToSend = "Admin Panelga xush kelibsiz!";
    showPage($page, $textToSend, $buttons, true);
}

function showAdminChooseProductCategory($page)
{
    global $texts, $categories;
    $buttons = $categories->getAllNames();
    if (!$buttons) {
        sendMessage("Iltimos, avval kategoriya qo'shing.");
    } else {
        $textToSend = $texts->get("page_categories_text");
        showPage($page, $textToSend, $buttons, true);
    }
}

function showAdminSendProductPhoto()
{
    showPage(Pages::ADMIN_PRODUCT_PHOTO, "Iltimos, tovar rasmini yuboring.", [], true);
}

function showAdminSendProductName()
{
    showPage(Pages::ADMIN_PRODUCT_NAME, "Iltimos, tovar nomini yuboring.", [], true);
}

function showAdminSendProductInfo()
{
    showPage(Pages::ADMIN_PRODUCT_INFO, "Iltimos, tovar haqidagi ma'lumotni yuboring.", [], true);
}

function showAdminSendProductOptionsName()
{
    showPage(Pages::ADMIN_PRODUCT_OPTIONS_NAME, "Iltimos, tovar turlarini yuboring.", [], true);
    sendMessage("Tovar turining nomini yuboring.");
}

function showAdminSendProductOptionsPrice()
{
    showPage(Pages::ADMIN_PRODUCT_OPTIONS_PRICE, "Tovar turining narxini yuboring.", [], true);
}

function showAdminSendProductOptionsConfirmOrAddMore()
{
    global $texts;
    $page = Pages::ADMIN_PRODUCT_OPTIONS_CONFIRM_OR_ADD_MORE;
    $buttons = [$texts->get("btn_add_more"), $texts->get('btn_done')];
    $textToSend = "Tur qo'shildi. {$texts->get("btn_add_more")} yoki {$texts->get('btn_done')} tugmasini bosing.";
    showPage($page, $textToSend, $buttons, true);
}

function showAdminAddCategory()
{
    showPage(Pages::ADMIN_ADD_CATEGORY, "Kategoriya nomini kiriting.", [], true);
}

function showAdminChooseProduct($categoryId, $page = Pages::ADMIN_DELETE_PRODUCT_NAME)
{
    global $texts, $products;
    $buttons = $products->getNamesByCategoryId($categoryId);
    if (!$buttons) {
        sendMessage($texts->get('page_products_text_no_product'));
    } else {
        $textToSend = $texts->get("page_products_text");
        showPage($page, $textToSend, $buttons, true);
    }
}

function showAdminSendProductNewPrice($productName)
{
    global $user, $products;

    $productId = $products->getIdByName($productName);
    $user->setProductId($productId);

    sendMessage("Tovar turini tanlang");
    showProduct($products->getProduct($productId), false, "editProduct");
}

// end admin pages

// helper functions

function sendTextWithKeyboard($buttons, $text, $backBtn = false, $allButton = false)
{
    global $telegram, $chatID, $texts;
    $option = [];
    if ($allButton) {
        $option[] = array($telegram->buildKeyboardButton($texts->get('show_all')));
    }
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
        $option[] = [$telegram->buildKeyboardButton($texts->get("back_btn"))];
    }
    $keyboard = $telegram->buildKeyBoard($option, $onetime = false, $resize = true);
    $content = array('chat_id' => $chatID, 'reply_markup' => $keyboard, 'text' => $text, 'parse_mode' => "HTML");
    $telegram->sendMessage($content);
}

function showPage($page, $text, $buttons = [], $backBtn = false, $allButton = false)
{
    global $user;
    $user->setPage($page);
    sendTextWithKeyboard($buttons, $text, $backBtn, $allButton);
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

function sendChooseButtons()
{
    sendMessage("Iltimos, quyidagi tugmalardan birini tanlang.");
}

function logD($text)
{
    global $telegram, $DEVELOPER_CHAT_ID;
    $telegram->sendMessage(['chat_id' => $DEVELOPER_CHAT_ID, 'text' => $text]);
}

function sendJSON() {
    global $data;
    logD(json_encode($data, JSON_PRETTY_PRINT));
}