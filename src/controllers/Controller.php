<?php

namespace src\controllers;

use DateTime;
use src\models\Model;
use app\services\Service;
use app\classes\http\HttpStatusCode;
use Throwable;

abstract class Controller {
    private Service $service;

    public function __construct(Service $service) {
        $this->setService($service);
    }

    protected function service() {
        return $this->service;
    }

    protected function setService(Service $service) {
        $this->service = $service;
    }

    abstract protected function criar(array $corpoRequisicao);

    public function novo(array $corpoRequisicao, $args, $parametros) {
        $idRecursoPai = isset($args['id']) ? intval($args['id']) : null;
        $objeto = $this->criar($corpoRequisicao);
        $this->service()->salvar($objeto, $idRecursoPai);

        return $this->resposta(HttpStatusCode::CREATED, [
            'message' => 'Cadastrado com sucesso.'
        ]);
    }

    public function editar(array $corpoRequisicao, $args, $parametros) {
        $id = intval($args['id']);

        $objeto = $this->criar($corpoRequisicao);
        $objeto->setId($id);
        $this->service()->salvar($objeto);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Atualizado com suceso.'
        ]);
    }

    public function obterTodos(array $corpoRequisicao, $args, array $parametros) {
        $objeto = $this->service()->obterComRestricoes($parametros);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Sucesso ao obter os dados.',
            'data' => [
                $objeto
            ]
        ]);
    }

    public function obterComId(array $corpoRequisicao, $args) {
        $id = intval($args['id']);
        $objeto = $this->service()->obterComId($id);

        return $this->resposta(HttpStatusCode::OK, [
            'message' => 'Sucesso ao obter os dados.',
            'data' => [
                $objeto
            ]
        ]);
    }

    public function excluirComId(array $corpoRequisicao, $args, $parametros) {
        $id = intval($args['id']);
        $this->service()->excluirComId($id);

        return $this->resposta(HttpStatusCode::NO_CONTENT);
    }

    protected function povoarSimples(Model $objeto, array $campos, array $corpoRequisicao) {
        foreach ($campos as $campo) {
            if (isset($corpoRequisicao[$campo])) {
                $metodo = 'set' . ucfirst($campo);
                if (method_exists($objeto, $metodo)) {
                    try {
                        $objeto->$metodo($corpoRequisicao[$campo]);
                    } catch (Throwable $e) {
                    }
                }
            }
        }
    }

    protected function povoarDateTime(Model $objeto, array $campos, array $corpoRequisicao) {
        foreach ($campos as $campo) {
            if (isset($corpoRequisicao[$campo])) {
                $metodo = 'set' . ucfirst($campo);
                if (method_exists($objeto, $metodo)) {
                    $data = DateTime::createFromFormat('d/m/Y', $corpoRequisicao[$campo]);
                    if ($data) {
                        $objeto->$metodo($data);
                    }
                }
            }
        }
    }

    protected function resposta(HttpStatusCode $status = HttpStatusCode::OK, array $data = []) {
        return [
            'status' => $status,
            'data' => $data
        ];
    }
}
