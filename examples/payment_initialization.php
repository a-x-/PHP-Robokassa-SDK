<?php

/* ===================================
 * Author: Nazarkin Roman
 * -----------------------------------
 * Contacts:
 * email - roman@nazarkin.su
 * icq - 642971062
 * skype - roman444ik
 * -----------------------------------
 * GitHub:
 * https://github.com/NazarkinRoman
 * ===================================
 */

/* простой пример инициализации оплаты */
$kassa = new Robokassa('merchant_login', 'pass1', 'pass2');

/* назначение параметров */
$kassa->OutSum       = 500;
$kassa->IncCurrLabel = 'WMRM';
$kassa->Desc         = 'Тестовая оплата';

$kassa->addCustomValues([
        'user'     => $userId, // все ключи массива должны быть с префиксом shp_
        'someData' => 'someValue'
    ]);

/* редирект на сайт робокассы */
header('Location: '.$kassa->getRedirectURL());
