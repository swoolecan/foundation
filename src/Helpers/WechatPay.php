<?php

namespace Swoolecan\Foundation\Helpers;



use EasyWeChat\Factory;

class WechatPay
{
    public  $config = [];

    public function __construct($config) {
        $pay = config('pay.wechat');

        $config = [
            'app_id' => $pay['appid'],
            'mch_id' => $pay['mch_id'],
            'key_path' => $pay['cert_key'],
            'cert_path' => $pay['cert_client'],
            'key' => $pay['key'],
        ];
        $this->config = $config;
    }

    public function getApplication() {
        return  Factory::payment($this->config);
    }

    //付款
    public function toBalance() {
        $app = $this->getApplication();
        $partnerTradeNo = '1676012621ABCDD';
        $refundOrder =  [
            'openid' => 'oYtIj6uZfLz97-zcYUtW5CaB5nJI',
            'check_name' => 'NO_CHECK',
            'amount' => 1,
            'desc' => '理赔',
            'partner_trade_no' => $partnerTradeNo,
        ];
        return $app->transfer->toBalance($refundOrder);
    }


    public function queryBalanceOrder () {
        $app = $this->getApplication();
        $partnerTradeNo = '1676012621ABCDD';
        return $app->transfer->queryBalanceOrder($partnerTradeNo);
    }
}
