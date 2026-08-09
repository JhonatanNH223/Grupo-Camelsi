<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

include("../../Conect/conect.php");

$method = $_SERVER['REQUEST_METHOD'];
$input = json_decode(file_get_contents('php://input'), true);

switch ($method) {
    case 'GET':
        obtenerUsuarios();
        break;
    case 'POST':
        crearUsuarios($input);
        break;
    case 'PUT':
        actualizarUsuario($input);
        break;
    case 'DELETE':
        eliminarElemento($input);
        break;
    case 'OPTIONS':
        http_response_code(200);
        break;
    default:
        http_response_code(405);
        echo json_encode([
            'success' => false,
            'error' => 'Método no permitido'
        ]);
        break;
}

function obtenerUsuarios()
{
    global $pdo;
    header('Content-Type: application/json'); // Opcional pero recomendado

    $resultado = [];

    try {
        if (isset($_GET['id2']) && $_GET['id2'] !== '') {
            $id2 = $_GET['id2'];
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ? ");
            $stmt->execute([$id2]);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM usuarios");
            $stmt->execute();
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        echo json_encode([
            'success' => true,
            'data' => $resultado,
            'message' => count($resultado) > 0 ? 'Datos cargados correctamente' : 'No se encontraron registros',
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener registros: ' . $e->getMessage()
        ]);
    }
}


function crearUsuarios($data)
{
    global $pdo;

    try {
        // Verificar que el username no esté duplicado
        $stmt1 = $pdo->prepare("SELECT * FROM usuarios WHERE username = ?");
        $stmt1->execute([$data['username']]);

        if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
            //http_response_code(409); // Conflicto
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe un usuario con este username'
            ]);
            return;
        }

        // Hashear la contraseña
        $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);

        // Insertar nuevo registro
        $stmt = $pdo->prepare("INSERT INTO usuarios (
            nombre, apellido, username, password, nivel, estatus
        ) VALUES (?, ?, ?, ?, ?, 1)");

        $resultado = $stmt->execute([
            $data['nombre'],
            $data['apellido'],
            $data['username'],
            $hashedPassword,
            $data['nivel']
        ]);

        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            throw new Exception('No se pudo insertar el registro');
        }
    } catch (PDOException $e) {
        error_log("Error PDO: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error de base de datos: ' . $e->getMessage(),
            'debug' => [
                'sql_error' => $e->getMessage(),
                'sql_code' => $e->getCode()
            ]
        ]);
    } catch (Exception $e) {
        error_log("Error general: " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al crear elemento: ' . $e->getMessage()
        ]);
    }
}


function actualizarUsuario($data)
{
    global $pdo;

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'ID del elemento requerido'
        ]);
        return;
    }

    if (empty($data['nivel'])) {
        $data['nivel'] = "SADMIN";
    }

    // Validar campos obligatorios
    if (empty($data['username'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Los campos username son obligatorios'
        ]);
        return;
    }

    try {
        $stmt1 = $pdo->prepare("SELECT * FROM usuarios WHERE username = ? AND id_usuario != ?");
        $stmt1->execute([
            $data['username'],
            $data['id'],
        ]);

        if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
            //http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe otro usuario con ese USERNAME'
            ]);
            return;
        }

        if ($data['checkbox']) {
            //Checkbox marcado: actualizar también la contraseña
            $hashedPassword = password_hash($data['password'], PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE usuarios SET 
            nombre = ?, apellido = ?, username = ?, password = ?, nivel = ? WHERE id_usuario = ?");

            $resultado = $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['username'],
                $hashedPassword,
                $data['nivel'],
                $data['id']
            ]);
        } else {
            //Checkbox no marcado: actualizar sin tocar la contraseña
            $stmt = $pdo->prepare("UPDATE usuarios SET 
            nombre = ?, apellido = ?, username = ?, nivel = ? WHERE id_usuario = ?");

            $resultado = $stmt->execute([
                $data['nombre'],
                $data['apellido'],
                $data['username'],
                $data['nivel'],
                $data['id']
            ]);
        }




        if ($resultado && $stmt->rowCount() > 0) {
            // Obtener el elemento actualizado
            $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id_usuario = ?");
            $stmt->execute([$data['id']]);
            $elemento = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'Elemento actualizado exitosamente',
                'data' => $elemento
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'error' => 'No se encontró el elemento o no hubo cambios'
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Errooooooooor al actualizar elemento: ' . $e->getMessage()
        ]);
    }
}

function eliminarElemento($data)
{
    global $pdo;

    if (empty($data['id'])) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de elemento requerido']);
        return;
    }

    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$data['id']]);

        if ($stmt->rowCount() > 0) {
            echo json_encode([
                'success' => true,
                'message' => 'Elemento eliminada exitosamente'
            ]);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Elemento no encontrada']);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Error al eliminar elemento: ' . $e->getMessage()]);
    }
}
