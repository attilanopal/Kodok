<?php
require __DIR__ . '/../vendor/autoload.php';
 
 
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;
 
 
use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\AudioMessageBuilder;
use \LINE\LINEBot\MessageBuilder\ImageMessageBuilder;
use \LINE\LINEBot\MessageBuilder\VideoMessageBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;
 
 
$pass_signature = true;

// Variabel buatan Atl Ngokhey
$autoReplyStatus = '0ff';
$prefix='!';
 
// set LINE channel_access_token and channel_secret
$channel_access_token = "B9h0QfSxK3g6cDGBE9YnTKPqF3N31qP2f+crDf5Qi4UzUnoQzkUYP4qArMseGRxSkv/tep7B78zqLeitmUz3KvsSJNQMRa3QjLUsaBmjqAv9bf0nSd39KWrDAEqOxEiqr5n/mwOu6x2fF5jAYk+RqAdB04t89/1O/w1cDnyilFU=";
$channel_secret = "48dbd9a6612ec9c6deb8ce1fdca9b115";
 
 
// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
 
 
 
 
$app = AppFactory::create();
$app->setBasePath("/public");
 
 
 
 
$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});
 
 
// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) {
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
 
 
    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);
 
 
    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }
 
 
        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }
 
 
    $data = json_decode($body, true);
    if(is_array($data['events'])){
        foreach ($data['events'] as $event)
        {
            if ($event['type'] == 'message')
            {
                if($event['message']['type'] == 'text')
                {
                    // send same message as reply to user
                    // $result = $bot->replyText($event['replyToken'],'ini user Id kamu : '.$event['source']['userId']);
                    $multiMessageBuilder = new MultiMessageBuilder();
                    if($event['message']['text']=='!autoReply'){
                        $autoReplyStatusMessage = new TextMessageBuilder('Auto Reply : '.$autoReplyStatus);

                    }
 
 
                    if($event['message']['text'] == '!myUserId'){
                    // or we can use replyMessage() instead to send reply message
                    
                    $stickerMessageBuilder= new StickerMessageBuilder(1,117);
                    $textMessageBuilder = new TextMessageBuilder('Id kamu : ');
                    $textMessageUserId = new TextMessageBuilder($event['source']['userId']);
                    $multiMessageBuilder->add($textMessageBuilder);
                    $multiMessageBuilder->add($textMessageUserId);
                    }
                    $result = $bot->replyMessage($event['replyToken'], $multiMessageBuilder);
 
 
 
                    
                    $response->getBody()->write(json_encode($result->getJSONDecodedBody()));
                    return $response
                        ->withHeader('Content-Type', 'application/json')
                        ->withStatus($result->getHTTPStatus());
                }
            }
        }
        return $response->withStatus(200, 'for Webhook!'); //buat ngasih response 200 ke pas verify webhook
    }
    return $response->withStatus(400, 'No event sent!');
});
// $app->get('/pushmessage', function ($req, $response) use ($bot) {
//     // send push message to user
//     $userId = 'Ue92a5b9df7e195607bddab3f3fa8f336';
//     $textMessageBuilder = new TextMessageBuilder('Halo, ini pesan push');
//     $result = $bot->pushMessage($userId, $textMessageBuilder);
 
//     $response->getBody()->write("Pesan push berhasil dikirim!");
//     return $response
//         ->withHeader('Content-Type', 'application/json')
//         ->withStatus($result->getHTTPStatus());
// });
$app->run();