<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class Conta {
    private $conn;
    private $table_name = "contas";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY data_vencimento";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                (codigo, descricao, tipo, valor, data_vencimento, status)
                VALUES
                (:codigo, :descricao, :tipo, :valor, :data_vencimento, :status)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":codigo", $data->codigo);
        $stmt->bindParam(":descricao", $data->descricao);
        $stmt->bindParam(":tipo", $data->tipo);
        $stmt->bindParam(":valor", $data->valor);
        $stmt->bindParam(":data_vencimento", $data->data_vencimento);
        $stmt->bindParam(":status", $data->status);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($data) {
        $query = "UPDATE " . $this->table_name . "
                SET
                    descricao = :descricao,
                    tipo = :tipo,
                    valor = :valor,
                    data_vencimento = :data_vencimento,
                    status = :status
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":descricao", $data->descricao);
        $stmt->bindParam(":tipo", $data->tipo);
        $stmt->bindParam(":valor", $data->valor);
        $stmt->bindParam(":data_vencimento", $data->data_vencimento);
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
        return "CON" . str_pad($next_number, 10, "0", STR_PAD_LEFT);
    }
}

$database = new Database();
$db = $database->getConnection();
$conta = new Conta($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['next_code'])) {
            echo json_encode(array("codigo" => $conta->getNextCode()));
        } else {
            $stmt = $conta->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $contas_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($contas_arr, $row);
                }
                http_response_code(200);
                echo json_encode($contas_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Nenhuma conta encontrada."));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if($conta->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Conta criada com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível criar a conta."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if($conta->update($data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Conta atualizada com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível atualizar a conta."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if($conta->delete($data->id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Conta excluída com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível excluir a conta."));
        }
        break;
}
?> 