<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

try {
  $conn = new mysqli("localhost", "root", "", "avaliacoes_chamados");
  if ($conn->connect_error) {
    throw new Exception("Erro de conexão: " . $conn->connect_error);
  }

  $data = json_decode(file_get_contents("php://input"), true);
  if (!$data) {
    throw new Exception("JSON inválido.");
  }

  $ticket_id = $conn->real_escape_string($data['ticket_id']);
  $ticket_name = $conn->real_escape_string($data['ticket_name']);
  $ticket_createdate = $conn->real_escape_string($data['ticket_createdate']);
  $estrelas = (int)$data['estrelas'];
  $comentario = $conn->real_escape_string($data['comentario']);
  $requester_name = $conn->real_escape_string($data['requester_name']);


  $conn->query("CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(255),
    ticket_name TEXT,
    ticket_createdate VARCHAR(255),
    estrelas INT,
    comentario TEXT,
    data_submissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unico_ticket UNIQUE (ticket_id)
  )");

$sql = "INSERT INTO avaliacoes (ticket_id, ticket_name, ticket_createdate, requester_name, estrelas, comentario)
VALUES ('$ticket_id', '$ticket_name', '$ticket_createdate', '$requester_name', $estrelas, '$comentario')";


  if ($conn->query($sql)) {
    echo json_encode(["success" => true]);
  } else {
    if ($conn->errno == 1062) {
      echo json_encode(["success" => false, "error" => "Você já avaliou este chamado."]);
    } else {
      throw new Exception("Erro ao inserir: " . $conn->error);
    }
  }

  $conn->close();
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
