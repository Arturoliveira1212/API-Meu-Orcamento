<?php

namespace app\services;

use app\classes\OperacaoObjeto;
use Exception;
use app\services\Service;
use app\classes\jwt\TokenJWT;
use app\classes\Usuario;
use app\classes\utils\Validador;
use app\dao\UsuarioDAO;
use app\exceptions\NaoAutorizadoException;
use app\exceptions\NaoEncontradoException;
use app\exceptions\ServiceException;
use app\traits\Autenticavel;
use app\traits\Criptografavel;

class UsuarioService extends Service {
    use Criptografavel;
    use Autenticavel;

    public const ID_ADMINISTRADOR_MASTER = 1;
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
        if ($this->usuarioEhMaster($usuario)) {
            $erro['usuario'] = 'Não é possível editar o usuario master.';
        } else {
            $this->validarNome($usuario, $erro);
            $this->validarEmail($usuario, $operacaoObjeto, $erro);
            $this->validarSenha($usuario, $erro);
        }
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

    private function usuarioEhMaster(Usuario $usuario) {
        return $usuario->getId() == self::ID_ADMINISTRADOR_MASTER;
    }

    public function excluirComId(int $id) {
        $usuario = $this->obterComId($id);
        if ($this->usuarioEhMaster($usuario)) {
            $erro['usuario'] = 'Não é possível excluir o usuario master.';
            throw new ServiceException(json_encode($erro));
        }

        return parent::excluirComId($id);
    }

    public function autenticar(string $email, string $senha) {
        $usuario = $this->obterComEmail($email);
        if (!$usuario instanceof Usuario || !$this->verificarSenha($senha, $usuario->getSenha())) {
            throw new NaoAutorizadoException('Email ou senha inválidos.');
        }

        $tokenJWT = $this->gerarToken(
            $usuario->getId(),
            $usuario->getNome(),
            'admin'
        );

        if (!$tokenJWT instanceof TokenJWT) {
            throw new Exception('Houve um erro ao gerar o token de acesso.');
        }

        return $tokenJWT;
    }

    public function obterComEmail(string $email) {
        $restricoes = ['email' => $email];
        $usuarioes = $this->obterComRestricoes($restricoes);

        return array_shift($usuarioes);
    }

    public function salvarPermissoes(array $permissoes, int $idUsuario) {
        $usuario = $this->obterComId($idUsuario);
        if (!$usuario instanceof Usuario) {
            throw new NaoEncontradoException('Usuario não encontrado.');
        }

        $erro = [];
        $this->validarPermissoes($usuario, $permissoes, $erro);
        if (!empty($erro)) {
            throw new ServiceException(json_encode($erro));
        }

        /** @var UsuarioDAO */
        $usuarioDAO = $this->dao();
        $usuarioDAO->limparPermissoes($usuario);

        if (!empty($permissoes)) {
            $idsPermissao = $usuarioDAO->obterIdsPermissao($permissoes);
            $usuarioDAO->salvarPermissoes($usuario, $idsPermissao);
        }
    }

    private function validarPermissoes(Usuario $usuario, array $permissoes, array &$erro = []) {
        if ($this->usuarioEhMaster($usuario)) {
            $erro['permissoes'] = 'Não é permitido alterar as permissões do usuario master.';
        } elseif (!empty($permissoes)) {
            /** @var UsuarioDAO */
            $usuarioDAO = $this->dao();
            $idsPermissao = $usuarioDAO->obterIdsPermissao($permissoes);
            if (empty($idsPermissao)) {
                $erro['permissoes'] = 'Nenhuma permissão enviada é válida.';
            }
        }
    }
}
