<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

include_once '../config/database.php';

class Auth {
    private $conn;
    private $table_name = "usuarios";

    public function __construct($db) {
        $this->conn = $db;
    }

    public function login($email, $senha) {
        $query = "SELECT id, username, email, senha FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if(password_verify($senha, $row['senha'])) {
                return array(
                    "id" => $row['id'],
                    "username" => $row['username'],
                    "email" => $row['email']
                );
            }
        }
        return false;
    }
}

// Receber dados do POST
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->senha)) {
    $database = new Database();
    $db = $database->getConnection();
    
    $auth = new Auth($db);
    $result = $auth->login($data->email, $data->senha);
    
    if($result) {
        http_response_code(200);
        echo json_encode($result);
    } else {
        http_response_code(401);
        echo json_encode(array("message" => "Login inválido."));
    }
} else {
    http_response_code(400);
    echo json_encode(array("message" => "Dados incompletos."));
}
?> 