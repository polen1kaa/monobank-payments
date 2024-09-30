
# Monobank Payments

Accepting payments via Monobank jar


## Features

- Payment link generation



## Installation

Copy the monobank.php file to the directory with your project

```bash
  git clone https://github.com/polen1kaa/monobank-payments
  cd monobank-payments
  mv monobank.php /var/www/html
```

[Enable HTTP notifications](https://yoomoney.ru/transfer/myservices/http-notification) from YooMoney and generate a secret word for Webhook
[Get an X-Token](https://api.monobank.ua/) to work with the API and [create any jar in the Monobank app](https://www.prostobank.ua/depozity/novosti/banka_ot_monobank).
## Usage

### Creating a payment link
```php
<?php
include("monobank.php");
$monobank = new MonobankPayments(
  "token" => "your-x-token", # Your Monobank X-Token
  "jar" => "https://send.monobank.ua/jar/...", # Link to your jar.
  "webhook" => "https://site.site/monobank-handler" # Link to your webhook handler
);

/*
PLEASE SAVE THE PAYMENT AMOUNT AND ITS IDENTIFIER TO THE DATABASE.
It is difficult to implement a system of hashes and their verification, so we will check notifications directly due to pre-recorded data.
*/

$amount = 100; # Required: The amount of payment in UAH (10 to 29999).
$label = 123456; # Required: Payment ID or any other value you have for recognizing a specific payment on your site

$answer = $monobank->createLink([
  "amount" => $amount,
  "label" => $label
]);
if($answer["code"] == 200){
  echo $answer["data"] # https://send.monobank.ua/jar/...?a=100&t=123456
}else{
  echo json_encode($answer); # {"code": 400, "data": "Wow, is this a mistake?"}
}
?>
```

### Webhook handler example
Webhook server is set automatically when the payment link is generated. Please note that Monobank periodically checks whether your server is responding. If the http code is not 200, notifications may stop coming.

It is difficult to implement a system of hash and its verification. Therefore I suggest you to write down the amount of payment and its ID in advance to check it.
```php
<?php
# Needed for webhook notifications to work properly.
if($_SERVER["REQUEST_METHOD"] === "GET"){
    die("OK");
}else if($_SERVER["REQUEST_METHOD"] === "POST"){
    $request = json_decode(file_get_contents('php://input'), true);
    # Get the previously recorded data from the database.
    $amount = 100;
    $label = 123456;

    /*
    Checking the availability of payment on your website.
    In my case, the usual if a == b.
    In yours, most likely, getting strings from MySQL and checking their count.
    */

    if($label == $request["data"]["statementItem"]["comment"]){
        if($amount == $_REQUEST['data']['statementItem']["amount"] OR $payment["data"] == $_REQUEST['data']['statementItem']["amount"]){

            /*
            Here you can process a successful payment. For example, mark the purchase as paid.
            Don't forget to return the 200 code :)
            */

            echo "Done";

        }
    }
}
?>
```
