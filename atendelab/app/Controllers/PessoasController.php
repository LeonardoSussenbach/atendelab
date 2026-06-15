<?php

class PessoasController
{
    private PDO $pdo;

    public function __construct()
    {
        require __DIR__ . '/../../config/database.php';
        $this->pdo = $pdo;
    }

    // ─── LISTAR TODOS ───────────────────────────────────────────────────────
    public function listar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $sql = 'SELECT id, nome, email, telefone, matricula, status, criado_em
                FROM pessoas
                ORDER BY id DESC';

        $stmt   = $this->pdo->query($sql);
        $pessoas = $stmt->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($pessoas, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
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

        $sql  = 'SELECT id, nome, email, telefone, matricula, status, criado_em
                 FROM pessoas
                 WHERE id = :id';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':id', $id, PDO::PARAM_INT);
        $stmt->execute();

        $pessoa = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$pessoa) {
            http_response_code(404);
            echo json_encode(['erro' => 'Pessoa não encontrada.']);
            return;
        }

        echo json_encode($pessoa, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    // ─── CRIAR ──────────────────────────────────────────────────────────────
    public function criar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $nome      = trim($_POST['nome']      ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telefone  = trim($_POST['telefone']  ?? '');
        $matricula = trim($_POST['matricula'] ?? '');
        $status    = $_POST['status']         ?? 'ativo';

        if ($nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'O nome é obrigatório.']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql  = 'INSERT INTO pessoas (nome, email, telefone, matricula, status)
                     VALUES (:nome, :email, :telefone, :matricula, :status)';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':email',     $email ?: null);
            $stmt->bindValue(':telefone',  $telefone ?: null);
            $stmt->bindValue(':matricula', $matricula ?: null);
            $stmt->bindValue(':status',    $status);
            $stmt->execute();

            http_response_code(201);
            echo json_encode([
                'mensagem' => 'Pessoa cadastrada com sucesso.',
                'id'       => $this->pdo->lastInsertId()
            ], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao cadastrar pessoa.']);
        }
    }

    // ─── ATUALIZAR ──────────────────────────────────────────────────────────
    public function atualizar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id        = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
        $nome      = trim($_POST['nome']      ?? '');
        $email     = trim($_POST['email']     ?? '');
        $telefone  = trim($_POST['telefone']  ?? '');
        $matricula = trim($_POST['matricula'] ?? '');
        $status    = $_POST['status']         ?? 'ativo';

        if (!$id || $nome === '') {
            http_response_code(400);
            echo json_encode(['erro' => 'ID e nome são obrigatórios.']);
            return;
        }

        if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['erro' => 'E-mail inválido.']);
            return;
        }

        if (!in_array($status, ['ativo', 'inativo'], true)) {
            http_response_code(400);
            echo json_encode(['erro' => 'Status inválido.']);
            return;
        }

        try {
            $sql = 'UPDATE pessoas
                    SET nome      = :nome,
                        email     = :email,
                        telefone  = :telefone,
                        matricula = :matricula,
                        status    = :status
                    WHERE id = :id';

            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':nome',      $nome);
            $stmt->bindValue(':email',     $email ?: null);
            $stmt->bindValue(':telefone',  $telefone ?: null);
            $stmt->bindValue(':matricula', $matricula ?: null);
            $stmt->bindValue(':status',    $status);
            $stmt->bindValue(':id',        $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa atualizada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao atualizar pessoa.']);
        }
    }

    // ─── INATIVAR (exclusão lógica) ─────────────────────────────────────────
    public function inativar(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);

        if (!$id) {
            http_response_code(400);
            echo json_encode(['erro' => 'ID inválido.']);
            return;
        }

        try {
            $sql  = 'UPDATE pessoas SET status = :status WHERE id = :id';
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindValue(':status', 'inativo');
            $stmt->bindValue(':id',     $id, PDO::PARAM_INT);
            $stmt->execute();

            echo json_encode(['mensagem' => 'Pessoa inativada com sucesso.'], JSON_UNESCAPED_UNICODE);

        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['erro' => 'Erro ao inativar pessoa.']);
        }
    }
}