<?php

// web/index.php
use Freshdesk\Config\Connection,
    Freshdesk\Ticket,
    Freshdesk\Model\Ticket as TicketM;
use Symfony\Component\HttpFoundation\JsonResponse;

require_once __DIR__.'/../vendor/autoload.php';

$app = new Silex\Application();
$app->register(new Igorw\Silex\ConfigServiceProvider(__DIR__ . '/../config/config.php'));

/**
 * Merge webhook data into templated messages
 * @param $message
 * @param $data
 * @return string
 */
function buildMessage($message, $data) {
    $params = [
        'conversionMessages' => $data['conversionMessages'],
        'visitor_fullName' => $data['visitor']['fullName'],
        'visitor_city' => $data['visitor']['city'],
        'visitor_region' => $data['visitor']['region'],
        'visitor_countryCode' => $data['visitor']['countryCode'],
        'visitor_conversationBeginPage' => isset($data['visitor']['conversationBeginPage'])
            ? $data['visitor']['conversationBeginPage'] : '',
        'visitor_phoneNumber' => isset($data['visitor']['phoneNumber'])
            ? $data['visitor']['phoneNumber'] : '',
    ];

    $search = [];
    $replace = [];

    foreach ($params as $key => $value) {
        $search[] = '{{' . $key . '}}';
        $replace[] = $value;
    }

    return str_replace($search, $replace, $message);
}

$app->post('/', function () use ($app) {
    return 'Setup olark webhook to this url';
});

$app->post('/', function () use ($app) {
    // Handle Olark webhook
    $content = file_get_contents("php://input");
    $data = json_decode($content, true);

    // Stop if visitor email not found
    if (!isset($data['visitor']['emailAddress'])) {
        return new JsonResponse([
            'success' => false,
            'error' => 'Visitor email is required',
        ]);
    }

    // Build conversion_messages
    $conversionMessages = '';
    $offline = false;

    // If visitor full name not defined
    if (!isset($data['visitor']['fullName'])) {
        $data['visitor']['fullName'] = 'Visitor';
    }

    foreach ($data['items'] as $item) {
        if ($item['kind'] == 'OfflineMessage') {
            $offline = true;
        }

        // If nickname not provided
        if (!isset($item['nickname'])) {
            // If this is message to visitor or message to operator
            if (in_array($item['kind'], ['MessageToOperator', 'OfflineMessage'])) {
                // ~> Nick name is visitor full name
                $item['nickname'] = $data['visitor']['fullName'];
            } else {
                // Else
                // ~> Nick name is operator (Operator always has nick name)
                $item['nickname'] = 'Operator';
            }
        }

        $conversionMessages .= $item['nickname'] . ": " . $item['body'] . PHP_EOL;
    }
    $data['conversionMessages'] = $conversionMessages;

    // If only offline message and this is not offline
    if ($app['offline_only'] && !$offline) {
        // ~> return false
        return new JsonResponse([
            'success' => false,
            'error' => 'Not an offline message',
        ]);
    }

    $url = 'https://' . $app['freshdesk_api_key'] . ':X@' . $app['freshdesk_subdomain'] . '.freshdesk.com';
    $conf = new Connection($url);

    $t = new Ticket($conf);
    //create new ticket
    $model = new TicketM(
        array(
            'subject'       => buildMessage($app['ticket_subject'], $data),
            'description'   => buildMessage($app['ticket_description'], $data),
            'email'         => $data['visitor']['emailAddress'],
        )
    );
    //create new ticket, basic example
    $result = $t->createNewTicket($model);

    return new JsonResponse([
        'success' => ($result != false),
    ]);
});

$app->run();