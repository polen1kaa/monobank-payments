<?php
class MonobankPayments{
    protected string $token;
    protected string $jar;
    protected string $webhook;
    public function __construct(Array $param){
        $this->token = $param["token"];
        $this->jar = $param["jar"];
        $this->webhook = $param["webhook"];
    }
    private function answer(Int $status, String $text){
        return [
            "code" => $status,
            "data" => $text
        ];
    }
    public function createLink($param){
        if(!filter_var($param["amount"], FILTER_VALIDATE_FLOAT) or $param["amount"] < 10 or $param["amount"] > 29999){
            return $this->answer(400, "The payment amount cannot be less than 10 and cannot be more than 29999.");
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://api.monobank.ua/personal/webhook');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
            "webHookUrl" => $this->webhook
        ]));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-Token: '.$this->token
        ]);
        $answer = curl_exec($ch);
        $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if($httpcode !== 200){
            return $this->answer(400, "An error occurred while generating the payment link: ".$answer);
        }
        return $this->answer(200, $this->jar."/?a=".$param["amount"]."&t=".$param["label"]);
    }
}
?>