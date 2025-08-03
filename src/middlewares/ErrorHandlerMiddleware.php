<?php

namespace app\middlewares;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Slim\Interfaces\ErrorHandlerInterface;
use Throwable;
use Slim\Psr7\Response;
use app\classes\http\RespostaHttp;
use app\classes\http\HttpStatusCode;
use Slim\Exception\HttpNotFoundException;
use Slim\Exception\HttpForbiddenException;
use Slim\Exception\HttpBadRequestException;
use Slim\Exception\HttpUnauthorizedException;
use Slim\Exception\HttpMethodNotAllowedException;

class ErrorHandlerMiddleware implements ErrorHandlerInterface {
    public function __invoke(ServerRequestInterface $request, Throwable $e, bool $displayErrorDetails, bool $logErrors,
        bool $logErrorDetails): ResponseInterface {
        [$mensagem, $status] = $this->obterDadosErroDeAcordoComExceptionSlim($e);

        return RespostaHttp::enviarResposta(new Response(), $status, [
            'message' => $mensagem
        ]);
    }

    private function obterDadosErroDeAcordoComExceptionSlim(Throwable $e) {
        $mensagem = 'Houve um erro interno.';
        $status = HttpStatusCode::INTERNAL_SERVER_ERROR;

        if ($e instanceof HttpNotFoundException) {
            $mensagem = 'Rota não encontrada.';
            $status = HttpStatusCode::NOT_FOUND;
        } elseif ($e instanceof HttpMethodNotAllowedException) {
            $mensagem = 'Método não suportado.';
            $status = HttpStatusCode::METHOD_NOT_ALLOWED;
        } elseif ($e instanceof HttpUnauthorizedException) {
            $status = HttpStatusCode::UNAUTHORIZED;
            $mensagem = 'Não autorizado.';
        } elseif ($e instanceof HttpForbiddenException) {
            $status = HttpStatusCode::FORBIDDEN;
            $mensagem = 'Acesso proibido.';
        } elseif ($e instanceof HttpBadRequestException) {
            $status = HttpStatusCode::BAD_REQUEST;
            $mensagem = 'Requisição inválida.';
        }

        return [$mensagem, $status];
    }
}
