<?php

namespace app\dao;

use app\classes\Model;
use app\classes\OperacaoObjeto;

abstract class DAOEmBDR implements DAO {
    private ?BancoDadosRelacional $bancoDados = null;

    public function __construct(BancoDadosRelacional $bancoDados) {
        $this->bancoDados = $bancoDados;
    }

    protected function getBancoDados() {
        return $this->bancoDados;
    }

    abstract protected function nomeTabela();
    abstract protected function adicionarNovo(Model $objeto, ?int $idRecursoPai = null);
    abstract protected function atualizar(Model $objeto);
    abstract protected function parametros(Model $objeto);
    abstract protected function obterQuery(array $restricoes, array &$parametros);
    abstract protected function transformarEmObjeto(array $linhas);

    public function salvar($objeto, OperacaoObjeto $operacaoObjeto, ?int $idRecursoPai = null) {
        if ($operacaoObjeto == OperacaoObjeto::CADASTRAR) {
            $this->adicionarNovo($objeto, $idRecursoPai);
        } elseif ($operacaoObjeto == OperacaoObjeto::EDITAR) {
            $this->atualizar($objeto);
        }

        return $this->getBancoDados()->ultimoIdInserido();
    }

    public function desativarComId(int $id) {
        return $this->getBancoDados()->desativar($this->nomeTabela(), $id);
    }

    public function excluirComId(int $id) {
        return $this->getBancoDados()->excluir($this->nomeTabela(), $id);
    }

    public function existe(string $campo, string $valor) {
        return $this->getBancoDados()->existe($this->nomeTabela(), $campo, $valor);
    }

    public function obterComId(int $id) {
        $comando = "SELECT * FROM {$this->nomeTabela()} WHERE id = :id AND ativo = :ativo";
        $parametros = ['id' => $id, 'ativo' => true];
        $objetos = $this->obterObjetos($comando, [$this, 'transformarEmObjeto'], $parametros);

        return !empty($objetos) ? array_shift($objetos) : null;
    }

    public function obterComRestricoes(array $restricoes) {
        $parametros = [];
        $query = $this->obterQuery($restricoes, $parametros);
        $this->preencherLimitEOffset($query, $restricoes);
        $objetos = $this->obterObjetos($query, [$this, 'transformarEmObjeto'], $parametros);

        return $objetos;
    }

    protected function preencherLimitEOffset(string &$query, array $restricoes) {
        $limit = '';
        $offset = '';

        if (isset($restricoes['limit']) && is_numeric($restricoes['limit'])) {
            $limit = " LIMIT {$restricoes['limit']} ";

            if (isset($restricoes['offset']) && is_numeric($restricoes['offset'])) {
                $offset = " OFFSET {$restricoes['offset']} ";
            }
        }

        $query = "{$query} {$limit} {$offset}";
    }

    public function obterObjetos(string $comando, array $callback, array $parametros = []) {
        $objetos = [];

        $resultados = $this->getBancoDados()->consultar($comando, $parametros);

        if (!empty($resultados)) {
            foreach ($resultados as $resultado) {
                $objeto = call_user_func_array($callback, [$resultado]);
                $objetos[] = $objeto;
            }
        }

        return $objetos;
    }
}
