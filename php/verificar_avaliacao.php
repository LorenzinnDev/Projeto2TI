<?php
header('Content-Type: application/json');

$conn = new mysqli("localhost", "root", "", "avaliacoes_chamados");
if ($conn->connect_error) {
  echo json_encode(["success" => false, "error" => "Erro de conexão"]);
  exit;
}

$ticket_id = $_GET['ticket_id'] ?? '';
$ticket_id = $conn->real_escape_string($ticket_id);

$result = $conn->query("SELECT 1 FROM avaliacoes WHERE ticket_id = '$ticket_id' LIMIT 1");

if ($result && $result->num_rows > 0) {
  echo json_encode(["ja_avaliado" => true]);
} else {
  echo json_encode(["ja_avaliado" => false]);
}

$conn->close();
?>