<?php

namespace app\services;

use app\classes\model\RefreshToken;
use app\classes\model\Token;
use app\classes\OperacaoObjeto;
use Exception;
use app\services\Service;
use app\classes\jwt\TokenJWT;
use app\classes\Usuario;
use app\classes\utils\Validador;
use app\exceptions\NaoAutorizadoException;
use app\traits\Autenticavel;
use app\traits\Criptografavel;
use TipoToken;

class UsuarioService extends Service {
    use Criptografavel;
    use Autenticavel;

    public const TAMANHO_MINIMO_NOME = 2;
    public const TAMANHO_MAXIMO_NOME = 100;
    public const TAMANHO_MINIMO_EMAIL = 5;
    public const TAMANHO_MAXIMO_EMAIL = 200;
    public const TAMANHO_SENHA = 8;

    protected function preSalvar($usuario, OperacaoObjeto $operacaoObjeto, ?int $idRecursoPai = null) {
        parent::preSalvar($usuario, $operacaoObjeto, $idRecursoPai);

        $senha = $usuario->getSenha();
        $senhaCriptografada = $this->gerarHash($senha);
        $usuario->setSenha($senhaCriptografada);
    }

    protected function validar($usuario, OperacaoObjeto $operacaoObjeto, array &$erro = []) {
        $this->validarNome($usuario, $erro);
        $this->validarEmail($usuario, $operacaoObjeto, $erro);
        $this->validarSenha($usuario, $erro);
    }

    private function validarNome(Usuario $usuario, array &$erro) {
        $validacaoTamanhoNome = Validador::validarTamanhoTexto($usuario->getNome(), self::TAMANHO_MINIMO_NOME, self::TAMANHO_MAXIMO_NOME);
        if ($validacaoTamanhoNome == 0) {
            $erro['nome'] = 'Preencha o nome.';
        } elseif ($validacaoTamanhoNome == -1) {
            $erro['nome'] = 'O nome deve ter entre ' . self::TAMANHO_MINIMO_NOME . ' e ' . self::TAMANHO_MAXIMO_NOME . ' caracteres.';
        }
    }

    private function validarEmail(Usuario $usuario, OperacaoObjeto $operacaoObjeto, array &$erro) {
        $validacaoTamanhoEmail = Validador::validarTamanhoTexto($usuario->getEmail(), self::TAMANHO_MINIMO_EMAIL, self::TAMANHO_MAXIMO_EMAIL);
        if ($validacaoTamanhoEmail == 0) {
            $erro['email'] = 'Preencha o email.';
        } elseif ($validacaoTamanhoEmail == -1) {
            $erro['email'] = 'O email deve ter entre ' . self::TAMANHO_MINIMO_EMAIL . ' e ' . self::TAMANHO_MAXIMO_EMAIL . ' caracteres.';
        } elseif (!Validador::validarEmail($usuario->getEmail())) {
            $erro['email'] = 'Email inválido.';
        } elseif ($this->emailPertenceAOutroUsuario($usuario, $operacaoObjeto)) {
            $erro['email'] = 'Email já pertence a outro usuario.';
        }
    }

    private function emailPertenceAOutroUsuario(Usuario $usuario, OperacaoObjeto $operacaoObjeto) {
        $usuarioCadastrado = $this->obterComEmail($usuario->getEmail());
        $existeUsuario = $usuarioCadastrado instanceof Usuario;

        if ($existeUsuario && $operacaoObjeto == OperacaoObjeto::CADASTRAR) {
            return true;
        }

        if ($existeUsuario && $operacaoObjeto == OperacaoObjeto::EDITAR && $usuario->getId() != $usuarioCadastrado->getId()) {
            return true;
        }

        return false;
    }

    private function validarSenha(Usuario $usuario, array &$erro) {
        $validacaoTamanhoSenha = Validador::validarTamanhoTexto($usuario->getSenha(), self::TAMANHO_SENHA, self::TAMANHO_SENHA);
        if ($validacaoTamanhoSenha == 0) {
            $erro['senha'] = 'Preencha a senha.';
        } elseif ($validacaoTamanhoSenha == -1) {
            $erro['senha'] = 'A senha deve ter ' . self::TAMANHO_SENHA . ' caracteres.';
        }
    }

    public function autenticar(string $email, string $senha) {
        $usuario = $this->obterComEmail($email);
        if (!$usuario instanceof Usuario || !$this->verificarSenha($senha, $usuario->getSenha())) {
            throw new NaoAutorizadoException('Email ou senha inválidos.');
        }

        $resultadoAccesToken = $this->gerarToken(
            TipoToken::ACCESS_TOKEN,
            $usuario->getId(),
            $usuario->getNome(),
            'usuario',
            TokenJWT::DURACAO_ACCESS_TOKEN
        );

        $codigoRefreshToken = $this->gerarToken(
            TipoToken::REFRESH_TOKEN,
            $usuario->getId(),
            $usuario->getNome(),
            'usuario',
            TokenJWT::DURACAO_REFRESH_TOKEN
        );

        if (empty($codigoAccessToken) || empty($codigoRefreshToken)) {
            throw new Exception('Houve um erro ao gerar o token de acesso.');
        }

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken
        ];
    }

    public function obterComEmail(string $email): ?Usuario {
        $restricoes = ['email' => $email];
        $usuarioes = $this->obterComRestricoes($restricoes);

        return array_shift($usuarioes);
    }
}
