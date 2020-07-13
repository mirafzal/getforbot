<?phprequire_once("database/db_connect.php");class User{    /**     * @var integer     */    public $chatID;    function __construct($chatID)    {        $this->chatID = $chatID;        if (!$this->isUserSet()) $this->makeUser();        if ($this->getKeyValue('lang') == "") $this->setLanguage('uz');        if ($this->getKeyValue('quantity') == "") $this->setQuantity(0);    }    function setPage($page)    {        return $this->setKeyValue('page', $page);    }    function getPage()    {        return $this->getKeyValue('page');    }    function setCategoryId($category)    {        $this->setKeyValue('categoryId', $category);    }    function getCategoryId()    {        return $this->getKeyValue('categoryId');    }    function setProduct($product)    {        $this->setKeyValue('product', json_encode($product, JSON_UNESCAPED_UNICODE));    }    function getProduct()    {        return json_decode($this->getKeyValue('product'), true);    }    function setOrderText($data)    {        $this->setKeyValue('orderText', $data);    }    function getOrderText()    {        return $this->getKeyValue('orderText');    }    function setLatitude($data)    {        $this->setKeyValue('latitude', $data);    }    function getLatitude()    {        return $this->getKeyValue('latitude');    }    function setLongitude($data)    {        $this->setKeyValue('longitude', $data);    }    function getLongitude()    {        return $this->getKeyValue('longitude');    }    function setProductId($productId)    {        $this->setKeyValue('productId', $productId);    }    function getProductId()    {        return $this->getKeyValue('productId');    }    function setProductOption($productOption)    {        $this->setKeyValue('productOption', $productOption);    }    function getProductOption()    {        return $this->getKeyValue('productOption');    }    function setOrder($order)    {        $this->setKeyValue('order', $order);    }    function getOrder()    {        return $this->getKeyValue('order');    }    function setQuantity($quantity)    {        $this->setKeyValue('quantity', $quantity);    }    function getQuantity()    {        return $this->getKeyValue('quantity');    }    function setOrderType($orderType)    {        $this->setKeyValue('orderType', $orderType);    }    function getOrderType()    {        return $this->getKeyValue('orderType');    }    function setPhoneNumber($phoneNumber)    {        $this->setKeyValue('phoneNumber', $phoneNumber);    }    function getPhoneNumber()    {        return $this->getKeyValue('phoneNumber');    }    function setLanguage($lang)    {        $this->setKeyValue('lang', $lang);    }    function getLanguage()    {        return $this->getKeyValue('lang');    }    function setFirstName($data)    {        $this->setKeyValue('firstName', $data);    }    function getFirstName()    {        return $this->getKeyValue('firstName');    }    private function makeUser()    {        global $db;        $chatID = $db->real_escape_string($this->chatID);        $query = "insert into `users`(chatID) values('{$chatID}')";        if (!$db->query($query))            die("пользователя создать не удалось");    }    private function isUserSet()    {        global $db;        $chatID = $db->real_escape_string($this->chatID);        $result = $db->query("select * from users where chatID='$chatID' LIMIT 1");        $myArray = (array)($result->fetch_array());        if (!empty($myArray)) return true;        return false;    }    private function getData()    {        global $db;        $res = array();        $chatID = $db->real_escape_string($this->chatID);        $result = $db->query("select * from `users` where chatID='$chatID'");        $arr = $result->fetch_assoc();        if (isset($arr['data_json'])) {            $res = json_decode($arr['data_json'], true);        }        return $res;    }    private function setKeyValue($key, $value)    {        global $db;        $chatID = $db->real_escape_string($this->chatID);        $value = base64_encode($value);        if (!$this->isUserSet()) $this->makeUser();        $data = $this->getData();        $data[$key] = $value;        $data = json_encode($data, JSON_UNESCAPED_UNICODE);        return $db->query("update `users` SET data_json = '{$data}' WHERE chatID = '{$chatID}'");    }    private function getKeyValue($key)    {        $data = $this->getData();        if (array_key_exists($key, $data)) {            return base64_decode($data[$key]);        }        return "";    }}