<?php

namespace app\services;

// use app\classes\enum\OperacaoObjeto;
use app\classes\jwt\PayloadJWT;
use app\classes\Model;
use app\classes\OperacaoObjeto;
use app\dao\BancoDadosRelacional;
use app\dao\DAO;
use app\exceptions\NaoEncontradoException;
use app\exceptions\ServiceException;

abstract class Service {
    protected DAO $dao;
    protected ?PayloadJWT $payloadJWT;

    public function __construct(DAO $dao) {
        $this->setDao($dao);
    }

    protected function dao() {
        return $this->dao;
    }

    protected function setDao(DAO $dao) {
        $this->dao = $dao;
    }

    public function getPayloadJWT() {
        return $this->payloadJWT;
    }

    public function setPayloadJWT(?PayloadJWT $payloadJWT) {
        $this->payloadJWT = $payloadJWT;
    }

    abstract protected function validar(Model $objeto, OperacaoObjeto $operacaoObjeto, array &$erro = []);

    protected function preSalvar($objeto, OperacaoObjeto $operacaoObjeto, ?int $idRecursoPai = null) {
        $id = $objeto->getId();
        if ($operacaoObjeto == OperacaoObjeto::EDITAR && !$this->existe('id', $id)) {
            throw new NaoEncontradoException('Recurso não encontrado.');
        }

        $erro = [];
        $this->validar($objeto, $operacaoObjeto, $erro);
        if (!empty($erro)) {
            throw new ServiceException(json_encode($erro));
        }
    }

    public function salvar($objeto, ?int $idRecursoPai = null) {
        $operacaoObjeto = $objeto->getId() == BancoDadosRelacional::ID_INEXISTENTE ? OperacaoObjeto::CADASTRAR : OperacaoObjeto::EDITAR;
        $this->preSalvar($objeto, $operacaoObjeto, $idRecursoPai);
        $retorno = $this->dao()->salvar($objeto, $operacaoObjeto, $idRecursoPai);
        $this->posSalvar($objeto, $operacaoObjeto, $idRecursoPai);

        return $retorno;
    }

    protected function posSalvar($objeto, OperacaoObjeto $operacaoObjeto, ?int $idRecursoPai = null) {
    }

    public function desativarComId(int $id) {
        return $this->dao()->desativarComId($id);
    }

    /**
     * Método responsável por excluir o objeto pelo id.
     *
     * @param integer $id
     * @return int
     * @throws NaoEncontradoException
     */
    public function excluirComId(int $id) {
        $existe = $this->existe('id', $id);
        if (!$existe) {
            throw new NaoEncontradoException('Recurso não encontrado.');
        }

        return $this->dao()->excluirComId($id);
    }

    public function existe(string $campo, string $valor) {
        return $this->dao()->existe($campo, $valor);
    }

    /**
     * Método responsável por obter o objeto pelo id.
     *
     * @param integer $id
     * @return Model
     * @throws NaoEncontradoException
     */
    public function obterComId(int $id) {
        $objeto = $this->dao()->obterComId($id);
        if (!$objeto instanceof Model) {
            throw new NaoEncontradoException('Recurso não encontrado.');
        }

        return $objeto;
    }

    public function obterComRestricoes(array $restricoes) {
        $this->filtrarRestricoes($restricoes);
        return $this->dao()->obterComRestricoes($restricoes);
    }

    private function filtrarRestricoes(array &$restricoes) {
        // TO DO => Implementar orderBy

        if (isset($restricoes['limit']) && !empty($restricoes['limit']) && !is_numeric($restricoes['limit'])) {
            unset($restricoes['limit']);
        }

        if (isset($restricoes['offset']) && !empty($restricoes['offset']) && !is_numeric($restricoes['offset'])) {
            unset($restricoes['offset']);
        }
    }
}
