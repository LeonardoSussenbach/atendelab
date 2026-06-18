<?php

require_once __DIR__ . '/app/Middleware/auth.php';
require_once __DIR__ . '/app/Controllers/AuthController.php';
require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';

$controller = $_GET['controller'] ?? 'auth';
$action     = $_GET['action']     ?? 'login';

switch ($controller) {

    case 'auth':
        $authController = new AuthController();
        switch ($action) {
            case 'login':     $authController->exibirLogin(); break;
            case 'entrar':    $authController->entrar();      break;
            case 'dashboard': $authController->dashboard();   break;
            case 'logout':    $authController->logout();      break;
            default: http_response_code(404); echo 'Acao nao encontrada.';
        }
        break;

    case 'usuarios':
        exigirAutenticacao();
        $ctrl = new UsuariosController();
        switch ($action) {
            case 'listar':    $ctrl->listar();      break;
            case 'buscar':    $ctrl->buscarPorId(); break;
            case 'criar':     $ctrl->criar();       break;
            case 'atualizar': $ctrl->atualizar();   break;
            case 'excluir':   $ctrl->excluir();     break;
            default: http_response_code(404); echo 'Acao nao encontrada.';
        }
        break;

    case 'pessoas':
        exigirAutenticacao();
        $ctrl = new PessoasController();
        switch ($action) {
            case 'listar':    $ctrl->listar();      break;
            case 'buscar':    $ctrl->buscarPorId(); break;
            case 'criar':     $ctrl->criar();       break;
            case 'atualizar': $ctrl->atualizar();   break;
            case 'inativar':  $ctrl->inativar();    break;
            default: http_response_code(404); echo 'Acao nao encontrada.';
        }
        break;

    case 'tipos':
        exigirAutenticacao();
        $ctrl = new TiposAtendimentosController();
        switch ($action) {
            case 'listar':    $ctrl->listar();      break;
            case 'buscar':    $ctrl->buscarPorId(); break;
            case 'criar':     $ctrl->criar();       break;
            case 'atualizar': $ctrl->atualizar();   break;
            case 'inativar':  $ctrl->inativar();    break;
            default: http_response_code(404); echo 'Acao nao encontrada.';
        }
        break;

    case 'atendimentos':
        exigirAutenticacao();
        $ctrl = new AtendimentosController();
        switch ($action) {
            case 'listar':        $ctrl->listar();        break;
            case 'buscar':        $ctrl->buscarPorId();   break;
            case 'criar':         $ctrl->criar();         break;
            case 'alterarStatus': $ctrl->alterarStatus(); break;
            default: http_response_code(404); echo 'Acao nao encontrada.';
        }
        break;

    default:
        http_response_code(404);
        echo 'Controller nao encontrado.';
}