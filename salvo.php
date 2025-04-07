<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "avaliacao";

// Criar a conexão com o banco de dados
$conn = new mysqli($servername, $username, $password, $dbname);

// Verificar se a conexão foi bem-sucedida
if ($conn->connect_error) {
    die("Conexão falhou: " . $conn->connect_error);
}

// Receber os dados enviados via POST (JSON)
$data = json_decode(file_get_contents('php://input'), true);

// Verificar se os dados foram recebidos corretamente
if (!$data) {
    echo json_encode(["message" => "Erro: Nenhum dado foi recebido."]);
    exit;
}

// Extrair os dados recebidos
$nome = $data['nome'] ?? '';
$setor = $data['setor'] ?? '';
$data_avaliacao = $data['data'] ?? '';
$hora_avaliacao = $data['hora'] ?? '';
$opcao = $data['opcao'] ?? '';
$avaliacao = $data['avaliacao'] ?? 0;
$comentario = $data['comentario'] ?? '';

// Validar campos obrigatórios
if (!$nome || !$setor || !$data_avaliacao || !$hora_avaliacao || !$opcao || $avaliacao < 1 || $avaliacao > 5) {
    echo json_encode(["message" => "Erro: Todos os campos são obrigatórios e a avaliação deve ser de 1 a 5."]);
    exit;
}

// Preparar a query para inserir o feedback no banco de dados
$stmt = $conn->prepare("INSERT INTO feedback (nome, setor, data_avaliacao, hora_avaliacao, opcao, avaliacao, comentario) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)");

// Vincular os parâmetros para a query
$stmt->bind_param("sssssis", $nome, $setor, $data_avaliacao, $hora_avaliacao, $opcao, $avaliacao, $comentario);

// Executar a query e verificar se foi bem-sucedida
if ($stmt->execute()) {
    echo json_encode(["message" => "Feedback salvo com sucesso!"]);
} else {
    echo json_encode(["message" => "Erro ao salvar feedback: " . $stmt->error]);
}

// Fechar a declaração e a conexão com o banco de dados
$stmt->close();
$conn->close();
?> 