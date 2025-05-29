<?php
require_once __DIR__ . '/../models/Student.php';

class StudentController 
{
    // Get all students
    public static function getAll()
    {
        try {
            $studentModel = new Student();
            $students = $studentModel->obtenerTodos();

            http_response_code(200);
            echo json_encode($students);
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener los registros de la tabla.", $e);
        }
    }

    // Get student by ID
    public static function getStudentById($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $studentModel = new Student();
            $student = $studentModel->obtenerPorId($id);

            if ($student) {
                http_response_code(200);
                echo json_encode($student);
            } else {
                self::sendError(404, "Student no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro student", $e);
        }
    }

    public static function getStudentByEmail($email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return self::sendError(400, "El correo electrónico no es válido.");
        }

        try {
            $studentModel = new Student();
            $student = $studentModel->obtenerPorEmail($email);

            if ($student) {
                http_response_code(200);
                echo json_encode($student);
            } else {
                self::sendError(404, "Student no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro student", $e);
        }
    }

    public static function getStudentByMatricula($matricula)
    {
        try {
            $studentModel = new Student();
            $student = $studentModel->obtenerPorMatricula($matricula);

            if ($student) {
                http_response_code(200);
                echo json_encode($student);
            } else {
                self::sendError(404, "Student no encontrado");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al obtener el registro student", $e);
        }
    }

    public static function create($data)
    {
        $requiredVars = [
            'id_major',
            'id_group_english',
            'matricula',
            'first_names',
            'last_name',
            'email',
            'password'
        ];

        foreach ($requiredVars as $var) {
            if (!isset($data[$var]) || trim($data[$var]) === '') {
                http_response_code(400);
                echo json_encode(["error" => "El campo '$var' es obligatorio."]);
                return;
            }
        }

        // Validar que id_major e id_class_group_english sean numéricos
        if (!is_numeric($data['id_major']) || !is_numeric($data['id_group_english'])) {
            http_response_code(400);
            echo json_encode(["error" => "Los campos 'id_major' y 'id_group_english' deben ser numéricos."]);
            return;
        }

        // Validar formato del correo
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(["error" => "El correo electrónico no es válido."]);
            return;
        }

        try {
            $studentModel = new Student();
            $creado = $studentModel->crear($data);
    
            if ($creado) {
                http_response_code(201);
                echo json_encode([
                    "message" => "Student creado exitosamente.",
                    "student" => $data
                ]);
            } else {
                self::sendError(500, "Error al crear el student.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al crear el student.", $e);
        }
    }


    public static function update($id, $data)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }
    
        if (empty($data)) {
            return self::sendError(400, "Se requiere al menos un campo para actualizar el estudiante.");
        }
    
        try {
            $studentModel = new Student();
            $studentExistente = $studentModel->obtenerPorId($id);
    
            if (!$studentExistente) {
                return self::sendError(404, "No se encontró el student con id: $id");
            }
    
            $actualizado = $studentModel->actualizarPorId($id, $data);
    
            if ($actualizado) {
                $studentActualizado = $studentModel->obtenerPorId($id);
    
                http_response_code(200);
                echo json_encode([
                    "message" => "Estudiante actualizado exitosamente",
                    "student" => $studentActualizado
                ]);
            } else {
                self::sendError(500, "Error interno al intentar actualizar el student.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al actualizar el student", $e);
        }
    }

    public static function deleteOne($id)
    {
        if (!is_numeric($id)) {
            return self::sendError(400, "El id debe ser numérico.");
        }

        try {
            $studentModel = new Student();

            // 1. Obtener el registro antes de eliminar
            $student = $studentModel->obtenerPorId($id);
            if (!$student) {
                return self::sendError(404, "No se encontró el nivel con id: $id");
            }

            // 2. Eliminarlo
            $eliminado = $studentModel->eliminarPorId($id);

            if ($eliminado) {
                http_response_code(200);
                echo json_encode([
                    "message" => "Student eliminado exitosamente.",
                    "student" => $student
                ]);
            } else {
                self::sendError(500, "Error interno al intentar eliminar el Student.");
            }
        } catch (Exception $e) {
            self::sendError(500, "Error al eliminar el Student.", $e);
        }
    }

    // Helper for error response
    private static function sendError($code, $message, $exception = null)
    {
        http_response_code($code);
        $response = ["error" => $message];

        if (getenv('APP_ENV') === 'development' && $exception) {
            $response["details"] = $exception->getMessage();
        }

        echo json_encode($response);
    }
}