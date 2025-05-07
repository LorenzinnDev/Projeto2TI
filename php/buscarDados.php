<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
  // Conectar ao banco do GLPI
  $conn = new mysqli("helpdesk.optimall.com.br", "satisfaction_user", "V6x0@p3RgV8wS#haU6h91z", "glpi");

  if ($conn->connect_error) {
    throw new Exception("Erro de conexão: " . $conn->connect_error);
  }

  // Captura o ticket_id via GET
  if (!isset($_GET['ticket_id'])) {
    throw new Exception("Parâmetro ticket_id ausente.");
  }

  $ticket_id = $conn->real_escape_string($_GET['ticket_id']);

  // Consulta SQL para obter o nome do requerente e a data de criação do ticket
  $sql = "
    SELECT u.name AS requester_name, t.date AS ticket_createdate
    FROM glpi_tickets t
    JOIN glpi_users u ON t.users_id_recipient = u.id
    WHERE t.id = '$ticket_id'
    LIMIT 1
  ";

  $result = $conn->query($sql);
  if (!$result) {
    throw new Exception("Erro na consulta: " . $conn->error);
  }

  if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode([
      "requester_name" => $row['requester_name'],
      "ticket_createdate" => $row['ticket_createdate']
    ]);
  } else {
    echo json_encode(["requester_name" => null, "ticket_createdate" => null]);
  }

  $conn->close();
} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["error" => $e->getMessage()]);
}
?>
