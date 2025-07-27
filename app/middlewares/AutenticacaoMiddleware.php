<?php

namespace app\middlewares;

use app\classes\http\HttpStatusCode;
use app\classes\http\RespostaHttp;
use app\traits\Autenticavel;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Slim\Psr7\Response;

class AutenticacaoMiddleware implements MiddlewareInterface {
    use Autenticavel;

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $autorization = $request->getHeaderLine('Authorization');
        if (!$autorization || !preg_match('/^Bearer\s(\S+)/', $autorization, $matches)) {
            return $this->usuarioNaoAutenticado('Token de autenticação não foi enviado.');
        }

        $token = $matches[1];

        $payloadJWT = $this->decodificarToken($token);

        if (!$payloadJWT instanceof PayloadJWT) {
            return $this->usuarioNaoAutenticado();
        }

        $request = $request->withAttribute('payloadJWT', $payloadJWT);

        return $handler->handle($request);
    }

    private function usuarioNaoAutenticado(string $mensagem = 'Token de autenticação inválido.') {
        return RespostaHttp::enviarResposta(new Response(), HttpStatusCode::UNAUTHORIZED, [
            'message' => $mensagem
        ]);
    }
}
