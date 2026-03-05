<?php

$db = new mysqli('localhost', 'enigmaelaboration', 's9z63q3UN8Kq', 'my_enigmaelaboration');
if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
$db->set_charset('utf8mb4');

$tableUsers             = 'nextPlayShopClientiBot_users';
$tableProducts          = 'nextPlayShopClientiBot_products';
$tableCoupons           = 'nextPlayShopClientiBot_coupons';
$tableUsedCoupons       = 'nextPlayShopClientiBot_usedCoupons';
$tableCarts             = 'nextPlayShopClientiBot_carts';
$tableOrders            = 'nextPlayShopClientiBot_orders';
$tableClientsEmby       = 'nextPlayShopClientiBot_clientsEmby';
$tableClientsPlex       = 'nextPlayShopClientiBot_clientsPlex';