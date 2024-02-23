<?php

class Database
{
    private $conn;

    public function __construct($hostname, $username, $password, $database)
    {
        $this->conn = new mysqli($hostname, $username, $password, $database);
        if ($this->conn->connect_error) {
            die("Connection failed: " . $this->conn->connect_error);
        }
    }

    public function executeQuery($sql)
    {
        return $this->conn->query($sql);
    }

    public function __destruct()
    {
        $this->conn->close();
    }
}

class CRUD
{
    private $db;

    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    public function create($data)
    {
        $sql = "INSERT INTO schooldata (schoolid, first_name, middle_initial, last_name, gender, date_of_birth, course)
                VALUES ('{$data['schoolid']}', '{$data['first_name']}', '{$data['middle_initial']}', '{$data['last_name']}', '{$data['gender']}', '{$data['date_of_birth']}', '{$data['course']}')";
        return $this->db->executeQuery($sql);
    }

    public function update($data)
    {
        $sql = "UPDATE schooldata
                SET first_name='{$data['first_name']}', middle_initial='{$data['middle_initial']}', last_name='{$data['last_name']}',
                gender='{$data['gender']}', date_of_birth='{$data['date_of_birth']}', course='{$data['course']}'
                WHERE schoolid='{$data['schoolid']}'";
        return $this->db->executeQuery($sql);
    }

    public function delete($schoolid)
    {
        $sql = "DELETE FROM schooldata WHERE schoolid='{$schoolid}'";
        return $this->db->executeQuery($sql);
    }

    public function search($search)
    {
        $sql = "SELECT * FROM schooldata WHERE schoolid = '{$search}' OR first_name = '{$search}'";
        return $this->db->executeQuery($sql);
    }

    public function getAll()
    {
        $sql = "SELECT * FROM schooldata";
        return $this->db->executeQuery($sql);
    }

}

$db = new Database("localhost", "root", "", "clutch1");
$CRUD = new CRUD($db);

$method = $_SERVER['REQUEST_METHOD'];

switch ($method) {
    case "POST":
        $data = $_REQUEST;
        $res = $CRUD->create($data);
        if ($res === true) {
            echo "Record added";
        } else {
            echo "Error: " . $db->conn->error;
        }
        break;
    case "PUT":
        $_PUT = file_get_contents("php://input");
        parse_str($_PUT, $put_vars);
        $res = $CRUD->update($put_vars);
        if ($res === true) {
            echo "Record updated";
        } else {
            echo "Error: " . $db->conn->error;
        }
        break;

    case "DELETE":
        $schoolid = $_GET['schoolid'];
        $res = $CRUD->delete($schoolid);
        if ($res === true) {
            echo "Record deleted";
        } else {
            echo "Error: " . $db->conn->error;
        }
        break;

    default:
        $data = $_GET;
        if (isset($data['search'])) {
            $search = $data['search'];
            $result = $CRUD->search($search);
        } else {
            $result = $CRUD->getAll();
        }

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                var_dump($row);
            }
        } else {
            echo "No results found.";
        }
        break;
}
