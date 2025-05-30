<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';
require_once 'controllers/StudentController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/auth/student/StudentLoginController.php';
require_once 'controllers/auth/user/UserLoginController.php';
require_once 'middlewares/SessionValidator.php';


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


