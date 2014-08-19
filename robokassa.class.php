<?php
namespace Robokassa;
/**
 * Documentation:
 * http://robokassa.ru/ru/Doc/Ru/Interface.aspx -- «ROBOKASSA. Описание интерфейсов»
 * http://robokassa.ru/ru/DocTest/Ru/Interface.aspx -- «ROBOKASSA. Использование тестового сервера»
 */
class Robokassa
{
    //
    // Consts
    const ENDPOINT_SANBOX     = 'http://test.robokassa.ru/Index.aspx';
    const ENDPOINT_PRODUCTION = 'https://auth.robokassa.ru/Merchant/Index.aspx';

    private $requestParameters = [
        //
        // float - требуемая к получению сумма (обязательный параметр).
        // Формат представления числа - разделитель точка.
        // Сумма должна быть указана в той валюте, которая была указана при регистрации магазина,
        // как валюта текущего баланса Продавца или как электронная валюта, в которой будет получать средства Продавец.
        'OutSum'       => 0,
        //
        // string - Email пользователя. Пользователь может изменить его в процессе оплаты.
        'Email'        => false,
        //
        // integer - Идентификатор транзакции в приложении (в магазине). Должен быть уникальным для магазина.
        // Может принимать значения от 1 до 2147483647 (2^31-1).
        // Если содержит пустое значение, вовсе не указан, либо равен "0", то при создании операции ей будет автоматически присвоен уникальный номер счета.
        // Рекомендуется использовать данную возможность только в очень простых магазинах, где не требуется какого-либо контроля.
        'InvId'        => 0,
        //
        // string - Описание покупки.
        // можно использовать только символы английского или русского алфавита, цифры и знаки препинания.
        // Максимальная длина 100 символов.
        'InvDesc'      => '',
        //
        // string - Предлагаемая валюта платежа.
        // Пользователь может изменить ее в процессе оплаты.
        // Доступные значения для параметра IncCurrLabel - метки валют.
        // Cпособ получения этой информации описан в разделе: XML интерфейсы. Интерфейс получения списка валют.
        // Однако он доступен только активным мерчантам (продавцам).
        'IncCurrLabel' => '',
        //
        // string - [опционально] Язык общения с клиентом. Значения: en, ru.
        // Если не установлен - берется язык региональных установок браузера.
        'Culture'      => '',
        //
        // string - кодировка, в которой возвращается HTML-код кассы. По умолчанию: windows-1251.
        'Encoding'     => 'utf-8',
        //
        // string[] - Коллекция дополнительных параметров приложения (магазина),
        // необрабатываемых сервисов ROBOKASSA, но пересылаемых на этап верификации платежа.
        'CustomValues' => [
        ]
    ];
    private $login; // логин мерчанта (продавца)
    private $password1; // пароль №1 - sMerchantPass1, используется интерфейсом инициализации оплаты
    private $password2; // пароль №2 - sMerchantPass2, для получения информации о состояниях платежей, для XML-интерфейсов
    private $endpoint = '';

    /**
     * @param string  $login
     * @param string  $pass1
     * @param string  $pass2
     * @param boolean $isTest работа с тестовым сервером
     *
     * @return none
     */
    public function __construct($login, $pass1, $pass2, $isTest = false)
    {
        $this->login     = $login;
        $this->password1 = $pass1;
        $this->password2 = $pass2;

        $this->endpoint = $isTest ? $this::ENDPOINT_SANBOX : $this::ENDPOINT_PRODUCTION;
    }

    /**
     * Request and process ROBOKASSA payment transaction
     */
    public function processPayment($requestParametersCollection)
    {
        $this->setRequestParameters($requestParametersCollection);
        //
        // Редирект на сайт РОБОКАССЫ с передачей параметров транзакции
        return $this->getRedirectURL();
    }

    /**
     *
     */
    private function setRequestParameters($requestParametersCollection)
    {
        $this->requestParameters            = array_merge($this->requestParameters, $requestParametersCollection);
        $this->requestParameters['InvDesc'] = substr(
            preg_replace('/[^a-z0-9,.!]i/', '-',
                \Invntrm\transliterateCyr(
                    $this->requestParameters['InvDesc']
                )
            ), 0, 100);
    }

