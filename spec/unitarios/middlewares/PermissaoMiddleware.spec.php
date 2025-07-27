<?php

namespace spec\app\middlewares;

use Mockery;
use Slim\Psr7\Response;
use app\classes\TipoPermissao;
use app\classes\jwt\PayloadJWT;
use app\classes\http\HttpStatusCode;
use app\middlewares\PermissaoMiddleware;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

describe('PermissaoMiddleware', function () {
    beforeEach(function () {
        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->handler = Mockery::mock(RequestHandlerInterface::class);
        $this->payloadJWT = Mockery::mock(PayloadJWT::class);
    });

    function validarErroMiddleware($response, int $status, array $respostaEmArrayEsperada) {
        $respostaEmArray = json_decode($response->getBody(), true);

        expect($response)->toBeAnInstanceOf(Response::class);
        expect($response->getStatusCode())->toEqual($status);
        expect($respostaEmArray)->toBeA('array');
        expect($respostaEmArray)->toContainKeys(array_keys($respostaEmArrayEsperada));
        expect($respostaEmArray)->toEqual($respostaEmArrayEsperada);
    }

    it('Retorna erro(403) quando o tipo de permissão não for permitido', function () {
        $this->payloadJWT->shouldReceive('role')->andReturn('admin');
        $this->request->shouldReceive('getAttribute')->with('payloadJWT')->andReturn($this->payloadJWT);

        $tiposPermissao = [
            new TipoPermissao('cliente', 'permissaoCliente')
        ];
        $middleware = new PermissaoMiddleware($tiposPermissao);

        $response = $middleware($this->request, $this->handler);

        validarErroMiddleware($response, HttpStatusCode::FORBIDDEN, [
            'sucess' => false,
            'message' => 'Você não tem permissão para realizar essa ação.'
        ]);
    });

    it('Retorna erro(403) quando o middleware não for encontrado', function () {
        $this->payloadJWT->shouldReceive('role')->andReturn('admin');
        $this->request->shouldReceive('getAttribute')->with('payloadJWT')->andReturn($this->payloadJWT);

        $tiposPermissao = [
            new TipoPermissao('admin', 'middlewareInexistente', [])
        ];
        $middleware = new PermissaoMiddleware($tiposPermissao);
        allow($middleware)->toReceive('existeMiddleware')->andReturn(false);

        $response = $middleware($this->request, $this->handler);

        validarErroMiddleware($response, HttpStatusCode::FORBIDDEN, [
            'sucess' => false,
            'message' => 'Você não tem permissão para realizar essa ação.'
        ]);
    });
});
