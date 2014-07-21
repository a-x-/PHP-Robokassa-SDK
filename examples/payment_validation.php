<?php
/**
 * file Простой пример проверки оплаты.
 * Указывается в ResultURL, SuccessURL и в FailURL с get-параметром 'target' равным 'result', 'success' или 'fail'.
 */

const IS_DEBUG_MODE = true;

//
// Получение результата транзакции
// и выполнение приложением (магазином) сценариев завершения.
//
// Робокасса имеет странную модель подтверждения транзакции:
//
//
try {
    $requestParameters = (new Robokassa('merchant_login', 'pass1', 'pass2', IS_DEBUG_MODE))->getPaymentResult();
    if ($_REQUEST['target'] === 'result') {
        //
        // Process success payment with $requestParameters parameters.
        // $requestParameters =
        //   [OutSum=>'...', 'InvId'=>'...', 'SignatureValue'=>'...','CustomValues' => [...]]
        //
        // Save InvId in the DB
        //
        //
    } elseif ($_REQUEST['target'] === 'success') {
        //
        // Show result message for user
        // $requestParameters =
        //   [OutSum=>'...', 'InvId'=>'...', 'SignatureValue'=>'...','CustomValues' => [...]]
        //
        // Check InvId in the DB
        // If exest then echo 'Payment is success!';
        // else echo 'Payment is fail!';
        //
    } elseif ($_REQUEST['target'] === 'fail') {
        //
        // Show result message for user
        // $requestParameters =
        //   [OutSum=>'...', 'InvId'=>'...', 'CustomValues' => [...]]
        //
        die('Payment is reject!');
    }

}
 catch (\Exception $e) {
    die('Payment is fail!');
}
