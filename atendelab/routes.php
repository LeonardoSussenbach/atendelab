<?php

require_once __DIR__ . '/app/Controllers/UsuariosController.php';
require_once __DIR__ . '/app/Controllers/PessoasController.php';
require_once __DIR__ . '/app/Controllers/TiposAtendimentosController.php';
require_once __DIR__ . '/app/Controllers/AtendimentosController.php';

$controller = $_GET['controller'] ?? 'home';
$action     = $_GET['action']     ?? 'index';

// ─── USUÁRIOS ────────────────────────────────────────────────────────────────
if ($controller === 'usuarios') {

    $ctrl = new UsuariosController();

    switch ($action) {
        case 'listar':    $ctrl->listar();      break;
        case 'buscar':    $ctrl->buscarPorId(); break;
        case 'criar':     $ctrl->criar();       break;
        case 'atualizar': $ctrl->atualizar();   break;
        case 'excluir':   $ctrl->excluir();     break;
        default: echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// ─── PESSOAS ─────────────────────────────────────────────────────────────────
} elseif ($controller === 'pessoas') {

    $ctrl = new PessoasController();

    switch ($action) {
        case 'listar':    $ctrl->listar();      break;
        case 'buscar':    $ctrl->buscarPorId(); break;
        case 'criar':     $ctrl->criar();       break;
        case 'atualizar': $ctrl->atualizar();   break;
        case 'inativar':  $ctrl->inativar();    break;
        default: echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// ─── TIPOS DE ATENDIMENTOS ───────────────────────────────────────────────────
} elseif ($controller === 'tipos') {

    $ctrl = new TiposAtendimentosController();

    switch ($action) {
        case 'listar':    $ctrl->listar();      break;
        case 'buscar':    $ctrl->buscarPorId(); break;
        case 'criar':     $ctrl->criar();       break;
        case 'atualizar': $ctrl->atualizar();   break;
        case 'inativar':  $ctrl->inativar();    break;
        default: echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// ─── ATENDIMENTOS ────────────────────────────────────────────────────────────
} elseif ($controller === 'atendimentos') {

    $ctrl = new AtendimentosController();

    switch ($action) {
        case 'listar':          $ctrl->listar();          break;
        case 'buscar':          $ctrl->buscarPorId();     break;
        case 'criar':           $ctrl->criar();           break;
        case 'atualizarStatus': $ctrl->atualizarStatus(); break;
        default: echo json_encode(['erro' => 'Ação não encontrada.']); break;
    }

// ─── HOME ────────────────────────────────────────────────────────────────────
} else {
    echo '<h1>AtendeLab</h1>';
    echo '<p>Rotas disponíveis: usuarios | pessoas | tipos | atendimentos</p>';
}