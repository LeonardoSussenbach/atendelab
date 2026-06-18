<?php

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../Middleware/auth.php';

class AuthController
{
    private PDO $pdo;

    public function __construct()
    {
        global $pdo;
        $this->pdo = $pdo;
    }

    // ─── EXIBIR LOGIN ────────────────────────────────────────────────────────
    public function exibirLogin(): void
    {
        // Se já está logado, vai direto pro dashboard
        if (usuarioAutenticado()) {
            header('Location: ?controller=auth&action=dashboard');
            exit;
        }

        // Pega mensagens temporárias da sessão
        $erro     = $_SESSION['erro_login'] ?? null;
        $mensagem = $_SESSION['mensagem']   ?? null;

        // Remove as mensagens pra não aparecer duas vezes
        unset($_SESSION['erro_login'], $_SESSION['mensagem']);

        require __DIR__ . '/../Views/auth/login.php';
    }

    // ─── ENTRAR ──────────────────────────────────────────────────────────────
    public function entrar(): void
    {
        // Só aceita POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Location: ?controller=auth&action=login');
            exit;
        }

        $email = trim($_POST['email'] ?? '');
        $senha = $_POST['senha']      ?? '';

        // Campos obrigatórios
        if ($email === '' || $senha === '') {
            $_SESSION['erro_login'] = 'Informe o e-mail e a senha.';
            header('Location: ?controller=auth&action=login');
            exit;
        }

        // Formato de e-mail válido
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['erro_login'] = 'Informe um e-mail valido.';
            header('Location: ?controller=auth&action=login');
            exit;
        }

        // Busca o usuário pelo e-mail
        $sql  = 'SELECT id, nome, email, senha, perfil, status
                 FROM usuarios
                 WHERE email = :email
                 LIMIT 1';

        $stmt = $this->pdo->prepare($sql);
        $stmt->bindValue(':email', $email);
        $stmt->execute();

        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        // Valida usuário, status e senha
        if (
            !$usuario
            || $usuario['status'] !== 'ativo'
            || !password_verify($senha, $usuario['senha'])
        ) {
            $_SESSION['erro_login'] = 'E-mail ou senha invalidos.';
            header('Location: ?controller=auth&action=login');
            exit;
        }

        // Gera novo ID de sessão por segurança
        session_regenerate_id(true);

        // Salva só o necessário na sessão (nunca a senha)
        $_SESSION['usuario'] = [
            'id'     => $usuario['id'],
            'nome'   => $usuario['nome'],
            'email'  => $usuario['email'],
            'perfil' => $usuario['perfil'],
        ];

        header('Location: ?controller=auth&action=dashboard');
        exit;
    }

    // ─── DASHBOARD ───────────────────────────────────────────────────────────
    public function dashboard(): void
    {
        exigirAutenticacao();

        $usuario = usuarioAtual();

        require __DIR__ . '/../Views/dashboard/index.php';
    }

    // ─── LOGOUT ──────────────────────────────────────────────────────────────
    public function logout(): void
    {
        // Limpa os dados da sessão
        $_SESSION = [];

        // Remove o cookie de sessão
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(
                session_name(),
                '',
                time() - 42000,
                $params['path'],
                $params['domain'],
                $params['secure'],
                $params['httponly']
            );
        }

        session_destroy();

        // Nova sessão só pra enviar a mensagem de retorno
        session_start();
        $_SESSION['mensagem'] = 'Sessao encerrada com sucesso.';

        header('Location: ?controller=auth&action=login');
        exit;
    }
}