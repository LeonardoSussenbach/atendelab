<?php

class AtendimentosController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    // ─── LISTAR TODOS (com JOIN) ─────────────────────────────────────────────
    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = 'SELECT
                    a.id,
                    p.nome        AS pessoa,
                    u.nome        AS atendente,
                    t.descricao   AS tipo_atendimento,
                    a.descricao,
                    a.status,
                    a.data_atendimento
                FROM atendimentos a
                INNER JOIN pessoas           p ON p.id = a.pessoa_id
                INNER JOIN usuarios          u ON u.id = a.usuario_id
                INNER JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
                ORDER BY a.id DESC';

        $stmt          = $this->pdo->query($sql);
        $atendimentos  = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($atendimentos, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ─── BUSCAR POR ID ──────────────────────────────────────────────────────
    public function buscarPorId(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        $sql = 'SELECT
                    a.id,
                    p.nome        AS pessoa,
                    u.nome        AS atendente,
                    t.descricao   AS tipo_atendimento,
                    a.descricao,
                    a.status,
                    a.data_atendimento
                FROM atendimentos a
                INNER JOIN pessoas            p ON p.id = a.pessoa_id
                INNER JOIN usuarios           u ON u.id = a.usuario_id
                INNER JOIN tipos_atendimentos t ON t.id = a.tipo_atendimento_id
                WHERE a.id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $atendimento = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$atendimento) {
            http_response_code(404);
            echo json_encode(['erro' => 'Atendimento não encontrado.']);
            return;
        }

        echo json_encode($atendimento, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ─── CRIAR ──────────────────────────────────────────────────────────────
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $pessoa_id          = filter_input(INPUT_POST, 'pessoa_id',          FILTER_VALIDATE_INT);
        $usuario_id         = filter_input(INPUT_POST, 'usuario_id',         FILTER_VALIDATE_INT);
        $tipo_atendimento_id = filter_input(INPUT_POST, 'tipo_atendimento_id', FILTER_VALIDATE_INT);
        $descricao          = trim($_POST['descricao'] ?? '');
        $status             = $_POST['status']         ?? 'aberto';

        if (!$pessoa_id || !$usuario_id || !$tipo_atendimento_id) {
            http_response_code(400);
            echo json_encode(['erro' => 'pessoa_id, usuario_id e tipo_atendimento_id são obrigatórios.']);
            return;
        }

        if (!in_array($status, ['aberto', 'encerrado'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Use aberto ou encerrado.']);
            return;
        }

        try {
            $sql  = 'INSERT INTO atendimentos (pessoa_id, usuario_id, tipo_atendimento_id, descricao, status)
                     VALUES (:pessoa_id, :usuario_id, :tipo_atendimento_id, :descricao, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':pessoa_id',           $pessoa_id,           PDO::PARAM_INT);
            $stmt->bindValue(':usuario_id',          $usuario_id,          PDO::PARAM_INT);
            $stmt->bindValue(':tipo_atendimento_id', $tipo_atendimento_id, PDO::PARAM_INT);
            $stmt->bindValue(':descricao',           $descricao ?: null);
            $stmt->bindValue(':status',              $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Atendimento registrado com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao registrar atendimento.']);
        }
    }

    // ─── ATUALIZAR STATUS ───────────────────────────────────────────────────
    public function atualizarStatus(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id     = filter_input(INPUT_POST, 'id',     FILTER_VALIDATE_INT);
        $status = $_POST['status'] ?? '';

        if (!$id || $status === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e status são obrigatórios.']);
            return;
        }

        if (!in_array($status, ['aberto', 'encerrado'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido. Use aberto ou encerrado.']);
            return;
        }

        try {
            $sql  = 'UPDATE atendimentos SET status = :status WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', $status);
            $stmt->bindValue(':id',     $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Status do atendimento atualizado com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar status do atendimento.']);
        }
    }
}