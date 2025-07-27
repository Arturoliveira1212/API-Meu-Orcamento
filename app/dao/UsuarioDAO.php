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
        $parametros = ConversorDados::converterEmArray($usuario);
        unset($parametros['permissoes']);

        return $parametros;
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
        /** @var Usuario */
        $usuario = ConversorDados::converterEmObjeto(Usuario::class, $linhas);

        $permissoes = $this->permissoesDoUsuario($usuario);
        $usuario->setPermissoes($permissoes);

        return $usuario;
    }

    protected function permissoesDoUsuario(Usuario $usuario) {
        $comando = "SELECT permissao.descricao FROM permissao_usuario
            JOIN permissao ON permissao.id = permissao_usuario.idPermissao
                WHERE idUsuario = :idUsuario
                AND permissao_usuario.ativo = :ativo";
        $parametros = [
            'idUsuario' => $usuario->getId(),
            'ativo' => 1
        ];

        $permissoes = $this->getBancoDados()->consultar($comando, $parametros);
        if (!empty($permissoes)) {
            return array_map(function ($permissao) {
                return $permissao['descricao'];
            }, $permissoes);
        }

        return [];
    }

    /**
     * Método responsável por obter o id das permissões passadas por parâmetro.
     *
     * @param array $permissoes
     * @return array
     */
    public function obterIdsPermissao(array $permissoes) {
        $comando = "SELECT id FROM permissao WHERE descricao IN (" . implode(',', array_fill(0, count($permissoes), '?')) . ") AND ativo = 1";
        $ids = $this->getBancoDados()->consultar($comando, $permissoes);

        if (!empty($ids)) {
            return array_map(function ($id) {
                return $id['id'];
            }, $ids);
        }

        return [];
    }

    public function limparPermissoes(Usuario $usuario) {
        $comando = 'DELETE FROM permissao_usuario WHERE idUsuario = :idUsuario';
        $parametros = [
            'idUsuario' => $usuario->getId()
        ];
        return $this->getBancoDados()->executar($comando, $parametros);
    }

    public function salvarPermissoes(Usuario $usuario, array $idsPermissao) {
        $this->getBancoDados()->executarComTransacao(function () use ($usuario, $idsPermissao) {
            foreach ($idsPermissao as $idPermissao) {
                $this->adicionarPermissao($usuario, $idPermissao);
            }
        });
    }

    public function adicionarPermissao(Usuario $usuario, int $idPermissao) {
        $comando = 'INSERT INTO permissao_usuario ( idUsuario, idPermissao ) VALUES( :idUsuario, :idPermissao )';
        $parametros = [
            'idUsuario' => $usuario->getId(),
            'idPermissao' => $idPermissao
        ];
        return $this->getBancoDados()->executar($comando, $parametros);
    }
}
