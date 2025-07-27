<?php

require_once '../bootstrap.php';

use app\classes\GerenciadorRecurso;
use Slim\Factory\AppFactory;
use Slim\Middleware\BodyParsingMiddleware;
use app\middlewares\ErrorHandlerMiddleware;
use app\middlewares\SanitizacaoDadosMiddleware;

$app = AppFactory::create();

$app->add(new SanitizacaoDadosMiddleware());
$app->add(new BodyParsingMiddleware());

$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setDefaultErrorHandler(new ErrorHandlerMiddleware());

$gerenciadorRecurso = new GerenciadorRecurso($app);

$rotas = glob('../rotas/*.php');
foreach ($rotas as $rota) {
    require_once $rota;
}

$app->run();