<?php

namespace app\classes\factory;

use app\classes\Usuario;
use app\services\UsuarioService;
use app\middlewares\PermissaoMiddleware;
use app\middlewares\AutenticacaoMiddleware;
use app\middlewares\CorpoRequisicaoMiddleware;

class MiddlewareFactory {
    public static function autenticacao() {
        return new AutenticacaoMiddleware();
    }

    public static function permissao() {
        /** @var UsuarioService */
        $usuarioService = ClassFactory::makeService(Usuario::class);

        return new PermissaoMiddleware($usuarioService);
    }

    public static function corpoRequisicao(array $camposObrigatorios = [], array $camposOpcionais = [], string $contentType = 'application/json') {
        return new CorpoRequisicaoMiddleware(
            $contentType,
            $camposObrigatorios,
            $camposOpcionais
        );
    }
}
