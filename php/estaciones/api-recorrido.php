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
        obtenerRecorrido();
        break;
    case 'POST':
        crearElemento($input);
        break;
    case 'PUT':
        actualizarElemento($input);
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

function obtenerRecorrido()
{
    global $pdo;

    $id = $_GET['id'] ?? null;
    $id2 = $_GET['id2'] ?? null;

    try {

        if (!$id) {
            $stmt = $pdo->prepare("SELECT * FROM recorrido WHERE id_recorrido = ? ");
            $stmt->execute([$id2]);
            $recorridos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        } else {
            $stmt = $pdo->prepare("SELECT * FROM recorrido WHERE f_estacion = ? ORDER BY id_recorrido");
            $stmt->execute([$id]);
            $recorridos = $stmt->fetchAll(PDO::FETCH_ASSOC);

            
        }
        
        echo json_encode([
            'success' => true,
            'data' => $recorridos,
            'message' => count($recorridos) > 0 ? 'Datos cargados correctamente' : 'No se encontraron registros'
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Error al obtener registros: ' . $e->getMessage()
        ]);
    }
}

function crearElemento($data)
{
    global $pdo;

    try {
        // Verificar que la estación existe
        $stmtEstacion = $pdo->prepare("SELECT id FROM estaciones WHERE id = ?");
        $stmtEstacion->execute([$data['f_estacion']]);
        if (!$stmtEstacion->fetch()) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'La estación especificada no existe',
                'debug' => ['estacion_id' => $data['f_estacion']]
            ]);
            return;
        }

        // Comprobar si ya existe un elemento con la misma localización en esta estación
        $stmt1 = $pdo->prepare("SELECT * FROM recorrido WHERE localizacion = ? AND f_estacion = ?");
        $stmt1->execute([
            $data['localizacion'],
            $data['f_estacion']
        ]);

        if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
            http_response_code();
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe un elemento con esa localización en esta estación'
            ]);
            return;
        }

        // Insertar nuevo registro
        $stmt = $pdo->prepare("INSERT INTO recorrido (
            localizacion, contenido, capacidad, carga, mantenimiento, metalico, presillo,
            manguera, cincho, señalam, soporte, pintura, fecha_mes, fecha_año, observaciones, f_estacion
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");

        $resultado = $stmt->execute([
            $data['localizacion'],
            $data['contenido'],
            $data['capacidad'],
            $data['carga'],
            $data['mantenimiento'],
            $data['metalico'],
            $data['presillo'],
            $data['manguera'],
            $data['cincho'],
            $data['senalam'],
            $data['soporte'],
            $data['pintura'],
            $data['fecha_mes'],
            $data['fecha_año'],
            $data['observaciones'],
            $data['f_estacion']
        ]);

        if ($resultado) {
            echo json_encode([
                'success' => true,
                'message' => 'Elemento del recorrido creado exitosamente',
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

function actualizarElemento($data)
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

    // Validar campos obligatorios
    if (empty($data['localizacion']) || empty($data['contenido'])) {
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'error' => 'Los campos localización y contenido son obligatorios'
        ]);
        return;
    }

    try {
        // Verificar si existe otro elemento con la misma localización (excluyendo el actual)
        $stmt1 = $pdo->prepare("SELECT * FROM recorrido WHERE localizacion = ? AND id_recorrido != ? AND f_estacion = ?");
        $stmt1->execute([
            $data['localizacion'],
            $data['id'],
            $data['f_estacion'] 
        ]);

        if ($stmt1->fetch(PDO::FETCH_ASSOC)) {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'error' => 'Ya existe otro elemento con esa localización'
            ]);
            return;
        }

        // Actualizar elemento
        $stmt = $pdo->prepare("UPDATE recorrido SET 
            localizacion = ?, contenido = ?, capacidad = ?, carga = ?, mantenimiento = ?, 
            metalico = ?, presillo = ?, manguera = ?, cincho = ?, señalam = ?, 
            soporte = ?, pintura = ?, fecha_mes = ?, fecha_año = ?, observaciones = ?
            WHERE id_recorrido = ?");

        $resultado = $stmt->execute([
            $data['localizacion'],
            $data['contenido'],
            $data['capacidad'],
            $data['carga'],
            $data['mantenimiento'],
            $data['metalico'],
            $data['presillo'],
            $data['manguera'],
            $data['cincho'],
            $data['senalam'],
            $data['soporte'],
            $data['pintura'],
            $data['fecha_mes'],
            $data['fecha_año'],
            $data['observaciones'],
            $data['id']
        ]);

        if ($resultado && $stmt->rowCount() > 0) {
            // Obtener el elemento actualizado
            $stmt = $pdo->prepare("SELECT * FROM recorrido WHERE id_recorrido = ?");
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
        $stmt = $pdo->prepare("DELETE FROM recorrido WHERE id_recorrido = ?");
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
