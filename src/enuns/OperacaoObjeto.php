<?php

namespace app\classes\model;

enum OperacaoObjeto: string {
    case CADASTRAR = 'CADASTRAR';
    case EDITAR = 'EDITAR';
    case EXCLUIR = 'EXCLUIR';
    case OBTER_COM_ID = 'OBTER_COM_ID';
    case OBTER_TODOS = 'OBTER_TODOS';
}