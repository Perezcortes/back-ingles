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

// Rutas para EnglishGroup con parámetros dinámicos
Route::post('/english-group/create', [EnglishGroupController::class, 'create']);
Route::get('/english-group/getOne/{id}', [EnglishGroupController::class, 'getOne']);
Route::get('/english-group/getAll', [EnglishGroupController::class, 'getAll']);
Route::get('/english-group/getByProfessor/{id_professor}', [EnglishGroupController::class, 'getByProfessor']);
Route::get('/english-group/getByLevel/{id_level}', [EnglishGroupController::class, 'getByLevel']);
Route::put('/english-group/update/{id}', [EnglishGroupController::class, 'update']);
Route::delete('/english-group/deleteOne/{id}', [EnglishGroupController::class, 'deleteOne']);
Route::post('/english-group/restore/{id}', [EnglishGroupController::class, 'restore']); // Ruta para restaurar un grupo
Route::delete('/english-group/deletePermanent/{id}', [EnglishGroupController::class, 'deletePermanent']); // Ruta para eliminar permanentemente un grupo

// Rutas para ExamType
Route::post('/exam-type/create', [ExamTypeController::class, 'create']);
Route::get('/exam-type/getOne/{id}', [ExamTypeController::class, 'getOne']);
Route::get('/exam-type/getAll', [ExamTypeController::class, 'getAll']);
Route::put('/exam-type/update/{id}', [ExamTypeController::class, 'update']);
Route::delete('/exam-type/deleteOne/{id}', [ExamTypeController::class, 'deleteOne']);
Route::post('/exam-type/restore/{id}', [ExamTypeController::class, 'restore']);          
Route::delete('/exam-type/deletePermanent/{id}', [ExamTypeController::class, 'deletePermanent']); 

// Rutas para WritingTemplate con parámetros dinámicos CRUD )
Route::post('/writing-template/create', [WritingTemplateController::class, 'create']);
Route::get('/writing-template/getOne/{id}', [WritingTemplateController::class, 'getOne']);
Route::get('/writing-template/getAll', [WritingTemplateController::class, 'getAll']);
Route::put('/writing-template/update/{id}', [WritingTemplateController::class, 'update']);
Route::delete('/writing-template/deleteOne/{id}', [WritingTemplateController::class, 'deleteOne']);

// Rutas para Image (CRUD)
Route::post('/image/create', [ImageController::class, 'create']);
Route::get('/image/getOne/{id}', [ImageController::class, 'getOne']);
Route::get('/image/getAll', [ImageController::class, 'getAll']);
Route::get('/image/getByLink/{link_image}', [ImageController::class, 'getByLink']);
Route::get('/image/getByWritingTemplate/{id_writing_template}', [ImageController::class, 'getByWritingTemplate']);
Route::put('/image/update/{id}', [ImageController::class, 'update']);
Route::delete('/image/deleteOne/{id}', [ImageController::class, 'deleteOne']);
// Verificar si se queda o se va
Route::delete('/image/truncate', [ImageController::class, 'truncate']);

// Rutas para ReadingTemplate
Route::post('/reading-template/create', [ReadingTemplateController::class, 'create']);
Route::get('/reading-template/getOne/{id}', [ReadingTemplateController::class, 'getOne']);
Route::get('/reading-template/getAll', [ReadingTemplateController::class, 'getAll']);
Route::get('/reading-template/getByLevel/{id_level}', [ReadingTemplateController::class, 'getByLevel']);
Route::put('/reading-template/update/{id}', [ReadingTemplateController::class, 'update']);
Route::delete('/reading-template/deleteOne/{id}', [ReadingTemplateController::class, 'deleteOne']);
Route::post('/reading-template/restore/{id}', [ReadingTemplateController::class, 'restore']);
Route::delete('/reading-template/deletePermanent/{id}', [ReadingTemplateController::class, 'deletePermanent']);

// Rutas para Exam
Route::post('/exam/create', [ExamController::class, 'create']);
Route::get('/exam/getOne/{id}', [ExamController::class, 'getOne']);
Route::get('/exam/getAll', [ExamController::class, 'getAll']);
Route::get('/exam/getByCreator/{who_created}', [ExamController::class, 'getByCreator']);
Route::put('/exam/update/{id}', [ExamController::class, 'update']);
Route::delete('/exam/deleteOne/{id}', [ExamController::class, 'deleteOne']);
Route::post('/exam/restore/{id}', [ExamController::class, 'restore']);
Route::delete('/exam/deletePermanent/{id}', [ExamController::class, 'deletePermanent']);

