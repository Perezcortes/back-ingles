<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';
require_once 'auth/StudentLoginController.php';


// Rutas para Level con parámetros dinámicos 
Route::post('/levels/create', [LevelController::class, 'create']);
Route::get('/levels/getOne/{id}', [LevelController::class, 'getOne']);
Route::get('/levels/getAll', [LevelController::class, 'getAll']);
Route::put('/levels/update/{id}', [LevelController::class, 'update']);
Route::delete('/levels/deleteOne/{id}', [LevelController::class, 'deleteOne']);


//Login para Alumnos
Route::post('/students/login', [StudentLoginController::class, 'login']);
Route::post('/students/logout', [StudentLoginController::class, 'logout']);
