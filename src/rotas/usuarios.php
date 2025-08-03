<?php

use app\classes\Usuario;
use app\classes\factory\MiddlewareFactory;

$gerenciadorRecurso->post('/usuarios', Usuario::class, 'novo', [
    MiddlewareFactory::corpoRequisicao([
        'nome' => 'string',
        'email' => 'string',
        'senha' => 'string'
    ])
]);

$gerenciadorRecurso->post('/usuarios/login', Usuario::class, 'login', [
    MiddlewareFactory::corpoRequisicao([
        'email' => 'string',
        'senha' => 'string'
    ])
]);

$gerenciadorRecurso->put('/usuarios/{id}', Usuario::class, 'editar', [
    MiddlewareFactory::autenticacao(),
    MiddlewareFactory::permissao(),
    MiddlewareFactory::corpoRequisicao([
        'nome' => 'string',
        'email' => 'string',
        'senha' => 'string'
    ])
]);

$gerenciadorRecurso->get('/usuarios/{id}', Usuario::class, 'obterComId', [
    MiddlewareFactory::autenticacao(),
    MiddlewareFactory::permissao()
]);

$gerenciadorRecurso->delete('/usuarios/{id}', Usuario::class, 'excluirComId', [
    MiddlewareFactory::autenticacao(),
    MiddlewareFactory::permissao()
]);