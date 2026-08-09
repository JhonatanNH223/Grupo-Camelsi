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
        obtenerAlmacen();
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

function obtenerAlmacen()
{
    global $pdo;
    header('Content-Type: application/json'); // Opcional pero recomendado

    $resultado = [];

    try {
        if (isset($_GET['id2']) && $_GET['id2'] !== '') {
            $id2 = $_GET['id2'];
            $stmt = $pdo->prepare("SELECT * FROM almacentipo WHERE id_almacen = ? ");
            $stmt->execute([$id2]);
            $resultado = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM almacentipo");
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

        if ($data['stock'] < 0) {
            //http_response_code(409); // Conflicto
            echo json_encode([
                'success' => false,
                'error' => 'El stcok es menor de 0'
            ]);
            return;
        }

        $stmt1 = $pdo->prepare("SELECT * FROM almacen WHERE fk_articulo = ?");
        $stmt1->execute([
            $data['articulo']
        ]);

        if ($fila = $stmt1->fetch(PDO::FETCH_ASSOC)) {
            $stockbd = $fila['stock'];
            $nuevoStock = $stockbd + $data['stock'];
            // Puedes hacer algo con $stock aquí

            // Insertar nuevo registro
            $stmt = $pdo->prepare("UPDATE almacen SET stock = ? WHERE id_almacen = ?");
            $resultado = $stmt->execute([
                $nuevoStock,
                $fila['id_almacen']
            ]);
        } else {
            // Insertar nuevo registro
            $stmt = $pdo->prepare("INSERT INTO almacen (stock, fk_articulo) VALUES (?, ?)");
            $resultado = $stmt->execute([
                $data['stock'],
                $data['articulo']
            ]);
        }




        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Se ha creado exitosamente',
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

    if ($data['stock'] < 0) {
            //http_response_code(409); // Conflicto
            echo json_encode([
                'success' => false,
                'error' => 'El stcok es menor de 0'
            ]);
            return;
        }

    try {

        //Checkbox no marcado: actualizar sin tocar la contraseña
        $stmt = $pdo->prepare("UPDATE almacen SET stock = ? WHERE id_almacen = ?");

        $resultado = $stmt->execute([
            $data['stock'],
            $data['id']
        ]);

        if ($resultado && $stmt->rowCount() > 0) {
            // Obtener el elemento actualizado
            $stmt = $pdo->prepare("SELECT * FROM almacen WHERE id_almacen = ?");
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
        $stmt = $pdo->prepare("DELETE FROM almacen WHERE id_almacen = ?");
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
