<?php

namespace app\middlewares;

use app\classes\Usuario;
use app\services\UsuarioService;
use Psr\Http\Server\MiddlewareInterface;
use Slim\Psr7\Response;
use app\classes\jwt\PayloadJWT;
use app\classes\http\RespostaHttp;
use app\classes\http\HttpStatusCode;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class PermissaoMiddleware implements MiddlewareInterface {
    private UsuarioService $usuarioService;

    public function __construct($usuarioService) {
        $this->usuarioService = $usuarioService;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        /** @var PayloadJWT */
        $payloadJWT = $request->getAttribute('payloadJWT');
        $idToken = $payloadJWT->sub();
        $idUrl = $this->obterIdURL($request);

        $usuario = $this->usuarioService->obterComId($idToken);

        if (!$usuario instanceof Usuario || $usuario->getId() != $idUrl) {
            return $this->usuarioSemPermissao();
        }

        return $handler->handle($request);
    }

    private function obterIdURL(ServerRequestInterface $request) {
        $routeContext = RouteContext::fromRequest($request);
        $route = $routeContext->getRoute();
        $idUrl = $route->getArguments()['id'] ?? 0;

        return intval($idUrl);
    }

    private function usuarioSemPermissao(string $mensagem = 'Você não tem permissão para realizar essa ação.') {
        return RespostaHttp::enviarResposta(new Response(), HttpStatusCode::FORBIDDEN, [
            'message' => $mensagem
        ]);
    }
}
