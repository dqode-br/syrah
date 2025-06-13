<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET,POST,PUT,DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class Cliente {
    private $conn;
    private $table_name = "clientes";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function read() {
        $query = "SELECT * FROM " . $this->table_name . " ORDER BY nome";
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt;
    }

    public function create($data) {
        $query = "INSERT INTO " . $this->table_name . "
                (codigo, nome, cnpj_cpf, email, telefone, endereco)
                VALUES
                (:codigo, :nome, :cnpj_cpf, :email, :telefone, :endereco)";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":codigo", $data->codigo);
        $stmt->bindParam(":nome", $data->nome);
        $stmt->bindParam(":cnpj_cpf", $data->cnpj_cpf);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":telefone", $data->telefone);
        $stmt->bindParam(":endereco", $data->endereco);

        if($stmt->execute()) {
            return true;
        }
        return false;
    }

    public function update($data) {
        $query = "UPDATE " . $this->table_name . "
                SET
                    nome = :nome,
                    cnpj_cpf = :cnpj_cpf,
                    email = :email,
                    telefone = :telefone,
                    endereco = :endereco
                WHERE
                    id = :id";

        $stmt = $this->conn->prepare($query);

        $stmt->bindParam(":nome", $data->nome);
        $stmt->bindParam(":cnpj_cpf", $data->cnpj_cpf);
        $stmt->bindParam(":email", $data->email);
        $stmt->bindParam(":telefone", $data->telefone);
        $stmt->bindParam(":endereco", $data->endereco);
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
        return "CLI" . str_pad($next_number, 10, "0", STR_PAD_LEFT);
    }
}

$database = new Database();
$db = $database->getConnection();
$cliente = new Cliente($db);

$method = $_SERVER['REQUEST_METHOD'];

switch($method) {
    case 'GET':
        if(isset($_GET['next_code'])) {
            echo json_encode(array("codigo" => $cliente->getNextCode()));
        } else {
            $stmt = $cliente->read();
            $num = $stmt->rowCount();
            
            if($num > 0) {
                $clientes_arr = array();
                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                    array_push($clientes_arr, $row);
                }
                http_response_code(200);
                echo json_encode($clientes_arr);
            } else {
                http_response_code(404);
                echo json_encode(array("message" => "Nenhum cliente encontrado."));
            }
        }
        break;

    case 'POST':
        $data = json_decode(file_get_contents("php://input"));
        if($cliente->create($data)) {
            http_response_code(201);
            echo json_encode(array("message" => "Cliente criado com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível criar o cliente."));
        }
        break;

    case 'PUT':
        $data = json_decode(file_get_contents("php://input"));
        if($cliente->update($data)) {
            http_response_code(200);
            echo json_encode(array("message" => "Cliente atualizado com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível atualizar o cliente."));
        }
        break;

    case 'DELETE':
        $data = json_decode(file_get_contents("php://input"));
        if($cliente->delete($data->id)) {
            http_response_code(200);
            echo json_encode(array("message" => "Cliente excluído com sucesso."));
        } else {
            http_response_code(503);
            echo json_encode(array("message" => "Não foi possível excluir o cliente."));
        }
        break;
}
?> 