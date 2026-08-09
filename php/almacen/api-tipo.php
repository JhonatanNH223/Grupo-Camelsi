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
        obtenerTipo();
        break;
    case 'POST':
        crearAlmacen($input);
        break;
    case 'PUT':
        actualizarAlmacen($input);
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

function obtenerTipo()
{
    global $pdo;
    header('Content-Type: application/json'); // Opcional pero recomendado

    $resultado = [];

    try {
        if (isset($_GET['id2']) && $_GET['id2'] !== '') {
            $id2 = $_GET['id2'];
            $stmt = $pdo->prepare("SELECT * FROM tipo_articulo WHERE id_articulo = ? ");
            $stmt->execute([$id2]);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM tipo_articulo");
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


function crearAlmacen($data)
{
    global $pdo;
    try {

        $articuloNuevo = strtolower(trim($data['articulo']));
        $checkbox = $data['checkbox'] ?? false;

        if ($checkbox) {
            $tipoExistente = strtolower(trim($data['existenteTipo']));

            $stmt1 = $pdo->prepare("SELECT * FROM tipo_articulo WHERE tipo = ? AND nombre = ?");
            $stmt1->execute([$tipoExistente, $articuloNuevo]);

            if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un Articulo con el mismo nombre en este Tipo']);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO tipo_articulo (tipo, nombre) VALUES (?, ?)");
            $resultado = $stmt->execute([$tipoExistente, $articuloNuevo]);
        } else {
            $nuevoTipo = strtolower(trim($data['nuevoTipo']));

            $stmt1 = $pdo->prepare("SELECT * FROM tipo_articulo WHERE tipo = ?");
            $stmt1->execute([$nuevoTipo]);

            if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
                echo json_encode(['success' => false, 'message' => 'Ya existe un Tipo con este Nombre']);
                return;
            }

            $stmt = $pdo->prepare("INSERT INTO tipo_articulo (tipo, nombre) VALUES (?, ?)");
            $resultado = $stmt->execute([$nuevoTipo, $articuloNuevo]);
        }

        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Registro creado exitosamente',
                'id' => $pdo->lastInsertId()
            ]);
        } else {
            throw new Exception("No se pudo insertar el registro");
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => $e->getMessage()
        ]);
    }
}


function actualizarAlmacen($data)
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

    try {

        $tipoExistente = strtolower(trim($data['existenteTipo']));
        $articuloNuevo = strtolower(trim($data['articulo']));

        $stmt1 = $pdo->prepare("SELECT * FROM tipo_articulo WHERE tipo = ? AND nombre = ?");
        $stmt1->execute([$tipoExistente, $articuloNuevo]);

        if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
            echo json_encode(['success' => false, 'message' => 'Ya existe un articulo con ese nombre en este tipo']);
            return;
        }



        
        //Checkbox no marcado: actualizar sin tocar la contraseña
        $stmt = $pdo->prepare("UPDATE tipo_articulo SET tipo = ?, nombre = ? WHERE id_articulo = ?");

        $resultado = $stmt->execute([
            $tipoExistente,
            $articuloNuevo,
            $data['id']
        ]);

        if ($resultado && $stmt->rowCount() > 0) {
            // Obtener el elemento actualizado
            $stmt = $pdo->prepare("SELECT * FROM tipo_articulo WHERE id_articulo = ?");
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
        $stmt = $pdo->prepare("DELETE FROM tipo_articulo WHERE id_articulo = ?");
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