// Rutas para SignsTemplate
Route::post('/signs-template/create', [SignsTemplateController::class, 'create']);
Route::get('/signs-template/getOne/{id}', [SignsTemplateController::class, 'getOne']);
Route::get('/signs-template/getAll', [SignsTemplateController::class, 'getAll']);
Route::get('/signs-template/getByLevel/{id_level}', [SignsTemplateController::class, 'getByLevel']);
Route::put('/signs-template/update/{id}', [SignsTemplateController::class, 'update']);
Route::delete('/signs-template/deleteOne/{id}', [SignsTemplateController::class, 'deleteOne']);
Route::post('/signs-template/restore/{id}', [SignsTemplateController::class, 'restore']);
Route::delete('/signs-template/deletePermanent/{id}', [SignsTemplateController::class, 'deletePermanent']);

// Rutas para Signs
Route::post('/signs/create', [SignsController::class, 'create']);
Route::get('/signs/getOne/{id}', [SignsController::class, 'getOne']);
Route::get('/signs/getAll', [SignsController::class, 'getAll']);
Route::get('/signs/getBySignTemplate/{id_sign_template}', [SignsController::class, 'getBySignTemplate']);
Route::put('/signs/update/{id}', [SignsController::class, 'update']);
Route::delete('/signs/deleteOne/{id}', [SignsController::class, 'deleteOne']);
Route::post('/signs/restore/{id}', [SignsController::class, 'restore']);
Route::delete('/signs/deletePermanent/{id}', [SignsController::class, 'deletePermanent']);

// Rutas para SignOptions
Route::post('/sign-options/create', [SignOptionsController::class, 'create']);
Route::get('/sign-options/getOne/{id}', [SignOptionsController::class, 'getOne']);
Route::get('/sign-options/getAll', [SignOptionsController::class, 'getAll']);
Route::get('/sign-options/getBySign/{id_sign}', [SignOptionsController::class, 'getBySign']);
Route::put('/sign-options/update/{id}', [SignOptionsController::class, 'update']);
Route::delete('/sign-options/deleteOne/{id}', [SignOptionsController::class, 'deleteOne']);
Route::delete('/sign-options/truncate', [SignOptionsController::class, 'truncate']);

// Rutas para SimpleMultipleTemplate
Route::post('/simple-multiple-template/create', [SimpleMultipleTemplateController::class, 'create']);
Route::get('/simple-multiple-template/getOne/{id}', [SimpleMultipleTemplateController::class, 'getOne']);
Route::get('/simple-multiple-template/getAll', [SimpleMultipleTemplateController::class, 'getAll']);
Route::get('/simple-multiple-template/getByLevel/{id_level}', [SimpleMultipleTemplateController::class, 'getByLevel']);
Route::put('/simple-multiple-template/update/{id}', [SimpleMultipleTemplateController::class, 'update']);
Route::delete('/simple-multiple-template/deleteOne/{id}', [SimpleMultipleTemplateController::class, 'deleteOne']);
Route::post('/simple-multiple-template/restore/{id}', [SimpleMultipleTemplateController::class, 'restore']);
Route::delete('/simple-multiple-template/deletePermanent/{id}', [SimpleMultipleTemplateController::class, 'deletePermanent']);

// Rutas para SimpleMultiple
Route::post('/simple-multiple/create', [SimpleMultipleController::class, 'create']);
Route::get('/simple-multiple/getOne/{id}', [SimpleMultipleController::class, 'getOne']);
Route::get('/simple-multiple/getAll', [SimpleMultipleController::class, 'getAll']);
Route::get('/simple-multiple/getByTemplate/{id_simple_multiple_template}', [SimpleMultipleController::class, 'getByTemplate']);
Route::put('/simple-multiple/update/{id}', [SimpleMultipleController::class, 'update']);
Route::delete('/simple-multiple/deleteOne/{id}', [SimpleMultipleController::class, 'deleteOne']);
Route::post('/simple-multiple/restore/{id}', [SimpleMultipleController::class, 'restore']);
Route::delete('/simple-multiple/deletePermanent/{id}', [SimpleMultipleController::class, 'deletePermanent']);

