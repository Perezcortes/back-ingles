<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';

// Rutas para Level con parámetros dinámicos
Route::get('/levels/getAll', [LevelController::class, 'getAll']);
Route::get('/levels/getOne/{id}', [LevelController::class, 'getOne']);
Route::post('/levels/create', [LevelController::class, 'create']);
Route::put('/levels/update/{id}', [LevelController::class, 'update']);
Route::delete('/levels/deleteOne/{id}', [LevelController::class, 'deleteOne']);
