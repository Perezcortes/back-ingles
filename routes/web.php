<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';
require_once 'controllers/StudentController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/auth/student/StudentLoginController.php';
require_once 'controllers/auth/user/UserLoginController.php';
require_once 'middlewares/SessionValidator.php';
require_once 'controllers/MajorController.php';
require_once 'controllers/EnglishGroupController.php';




//Login/Logout para Alumnos
Route::post('/student/login', [StudentLoginController::class, 'login']);
Route::post('/student/logout', [StudentLoginController::class, 'logout'], [SessionValidator::class, 'checkStudent']);

//Login/Logout para Profesores/Jefe de nivel/Administrador
Route::post('/user/login', [UserLoginController::class, 'login']);
Route::post('/user/logout', [UserLoginController::class, 'logout'], [SessionValidator::class, 'checkUser']);


// Rutas para Level con parámetros dinámicos 
Route::post('/level/create', [LevelController::class, 'create'],);
Route::get('/level/getOne/{id}', [LevelController::class, 'getOne'], );
Route::get('/level/getAll', [LevelController::class, 'getAll'],);
Route::put('/level/update/{id}', [LevelController::class, 'update'],);
Route::delete('/level/deleteOne/{id}', [LevelController::class, 'deleteOne'],);

// Rutas para student con parámetros dinámicos 
Route::post('/student/create', [StudentController::class, 'create'],);
Route::get('/student/getOne/Id/{id}', [StudentController::class, 'getStudentById'], );
Route::get('/student/getOne/Email/{id}', [StudentController::class, 'getStudentByEmail'], );
Route::get('/student/getOne/Matricula/{id}', [StudentController::class, 'getStudentByMatricula'], );
Route::get('/student/getAll', [StudentController::class, 'getAll'],);
Route::put('/student/update/{id}', [StudentController::class, 'update'],);
Route::delete('/student/deleteOne/{id}', [StudentController::class, 'deleteOne'],);

// Rutas para user con parámetros dinámicos 
Route::post('/user/create', [UserController::class, 'create'],);
Route::get('/user/getAll', [UserController::class, 'getAll'],);
Route::get('/user/getOne/Id/{id}', [UserController::class, 'getuserById'], );
Route::get('/user/getOne/Email/{id}', [UserController::class, 'getuserByEmail'], );
Route::put('/user/update/{id}', [UserController::class, 'update'],);
Route::delete('/user/deleteOne/{id}', [UserController::class, 'deleteOne'],);


// Rutas para EnglishGroup con parámetros dinámicos 
Route::post('/englishGroup/create', [EnglishGroupController::class, 'create']);
Route::get('/englishGroup/getOne/{id}', [EnglishGroupController::class, 'getOne']);
Route::get('/englishGroup/getByProfessor/{id_professor}', [EnglishGroupController::class, 'getByProfessor']);
Route::get('/englishGroup/getByLevel/{id_level}', [EnglishGroupController::class, 'getByLevel']);
Route::get('/englishGroup/getAll', [EnglishGroupController::class, 'getAll']);
Route::put('/englishGroup/update/{id}', [EnglishGroupController::class, 'update']);
Route::delete('/englishGroup/deleteOne/{id}', [EnglishGroupController::class, 'deleteOne']);
Route::post('/englishGroup/restore/{id}', [EnglishGroupController::class, 'restore']); // Para restaurar un grupo
Route::delete('/englishGroup/deletePermanent/{id}', [EnglishGroupController::class, 'deletePermanent']); // Eliminar permanentemente

// Rutas para Major con parámetros dinámicos
Route::post('/major/create', [MajorController::class, 'create']);
Route::get('/major/getOne/{id}', [MajorController::class, 'getOne']);
Route::get('/major/getAll', [MajorController::class, 'getAll']);
Route::put('/major/update/{id}', [MajorController::class, 'update']);
Route::delete('/major/deleteOne/{id}', [MajorController::class, 'deleteOne']);
Route::post('/major/restore/{id}', [MajorController::class, 'restore']); // Ruta para restaurar un major
Route::delete('/major/deletePermanent/{id}', [MajorController::class, 'deletePermanent']); // Ruta para eliminar permanentemente un major

