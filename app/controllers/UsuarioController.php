<?php

namespace app\controllers;

use app\classes\Usuario;
use app\controllers\Controller;
use app\classes\http\HttpStatusCode;
use app\classes\jwt\TokenJWT;
use app\services\UsuarioService;

class UsuarioController extends Controller {
    protected function criar(array $dados) {
        $usuario = new Usuario();
        $camposSimples = ['id', 'nome', 'email', 'senha'];
        $this->povoarSimples($usuario, $camposSimples, $dados);

        return $usuario;
    }

    public function login(array $dados) {
        ['email' => $email, 'senha' => $senha] = $dados;

        /** @var UsuarioService */
        $usuarioService = $this->service();
        /** @var TokenJWT */
        $tokenJWT = $usuarioService->autenticar($email, $senha);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Usuario autenticado com sucesso.',
            'data' => [
                'Token' => $tokenJWT->codigo(),
                'Duração' => $tokenJWT->validadeTokenFormatada()
            ]
        ]);
    }

    public function salvarPermissoes(array $dados, $args) {
        $id = intval($args['id']);
        $permissoes = $dados['permissoes'] ?? [];

        /** @var UsuarioService */
        $usuarioService = $this->service();
        $usuarioService->salvarPermissoes($permissoes, $id);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Permissões salvas com sucesso.'
        ]);
    }
}
