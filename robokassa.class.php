<?php

class Robokassa {
    //
    //request parameters
    public $OutSum
    public $Email = false
    public $InvId = 0
    public $Desc
    public $IncCurrLabel = ''
    public $Culture      = 'ru';

    //
    // Consts
    const ENDPOINT_PRODUCTION = 'https://merchant.roboxchange.com/Index.aspx';
    const ENDPOINT_SANBOX     = 'http://test.robokassa.ru/Index.aspx';

    private $login;
    private $password1;
    private $password2;
    private $endpoint   = '';
    private $customVars = [];

    /**
	 * Вносит в класс данные для генерации защищенной подписи
	 *
	 * @param string $login логин мерчанта
	 * @param string $pass1 пароль №1
	 * @param string $pass2 пароль №2
	 * @param boolean $test работа с тестовым сервером
	 *
	 * @return none
	 */
    public function __construct($login, $pass1, $pass2, $test = false) {
        $this->login     = $login;
        $this->password1 = $pass1;
        $this->password2 = $pass2;

        $this->endpoint = $test?$this::ENDPOINT_SANBOX:$this::ENDPOINT_PRODUCTION;
    }

    /**
	 * Добавление пользовательских значений в запрос
	 *
	 * @param array $vars именованный массив с переменными(названия указывать с суффиксом shp_)
	 * @return none
	 */
    public function addCustomValues($vars) {
        if (!is_array($vars)) {
            throw new Exception('Function `addCustomValues` take only array\'s');
        }

        foreach ($vars as $k => $v) {
            $this->customVars['shp_'.$k] = $v;
        }

    }

    /**
	 * Получение URL для запроса
	 *
	 * @return string $url
	 */
    public function getRedirectURL() {
        $customVars = $this->getCustomValues();
        $hash       = md5("{$this->login}:{$this->OutSum}:{$this->InvId}:{$this->password1}{$customVars}");
        $httpQuery  = [
            'MrchLogin'      => $this->login,
            'OutSum'         => (float) $this->OutSum,
            'Desc'           => urlencode($this->Desc),
            'SignatureValue' => $hash,
            'Culture'        => $this->Culture
        ];
        if ($this->InvId !== '') {$httpQuery['InvId']               = $this->InvId;}
        if ($this->IncCurrLabel !== '') {$httpQuery['IncCurrLabel'] = $this->IncCurrLabel;}
        if ($this->Email !== '') {$httpQuery['Email']               = $this->Email;}

        return $this->endpoint.'?'.http_build_query($httpQuery).$this->getCustomValues($url = true);
    }

    /**
	 * Проверка исполнения операции. Сравнение хеша
	 *
	 * @param string $hash значение SignatureValue, переданное кассой на Result URL
	 * @param boolean $checkSuccess проверка параметров в скрипте завершения операции (SuccessURL)
	 * @return boolean $hashValid
	 */
    function checkHash($hash, $checkSuccess = false) {
        $customVars    = $this->getCustomValues();
        $password      = $checkSuccess?$this->password1:$this->password2;
        $hashGenerated = md5("{$this->OutSum}:{$this->InvId}:{$password}{$customVars}");

        return (strtolower($hash) == $hashGenerated);
    }

    /**
	 * Получение строки с пользовательскими данными для шифрования
	 *
	 * @param boolean $isUrl генерация строки для использования в URL true/false
	 * @return string
	 */
    function getCustomValues($isUrl = false) {
        $out        = '';
        $customVars = [];
        if (!empty($this->customVars)) {
            foreach ($this->customVars as $k => $v)
            $customVars[$k] = $k.'='.$v;

            sort($customVars);

            if ($isUrl === true) {
                $out = '&'.join('&', $customVars);
            } else {

                $out = ':'.join(':', $customVars);
            }
        }

        return $out;
    }

}
