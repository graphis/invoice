<?php
use Radar\Adr\Boot;
use Relay\Middleware\ExceptionHandler;
use Relay\Middleware\ResponseSender;
use Zend\Diactoros\Response as Response;
use Zend\Diactoros\ServerRequestFactory as ServerRequestFactory;

$filename = __DIR__.preg_replace('#(\?.*)$#', '', $_SERVER['REQUEST_URI']);
if (php_sapi_name() === 'cli-server' && is_file($filename)) {
    return false;
}

/**
 * Bootstrapping
 */
require '../vendor/autoload.php';

$boot = new Boot();
$adr = $boot->adr([
    'Cadre\Core\Config',
    'Invoice\Config',
]);

/**
 * Middleware
 */
$adr->middle(new ResponseSender());
$adr->middle(new ExceptionHandler(new Response()));
$adr->middle('Radar\Adr\Handler\RoutingHandler');
$adr->middle('Radar\Adr\Handler\ActionHandler');

/**
 * Responder
 */
$adr->responder('Cadre\Core\Responder\TwigResponder');

/**
 * Routes
 */
$adr->get('ListAllInvoices', '/', 'Invoice\Domain\Action\ListAllInvoices')
    ->defaults(['_view' => '/app/views/index.twig.html']);
$adr->get('ViewSingleInvoice', '/{number}', 'Invoice\Domain\Action\ViewSingleInvoice')
    ->defaults(['_view' => '/app/views/invoice.twig.html']);

/**
 * Run
 */
$adr->run(ServerRequestFactory::fromGlobals(), new Response());
