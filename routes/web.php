<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';
require_once 'controllers/auth/student/StudentLoginController.php';
require_once 'middlewares/SessionValidator.php';


//Login/Logout para Alumnos
Route::post('/students/login', [StudentLoginController::class, 'login']);
Route::post('/students/logout', [StudentLoginController::class, 'logout'], [SessionValidator::class, 'check']);

//Login/Logout para Profesores



// Rutas para Level con parámetros dinámicos 
Route::post('/levels/create', [LevelController::class, 'create'],[SessionValidator::class, 'check']);
Route::get('/levels/getOne/{id}', [LevelController::class, 'getOne'], [SessionValidator::class, 'check']);
Route::get('/levels/getAll', [LevelController::class, 'getAll'],[SessionValidator::class, 'check']);
Route::put('/levels/update/{id}', [LevelController::class, 'update'],[SessionValidator::class, 'check']);
Route::delete('/levels/deleteOne/{id}', [LevelController::class, 'deleteOne'],[SessionValidator::class, 'check']);


