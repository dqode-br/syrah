<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class Documento {
    private $conn;
    private $table_name = "documentos";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY data_emissao DESC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                (codigo, tipo, descricao, data_emissao, valor, status)
                VALUES
                (:codigo, :tipo, :descricao, :data_emissao, :valor, :status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":codigo", $data->codigo);
        $stmt->bindParam(":tipo", $data->tipo);
        $stmt->bindParam(":descricao", $data->descricao);
        $stmt->bindParam(":data_emissao", $data->data_emissao);
        $stmt->bindParam(":valor", $data->valor);
        $stmt->bindParam(":status", $data->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($data) {
        $query = "UPDATE " . $this->table_name . "
                SET
                    tipo = :tipo,
                    descricao = :descricao,
                    data_emissao = :data_emissao,
                    valor = :valor,
                    status = :status
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":tipo", $data->tipo);
        $stmt->bindParam(":descricao", $data->descricao);
        $stmt->bindParam(":data_emissao", $data->data_emissao);
        $stmt->bindParam(":valor", $data->valor);
        $stmt->bindParam(":status", $data->status);
        $stmt->bindParam(":id", $data->id);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function delete($id) {
        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $id);
        
        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function getNextCode() {
        $query = "SELECT MAX(CAST(SUBSTRING(codigo, 4) AS UNSIGNED)) as max_code FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        
        $next_number = ($row['max_code'] ?? 0) + 1;
        return "DOC" . str_pad($next_number, 10, "0", STR_PAD_LEFT);
    }
}

$database = new Database();
$db = $database->getConnection();
$documento = new Documento($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['next_code'])) {
            echo json_encode(array("codigo" => $documento->getNextCode()));
        } else {
            $stmt = $documento->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $documentos_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($documentos_arr, $row);
                }
                http_response_code(200);
                echo json_encode($documentos_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Nenhum documento encontrado."));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if($documento->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Documento criado com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível criar o documento."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if($documento->update($data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Documento atualizado com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível atualizar o documento."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if($documento->delete($data->id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Documento excluído com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível excluir o documento."));
        }
        break;
}
?> 