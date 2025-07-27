<?php

use app\classes\factory\MiddlewareFactory;

$gerenciadorRecurso->post('/gastos', 'Gasto', 'novo', [
    MiddlewareFactory::autenticacao(),
    MiddlewareFactory::corpoRequisicao([
        'descricao' => 'string',
        'valor' => 'float',
        'data' => 'string',
        'categoria_id' => 'int'
    ])
]);