// Rutas para OptionSimpleMultiple
Route::post('/option-simple-multiple/create', [OptionSimpleMultipleController::class, 'create']);
Route::get('/option-simple-multiple/getOne/{id}', [OptionSimpleMultipleController::class, 'getOne']);
Route::get('/option-simple-multiple/getAll', [OptionSimpleMultipleController::class, 'getAll']);
Route::get('/option-simple-multiple/getBySimpleMultiple/{id_simple_multiple}', [OptionSimpleMultipleController::class, 'getBySimpleMultiple']);
Route::put('/option-simple-multiple/update/{id}', [OptionSimpleMultipleController::class, 'update']);
Route::delete('/option-simple-multiple/deleteOne/{id}', [OptionSimpleMultipleController::class, 'deleteOne']);
Route::delete('/option-simple-multiple/truncate', [OptionSimpleMultipleController::class, 'truncate']);

// Rutas para MultipleMatchingTemplate
Route::post('/multiple-matching-template/create', [MultipleMatchingTemplateController::class, 'create']);
Route::get('/multiple-matching-template/getOne/{id}', [MultipleMatchingTemplateController::class, 'getOne']);
Route::get('/multiple-matching-template/getAll', [MultipleMatchingTemplateController::class, 'getAll']);
Route::get('/multiple-matching-template/getByLevel/{id_level}', [MultipleMatchingTemplateController::class, 'getByLevel']);
Route::put('/multiple-matching-template/update/{id}', [MultipleMatchingTemplateController::class, 'update']);
Route::delete('/multiple-matching-template/deleteOne/{id}', [MultipleMatchingTemplateController::class, 'deleteOne']);
Route::post('/multiple-matching-template/restore/{id}', [MultipleMatchingTemplateController::class, 'restore']);
Route::delete('/multiple-matching-template/deletePermanent/{id}', [MultipleMatchingTemplateController::class, 'deletePermanent']);

// Rutas para OptionMultipleMatching
Route::post('/option-multiple-matching/create', [OptionMultipleMatchingController::class, 'create']);
Route::get('/option-multiple-matching/getOne/{id}', [OptionMultipleMatchingController::class, 'getOne']);
Route::get('/option-multiple-matching/getAll', [OptionMultipleMatchingController::class, 'getAll']);
Route::get('/option-multiple-matching/getByTemplate/{id_multiple_matching_template}', [OptionMultipleMatchingController::class, 'getByTemplate']);
Route::put('/option-multiple-matching/update/{id}', [OptionMultipleMatchingController::class, 'update']);
Route::delete('/option-multiple-matching/deleteOne/{id}', [OptionMultipleMatchingController::class, 'deleteOne']);
Route::post('/option-multiple-matching/restore/{id}', [OptionMultipleMatchingController::class, 'restore']);
Route::delete('/option-multiple-matching/deletePermanent/{id}', [OptionMultipleMatchingController::class, 'deletePermanent']);

// Rutas para MultipleMatching
Route::post('/multiple-matching/create', [MultipleMatchingController::class, 'create']);
Route::get('/multiple-matching/getOne/{id}', [MultipleMatchingController::class, 'getOne']);
Route::get('/multiple-matching/getAll', [MultipleMatchingController::class, 'getAll']);
Route::get('/multiple-matching/getByTemplate/{id_multiple_matching_template}', [MultipleMatchingController::class, 'getByTemplate']);
Route::put('/multiple-matching/update/{id}', [MultipleMatchingController::class, 'update']);
Route::delete('/multiple-matching/deleteOne/{id}', [MultipleMatchingController::class, 'deleteOne']);
Route::delete('/multiple-matching/truncate', [MultipleMatchingController::class, 'truncate']);

// Rutas para ReadingComprehensionTemplate
Route::post('/reading-comprehension-template/create', [ReadingComprehensionTemplateController::class, 'create']);
Route::get('/reading-comprehension-template/getOne/{id}', [ReadingComprehensionTemplateController::class, 'getOne']);
Route::get('/reading-comprehension-template/getAll', [ReadingComprehensionTemplateController::class, 'getAll']);
Route::get('/reading-comprehension-template/getByLevel/{id_level}', [ReadingComprehensionTemplateController::class, 'getByLevel']);
Route::put('/reading-comprehension-template/update/{id}', [ReadingComprehensionTemplateController::class, 'update']);
Route::delete('/reading-comprehension-template/deleteOne/{id}', [ReadingComprehensionTemplateController::class, 'deleteOne']);
Route::post('/reading-comprehension-template/restore/{id}', [ReadingComprehensionTemplateController::class, 'restore']);
Route::delete('/reading-comprehension-template/deletePermanent/{id}', [ReadingComprehensionTemplateController::class, 'deletePermanent']);

