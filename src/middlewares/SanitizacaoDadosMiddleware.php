<?php

namespace app\middlewares;

use app\classes\utils\Sanitizador;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;

class SanitizacaoDadosMiddleware implements MiddlewareInterface {
    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface {
        $corpoRequisicao = $request->getParsedBody();
        if (is_array($corpoRequisicao)) {
            $corpoRequisicaoLimpo = Sanitizador::limparArray($corpoRequisicao);
            $request = $request->withParsedBody($corpoRequisicaoLimpo);
        }

        $parametros = $request->getQueryParams();
        if (is_array($parametros)) {
            $parametrosLimpos = Sanitizador::limparArray($parametros);
            $request = $request->withQueryParams($parametrosLimpos);
        }

        return $handler->handle($request);
    }
}
