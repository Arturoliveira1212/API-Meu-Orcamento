<?php

namespace app\dao;

use app\classes\Usuario;
use app\classes\utils\ConversorDados;

class UsuarioDAO extends DAOEmBDR {
    protected function nomeTabela() {
        return 'usuario';
    }

    protected function adicionarNovo($usuario, ?int $idRecursoPai = null) {
        $comando = "INSERT INTO {$this->nomeTabela()} ( id, nome, email, senha ) VALUES ( :id, :nome, :email, :senha )";
        $this->getBancoDados()->executar($comando, $this->parametros($usuario));
    }

    protected function atualizar($usuario) {
        $comando = "UPDATE {$this->nomeTabela()} SET nome = :nome, email = :email, senha = :senha WHERE id = :id";
        $this->getBancoDados()->executar($comando, $this->parametros($usuario));
    }

    protected function parametros($usuario) {
        return [
            'id' => $usuario->getId(),
            'nome' => $usuario->getNome(),
            'email' => $usuario->getEmail(),
            'senha' => $usuario->getSenha()
        ];
    }

    protected function obterQuery(array $restricoes, array &$parametros) {
        $nomeTabela = $this->nomeTabela();

        $select = "SELECT * FROM {$nomeTabela}";
        $where = ' WHERE ativo = 1 ';
        $join = '';
        $orderBy = '';

        if (isset($restricoes['email'])) {
            $where .= " AND {$nomeTabela}.email = :email ";
            $parametros['email'] = $restricoes['email'];
        }

        $comando = $select . $join . $where . $orderBy;
        return $comando;
    }

    protected function transformarEmObjeto(array $linhas) {
        $usuario = new Usuario();
        $usuario->setId(intval($linhas['id']));
        $usuario->setNome($linhas['nome']);
        $usuario->setEmail($linhas['email']);
        $usuario->setSenha($linhas['senha']);

        return $usuario;
    }
}
