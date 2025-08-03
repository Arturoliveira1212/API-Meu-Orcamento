<?php

namespace src\controllers;

use app\classes\model\Usuario;
use src\controllers\Controller;
use app\classes\http\HttpStatusCode;

class UsuarioController extends Controller {
    protected function criar(array $corpoRequisicao) {
        $usuario = new Usuario();
        $camposSimples = ['id', 'nome', 'email', 'senha'];
        $this->povoarSimples($usuario, $camposSimples, $corpoRequisicao);

        return $usuario;
    }

    public function login(array $corpoRequisicao) {
        ['email' => $email, 'senha' => $senha] = $corpoRequisicao;

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
}
