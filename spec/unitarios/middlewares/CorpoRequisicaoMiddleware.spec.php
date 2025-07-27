<?php

use app\classes\http\HttpStatusCode;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use app\middlewares\CorpoRequisicaoMiddleware;
use Slim\Psr7\Response;

describe('CorpoRequisicaoMiddleware', function () {
    beforeEach(function () {
        $this->request = Mockery::mock(ServerRequestInterface::class);
        $this->handler = Mockery::mock(RequestHandlerInterface::class);
    });

    it('Retorna erro(400) quando o Content-Type é inválido.', function () {
        $middleware = new CorpoRequisicaoMiddleware('application/json', ['nome' => 'string']);

        $this->request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/xml');
        $this->request->shouldReceive('getParsedBody')->andReturn(['nome' => 'Artur']);
        $response = $middleware($this->request, $this->handler);

        validarErroMiddleware($response, HttpStatusCode::BAD_REQUEST, [
            'sucess' => false,
            'message' => 'O corpo da requisição deve ser em JSON válido.'
        ]);
    });

    it('Retorna erro(400) quando o corpo de requisição é vazio.', function () {
        $middleware = new CorpoRequisicaoMiddleware('application/json', ['nome' => 'string']);

        $this->request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json');
        $this->request->shouldReceive('getParsedBody')->andReturn([]);
        $response = $middleware($this->request, $this->handler);

        validarErroMiddleware($response, HttpStatusCode::BAD_REQUEST, [
            'sucess' => false,
            'message' => 'O corpo da requisição deve ser em JSON válido.'
        ]);
    });

    it('Retorna erro(400) quando o corpo de requisição não tem os campos obrigatórios.', function () {
        $middleware = new CorpoRequisicaoMiddleware('application/json', ['nome' => 'string']);

        $this->request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json');
        $this->request->shouldReceive('getParsedBody')->andReturn(['artur' => 'artur ']);
        $response = $middleware($this->request, $this->handler);

        validarErroMiddleware($response, HttpStatusCode::BAD_REQUEST, [
            'sucess' => false,
            'message' => 'O corpo da requisição é inválido.',
            'data' => [
                'erros' => [
                    'nome' => 'Campo nome não foi enviado.'
                ]
            ]
        ]);
    });

    it('Retorna erro(400) quando o corpo de requisição tem os campos obrigatórios mas de tipos inválidos.', function () {
        $middleware = new CorpoRequisicaoMiddleware('application/json', [
            'nome' => 'string',
            'idade' => 'numeric',
            'quantidade' => 'int',
            'peso' => 'float',
            'efetivado' => 'bool',
            'dias' => 'array',
            'objeto' => 'object'
        ]);

        $this->request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json');
        $this->request->shouldReceive('getParsedBody')->andReturn([
            'nome' => 11111,
            'idade' => 'aaaa',
            'quantidade' => 2.0,
            'peso' => 2,
            'efetivado' => 'aaaaa',
            'dias' => 'array',
            'objeto' => 'a'
        ]);
        $response = $middleware($this->request, $this->handler);

        validarErroMiddleware($response, HttpStatusCode::BAD_REQUEST, [
            'sucess' => false,
            'message' => 'O corpo da requisição é inválido.',
            'data' => [
                'erros' => [
                    'nome' => 'Campo nome deve ser do tipo string.',
                    'idade' => 'Campo idade deve ser do tipo numeric.',
                    'quantidade' => 'Campo quantidade deve ser do tipo int.',
                    'peso' => 'Campo peso deve ser do tipo float.',
                    'efetivado' => 'Campo efetivado deve ser do tipo bool.',
                    'dias' => 'Campo dias deve ser do tipo array.',
                    'objeto' => 'Campo objeto deve ser do tipo object.'
                ]
            ]
        ]);
    });

    it('Deve continuar a execução quando o corpo da requisição é válido', function () {
        $middleware = new CorpoRequisicaoMiddleware('application/json', ['nome' => 'string']);

        $this->request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json');
        $this->request->shouldReceive('getParsedBody')->andReturn(['nome' => 'Artur Alves']);

        $this->handler->shouldReceive('handle')->with($this->request)->andReturn(new Response());
        $this->request->shouldReceive('withParsedBody')->with(['nome' => 'Artur Alves'])->andReturn($this->request);

        $response = $middleware($this->request, $this->handler);
        expect($response->getStatusCode())->toEqual(HttpStatusCode::OK);
    });

    it('Deve limpar o corpo requisição e continuar a execução quando outros campos são enviados', function () {
        $middleware = new CorpoRequisicaoMiddleware('application/json', ['nome' => 'string']);

        $this->request->shouldReceive('getHeaderLine')->with('Content-Type')->andReturn('application/json');
        $this->request->shouldReceive('getParsedBody')->andReturn(['nome' => 'Artur Alves', 'id' => 1, 'admin' => true]);

        $this->handler->shouldReceive('handle')->with($this->request)->andReturn(new Response());
        $this->request->shouldReceive('withParsedBody')->with(['nome' => 'Artur Alves'])->andReturn($this->request);

        $response = $middleware($this->request, $this->handler);
        expect($response->getStatusCode())->toEqual(HttpStatusCode::OK);
    });
});
