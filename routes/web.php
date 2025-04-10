<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';

// Rutas para Level con parámetros dinámicos
Route::get('/level/getOne/{id}', [LevelController::class, 'getOne']);
Route::post('/level/create', [LevelController::class, 'create']);
Route::put('/level/update/{id}', [LevelController::class, 'update']);
Route::delete('/level/deleteOne/{id}', [LevelController::class, 'deleteOne']);