// Rutas para ReadingComprehension
Route::post('/reading-comprehension/create', [ReadingComprehensionController::class, 'create']);
Route::get('/reading-comprehension/getOne/{id}', [ReadingComprehensionController::class, 'getOne']);
Route::get('/reading-comprehension/getAll', [ReadingComprehensionController::class, 'getAll']);
Route::get('/reading-comprehension/getByTemplate/{id_reading_comprehension_template}', [ReadingComprehensionController::class, 'getByTemplate']);
Route::put('/reading-comprehension/update/{id}', [ReadingComprehensionController::class, 'update']);
Route::delete('/reading-comprehension/deleteOne/{id}', [ReadingComprehensionController::class, 'deleteOne']);
Route::post('/reading-comprehension/restore/{id}', [ReadingComprehensionController::class, 'restore']);
Route::delete('/reading-comprehension/deletePermanent/{id}', [ReadingComprehensionController::class, 'deletePermanent']);

// Rutas para OptionReadingComprehension
Route::post('/option-reading-comprehension/create', [OptionReadingComprehensionController::class, 'create']);
Route::get('/option-reading-comprehension/getOne/{id}', [OptionReadingComprehensionController::class, 'getOne']);
Route::get('/option-reading-comprehension/getAll', [OptionReadingComprehensionController::class, 'getAll']);
Route::get('/option-reading-comprehension/getByReading/{id_reading_comprehension}', [OptionReadingComprehensionController::class, 'getByReading']);
Route::put('/option-reading-comprehension/update/{id}', [OptionReadingComprehensionController::class, 'update']);
Route::delete('/option-reading-comprehension/deleteOne/{id}', [OptionReadingComprehensionController::class, 'deleteOne']);
Route::delete('/option-reading-comprehension/truncate', [OptionReadingComprehensionController::class, 'truncate']);

// Rutas para GapFillTemplate
Route::post('/gap-fill-template/create', [GapFillTemplateController::class, 'create']);
Route::get('/gap-fill-template/getOne/{id}', [GapFillTemplateController::class, 'getOne']);
Route::get('/gap-fill-template/getAll', [GapFillTemplateController::class, 'getAll']);
Route::get('/gap-fill-template/getByLevel/{id_level}', [GapFillTemplateController::class, 'getByLevel']);
Route::put('/gap-fill-template/update/{id}', [GapFillTemplateController::class, 'update']);
Route::delete('/gap-fill-template/deleteOne/{id}', [GapFillTemplateController::class, 'deleteOne']);
Route::post('/gap-fill-template/restore/{id}', [GapFillTemplateController::class, 'restore']);
Route::delete('/gap-fill-template/deletePermanent/{id}', [GapFillTemplateController::class, 'deletePermanent']);

// Rutas para GapFillOption
Route::post('/gap-fill-option/create', [GapFillOptionController::class, 'create']);
Route::get('/gap-fill-option/getOne/{id}', [GapFillOptionController::class, 'getOne']);
Route::get('/gap-fill-option/getAll', [GapFillOptionController::class, 'getAll']);
Route::get('/gap-fill-option/getByTemplate/{id_gap_fill_template}', [GapFillOptionController::class, 'getByTemplate']);
Route::put('/gap-fill-option/update/{id}', [GapFillOptionController::class, 'update']);
Route::delete('/gap-fill-option/deleteOne/{id}', [GapFillOptionController::class, 'deleteOne']);
Route::delete('/gap-fill-option/truncate', [GapFillOptionController::class, 'truncate']);

// Rutas para GapFillQuestion
Route::post('/gap-fill-question/create', [GapFillQuestionController::class, 'create']);
Route::get('/gap-fill-question/getOne/{id}', [GapFillQuestionController::class, 'getOne']);
Route::get('/gap-fill-question/getAll', [GapFillQuestionController::class, 'getAll']);
Route::get('/gap-fill-question/getByTemplate/{id_gap_fill_template}', [GapFillQuestionController::class, 'getByTemplate']);
Route::put('/gap-fill-question/update/{id}', [GapFillQuestionController::class, 'update']);
Route::delete('/gap-fill-question/deleteOne/{id}', [GapFillQuestionController::class, 'deleteOne']);
Route::delete('/gap-fill-question/truncate', [GapFillQuestionController::class, 'truncate']);
