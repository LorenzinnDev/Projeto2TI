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

  $sql = "INSERT INTO avaliacoes (ticket_id, ticket_name, ticket_createdate, estrelas, comentario)
          VALUES ('$ticket_id', '$ticket_name', '$ticket_createdate', $estrelas, '$comentario')";

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

/* -- Banco de dados e tabela para o sistema de avaliações GLPI

-- 1) Cria o banco de dados (se não existir)
CREATE DATABASE IF NOT EXISTS avaliacoes_chamados
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

-- 2) Seleciona o banco de dados
USE avaliacoes_chamados;

-- 3) Cria a tabela de avaliações (se não existir), garantindo que cada chamado só possa ser avaliado uma vez
CREATE TABLE IF NOT EXISTS avaliacoes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  ticket_id VARCHAR(255)        NOT NULL,
  ticket_name TEXT              NOT NULL,
  ticket_createdate VARCHAR(255) NOT NULL,
  estrelas INT                  NOT NULL  CHECK (estrelas BETWEEN 1 AND 5),
  comentario TEXT                       NULL,
  data_submissao TIMESTAMP       NOT NULL  DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT unico_ticket UNIQUE (ticket_id)
); */