    /**
     * Check transaction result
     * @return boolean - is transaction success
     */
    public function getPaymentResult()
    {
        $this->convertPaymentResultParameters();
        if ($this->checkHash($this->requestParameters['SignatureValue'])) {
            return $this->requestParameters;
        } else {
            throw new ExceptionExtended("Transaction is invalid");
        }
    }

    /**
     *
     */
    private function convertPaymentResultParameters()
    {
        $this->requestParameters = $this::convertPaymentResultParametersStatic($this->requestParameters);
    }

    /**
     *
     */
    public static function convertPaymentResultParametersStatic($requestParameters)
    {
        foreach ($_POST as $key => $value) {
            if (preg_match('!^SHP!i', $key)) {
                $requestParameters['CustomValues'][preg_replace('!^SHP!i', '', $key)] = $value;
            } else {
                $requestParameters[$key] = $value;
            }
        }
        return $requestParameters;
    }

    /**
     * Проверить исполнение операции. Сравнить контрольные суммы
     *
     * @param string $hash значение SignatureValue, переданное кассой на Result URL
     *                     md5-хеш строки вида «sMerchantLogin:nOutSum:nInvId:sMerchantPass1»
     *
     * @return boolean $hashValid
     */
    private function checkHash($hash)
    {
        $customVars    = $this->getSerialezedCustomValues();
        $hashGenerated = md5("{$this->requestParameters['OutSum']}:{$this->requestParameters['InvId']}:{$this->password2}:{$customVars}");

        \Invntrm\_d([
            'checkHash',
            "{$this->requestParameters['OutSum']}:{$this->requestParameters['InvId']}:{$this->password2}:{$customVars}",
            $hashGenerated,
            strtolower($hash)
        ]);
        return (strtolower($hash) == $hashGenerated);
    }

    /**
     * Вернуть сериализованную строку с пользовательскими данными
     *
     * @return string
     */
    private function getSerialezedCustomValues()
    {
        return join(':',
            \Invntrm\true_sort(\Invntrm\true_array_map(
                function ($val, $key) { return "$key=$val"; },
                $this->getRkSpecificCustomValues()
            ))
        );
    }

    /**
     * Вернуть коллекцию дополнительных параметров приложения (магазина) в специфичной для РОБОКАССЫ форме.
     */
    private function getRkSpecificCustomValues()
    {
        $customVars = [];
        foreach ($this->requestParameters['CustomValues'] as $k => $v) {
            $customVars['SHP' . $k] = html_entity_decode($v);
        }
        return $customVars;
    }

    /**
     * Вернуть URL для запроса транзакции
     *
     * @return string $url
     */
    private function getRedirectURL()
    {
        $this->requestParameters['OutSum'] = (float)$this->requestParameters['OutSum'];
        $customVars = $this->getSerialezedCustomValues();
        $hash       = md5("{$this->login}:{$this->requestParameters['OutSum']}:{$this->requestParameters['InvId']}:{$this->password1}:{$customVars}");
        $httpQuery  = array_merge(
            [
                'MrchLogin'      => $this->login,
                'SignatureValue' => $hash,
            ],
            $this->requestParameters,
            $this->getRkSpecificCustomValues()

        );
        unset($httpQuery['CustomValues']);
        return $this->endpoint . '?' . http_build_query($httpQuery);
    }
}

    class ExceptionExtended extends \Exception {
        protected $codeExtended;

        /**
         * @return string
         */
        public function getCodeExtended()
        {
            return $this->codeExtended;
        }

        /**
         * @param string     $codeExtended
         * @param string     $description
         * @param \Exception $previous    [optional]
         * @param \Exception $numericCode [optional]
         */
        public function __construct($codeExtended, $description=null, $previous=null, $numericCode=null)
        {
            if(!$description) $description = 'нет описания';
            parent::__construct($description, $numericCode, $previous);
            $this->codeExtended = \Invntrm\makeErrorCode($codeExtended);
        }
    }

    /**
     * Class ExceptionUser User not correct action exception
     * @package YandexMoney
     */
    class ExceptionUser extends ExceptionExtended {

    }

    /**
     * Class ExceptionYandexmoney Yandex Money specified exception
     * @package YandexMoney
     */
    class ExceptionRobokassa extends ExceptionExtended {

    }

    /**
     * Class ExceptionApp Application bug exception
     * @package YandexMoney
     */
    class ExceptionApp extends ExceptionExtended {

    }
