// Excuse my beginner's English


This SDK is not implement full set of «ROBOKASSA» API methods, but make basic operations simpliest:
* Request and process (init) payment.
* Get payment result.
 
**Status**: *Alpha*. But, i use it in my own projects. So, lib, probably, has unexpected my own narrow dependencies.
If you want use it, then call me.


# Public interface
Check using examples in the examples/ directory. 
Code has comments.

```php
/**
 * Request and process «ROBOKASSA» payment transaction
 */
public function processPayment($requestParametersCollection)
```


```php
/**
 * Check transaction result
 * @return boolean - is transaction success
 */
public function getPaymentResult()
```


`$requestParametersCollection` Structure:

```php
[
    //
    // float - требуемая к получению сумма (обязательный параметр).
    // Формат представления числа - разделитель точка.
    // Сумма должна быть указана в той валюте, которая была указана при регистрации магазина,
    // как валюта текущего баланса Продавца или как электронная валюта, в которой будет получать средства Продавец.
    'OutSum' => 0,
    //
    // string - [опционально] Email пользователя. Пользователь может изменить его в процессе оплаты.
    'Email' => false,
    //
    // integer - [опционально] Идентификатор транзакции в приложении (в магазине). Должен быть уникальным для магазина.
    // Может принимать значения от 1 до 2147483647 (2^31-1).
    // Если содержит пустое значение, вовсе не указан, либо равен "0", то при создании операции ей будет автоматически присвоен уникальный номер счета.
    // Рекомендуется использовать данную возможность только в очень простых магазинах, где не требуется какого-либо контроля.
    'InvId' => 0,
    //
    // string - Описание покупки.
    // можно использовать только символы английского или русского алфавита, цифры и знаки препинания.
    // Максимальная длина 100 символов.
    // Примерное регулярное выражение: /[a-zа-яё0-9,.?!-]{,100}/i.
    'InvDesc' => '',
    //
    // string - [опционально] Предлагаемая «валюта» платежа 
    // (на самом деле не валюта, а код, включающий в себя указание конечного провайдера платежа и валюту,
    //  если провайдер принимает отличные от рубля валюты). 
    // Пользователь может изменить ее в процессе оплаты.
    // Способ получения информации о доступный «валютах» описан в разделе: XML интерфейсы. Интерфейс получения списка валют.
    // Однако он доступен только активным мерчантам (продавцам).
    'IncCurrLabel' => '',
    //
    // string - [опционально] Язык общения с клиентом. Значения: en, ru.
    // Если не установлен - берется язык региональных установок браузера.
    'Culture' => '',
    //
    // string - [опционально] кодировка, в которой возвращается HTML-код кассы. По умолчанию: windows-1251.
    'Encoding' => 'utf-8',
    //
    // string[] - [опционально] Коллекция дополнительных параметров приложения (магазина),
    // необрабатываемых сервисов ROBOKASSA, но пересылаемых на этап верификации платежа.
    'CustomValues' => [
    ]
];
```

# License

I'm not sure, that this SDK are interesting for you, but this are open and licensed under the MIT.

# Contribute

You're welcome!

English typos corrections are welcome too.
