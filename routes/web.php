<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';
require_once 'controllers/auth/student/StudentLoginController.php';
require_once 'middlewares/SessionValidator.php';


//Login/Logout para Alumnos
Route::post('/student/login', [StudentLoginController::class, 'login']);
Route::post('/student/logout', [StudentLoginController::class, 'logout'], [SessionValidator::class, 'check']);

//Login/Logout para Profesores



// Rutas para Level con parámetros dinámicos 
Route::post('/level/create', [LevelController::class, 'create'],);
Route::get('/level/getOne/{id}', [LevelController::class, 'getOne'], );
Route::get('/level/getAll', [LevelController::class, 'getAll'],);
Route::put('/level/update/{id}', [LevelController::class, 'update'],);
Route::delete('/level/deleteOne/{id}', [LevelController::class, 'deleteOne'],);


