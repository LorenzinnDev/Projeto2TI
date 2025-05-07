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

  // Consulta para obter a localização do ticket no banco GLPI
  $glpi_conn = new mysqli("helpdesk.optimall.com.br", "satisfaction_user", "V6x0@p3RgV8wS#haU6h91z", "glpi");
  if ($glpi_conn->connect_error) {
    throw new Exception("Erro de conexão com o GLPI: " . $glpi_conn->connect_error);
  }

  $sql_location = "
    SELECT l.name AS location_name
    FROM glpi_tickets t
    JOIN glpi_locations l ON t.locations_id = l.id
    WHERE t.id = '$ticket_id'
  ";

  $result_location = $glpi_conn->query($sql_location);
  if (!$result_location) {
    throw new Exception("Erro na consulta de localização: " . $glpi_conn->error);
  }

  $location_name = null;
  if ($result_location->num_rows > 0) {
    $row = $result_location->fetch_assoc();
    $location_name = $row['location_name'];
  }

  // Cria a tabela de avaliações caso não exista
  $conn->query("CREATE TABLE IF NOT EXISTS avaliacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id VARCHAR(255),
    ticket_name TEXT,
    ticket_createdate VARCHAR(255),
    requester_name VARCHAR(255),
    estrelas INT,
    comentario TEXT,
    location_name VARCHAR(255),
    data_submissao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unico_ticket UNIQUE (ticket_id)
  )");

  // Inserção dos dados de avaliação, incluindo a localização
  $sql = "INSERT INTO avaliacoes (ticket_id, ticket_name, ticket_createdate, requester_name, estrelas, comentario, location_name)
  VALUES ('$ticket_id', '$ticket_name', '$ticket_createdate', '$requester_name', $estrelas, '$comentario', '$location_name')";

  if ($conn->query($sql)) {
    echo json_encode(["success" => true]);
  } else {
    if ($conn->errno == 1062) {
      echo json_encode(["success" => false, "error" => "Você já avaliou este chamado."]);
    } else {
      throw new Exception("Erro ao inserir: " . $conn->error);
    }
  }

  // Fecha a conexão com o GLPI
  $glpi_conn->close();
  $conn->close();

} catch (Exception $e) {
  http_response_code(500);
  echo json_encode(["success" => false, "error" => $e->getMessage()]);
}
?>
