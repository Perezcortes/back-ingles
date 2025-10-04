<?php

// Controladores necesarios
require_once 'controllers/LevelController.php';
require_once 'controllers/StudentController.php';
require_once 'controllers/UserController.php';
require_once 'controllers/auth/student/StudentLoginController.php';
require_once 'controllers/auth/user/UserLoginController.php';
require_once 'middlewares/SessionValidator.php';
require_once 'controllers/MajorController.php';
require_once 'controllers/EnglishClassController.php'; // Renombrado a EnglishClassController antes EnglishGroupController




//Login/Logout para Alumnos
Route::post('/api/v1/student/login', [StudentLoginController::class, 'login']);
Route::post('/api/v1/student/logout', [StudentLoginController::class, 'logout'], [SessionValidator::class, 'checkStudent']); // Middleware para validar sesión de alumno

//Login/Logout para Profesores/Jefe de nivel/Administrador
Route::post('/api/v1/user/login', [UserLoginController::class, 'login']);
Route::post('/api/v1/user/logout', [UserLoginController::class, 'logout'], [SessionValidator::class, 'checkUser']);


// Rutas para Level con parámetros dinámicos 
Route::post('/api/v1/level/create', [LevelController::class, 'create'],);
Route::get('/api/v1/level/getOne/{id}', [LevelController::class, 'getOne'], );
Route::get('/api/v1/level/getAll', [LevelController::class, 'getAll'],);
Route::put('/api/v1/level/update/{id}', [LevelController::class, 'update'],);
Route::delete('/api/v1/level/deleteOne/{id}', [LevelController::class, 'deleteOne'],);

// Rutas para student con parámetros dinámicos 
Route::post('/api/v1/student/create', [StudentController::class, 'create'],);
Route::get('/api/v1/student/getOne/Id/{id}', [StudentController::class, 'getStudentById'], [SessionValidator::class, 'checkUser']);
Route::post('/api/v1/student/getOne/email', [StudentController::class, 'getStudentByEmail'], [SessionValidator::class, 'checkUser']);
Route::post('/api/v1/student/getOne/matricula', [StudentController::class, 'getStudentByMatricula'], [SessionValidator::class, 'checkUser']);
Route::get('/api/v1/student/getAll', [StudentController::class, 'getAll'], [SessionValidator::class, 'checkUser']);
Route::put('/api/v1/student/update/{id}', [StudentController::class, 'update'],);
Route::delete('/api/v1/student/deleteOne/{id}', [StudentController::class, 'deleteOne'],);

// Rutas para user con parámetros dinámicos 
Route::post('/api/v1/user/create', [UserController::class, 'create'],);
Route::get('/api/v1/user/getAll', [UserController::class, 'getAll'],);
Route::get('/api/v1/user/getOne/Id/{id}', [UserController::class, 'getuserById'], );
Route::get('/api/v1/user/getOne/Email/{id}', [UserController::class, 'getuserByEmail'], );
Route::put('/api/v1/user/update/{id}', [UserController::class, 'update'],);
Route::delete('/api/v1/user/deleteOne/{id}', [UserController::class, 'deleteOne'],);


// Rutas para EnglishClass con parámetros dinámicos 
Route::post('/api/v1/englishClass/create', [EnglishClassController::class, 'create']); // Antes EnglishGroupController
Route::get('/api/v1/englishClass/getOne/{id}', [EnglishClassController::class, 'getOne']);
Route::get('/api/v1/englishClass/getByProfessor/{id_professor}', [EnglishClassController::class, 'getByProfessor']);
Route::get('/api/v1/englishClass/getByLevel/{id_level}', [EnglishClassController::class, 'getByLevel']);
Route::get('/api/v1/englishClass/getAll', [EnglishClassController::class, 'getAll']);
Route::put('/api/v1/englishClass/update/{id}', [EnglishClassController::class, 'update']);
Route::delete('/api/v1/englishClass/deleteOne/{id}', [EnglishClassController::class, 'deleteOne']);
Route::post('/api/v1/englishClass/restore/{id}', [EnglishClassController::class, 'restore']); // Para restaurar un grupo
Route::delete('/api/v1/englishClass/deletePermanent/{id}', [EnglishClassController::class, 'deletePermanent']); // Eliminar permanentemente

// Rutas para Major con parámetros dinámicos
Route::post('/api/v1/major/create', [MajorController::class, 'create']);
Route::get('/api/v1/major/getOne/{id}', [MajorController::class, 'getOne']);
Route::get('/api/v1/major/getAll', [MajorController::class, 'getAll']);
Route::put('/api/v1/major/update/{id}', [MajorController::class, 'update']);
Route::delete('/api/v1/major/deleteOne/{id}', [MajorController::class, 'deleteOne']);
Route::post('/api/v1/major/restore/{id}', [MajorController::class, 'restore']); // Ruta para restaurar un major
Route::delete('/api/v1/major/deletePermanent/{id}', [MajorController::class, 'deletePermanent']); // Ruta para eliminar permanentemente un major

// Rutas para EnglishGroup con parámetros dinámicos
Route::post('/api/v1/english-group/create', [EnglishGroupController::class, 'create']);
Route::get('/api/v1/english-group/getOne/{id}', [EnglishGroupController::class, 'getOne']);
Route::get('/api/v1/english-group/getAll', [EnglishGroupController::class, 'getAll']);
Route::get('/api/v1/english-group/getByProfessor/{id_professor}', [EnglishGroupController::class, 'getByProfessor']);
Route::get('/api/v1/api/english-group/getByLevel/{id_level}', [EnglishGroupController::class, 'getByLevel']);
Route::put('/api/v1/english-group/update/{id}', [EnglishGroupController::class, 'update']);
Route::delete('/api/v1/english-group/deleteOne/{id}', [EnglishGroupController::class, 'deleteOne']);
Route::post('/api/v1/english-group/restore/{id}', [EnglishGroupController::class, 'restore']); // Ruta para restaurar un grupo
Route::delete('/api/v1/english-group/deletePermanent/{id}', [EnglishGroupController::class, 'deletePermanent']); // Ruta para eliminar permanentemente un grupo

// Rutas para ExamType
Route::post('/api/v1/exam-type/create', [ExamTypeController::class, 'create']);
Route::get('/api/v1/exam-type/getOne/{id}', [ExamTypeController::class, 'getOne']);
Route::get('/api/v1/exam-type/getAll', [ExamTypeController::class, 'getAll']);
Route::put('/api/v1/exam-type/update/{id}', [ExamTypeController::class, 'update']);
Route::delete('/api/v1/exam-type/deleteOne/{id}', [ExamTypeController::class, 'deleteOne']);
Route::post('/api/v1/exam-type/restore/{id}', [ExamTypeController::class, 'restore']);          
Route::delete('/api/v1/exam-type/deletePermanent/{id}', [ExamTypeController::class, 'deletePermanent']); 

// Rutas para WritingTemplate con parámetros dinámicos CRUD )
Route::post('/api/v1/writing-template/create', [WritingTemplateController::class, 'create']);
Route::get('/api/v1/writing-template/getOne/{id}', [WritingTemplateController::class, 'getOne']);
Route::get('/api/v1/writing-template/getAll', [WritingTemplateController::class, 'getAll']);
Route::put('/api/v1/writing-template/update/{id}', [WritingTemplateController::class, 'update']);
Route::delete('/api/v1/writing-template/deleteOne/{id}', [WritingTemplateController::class, 'deleteOne']);

// Rutas para Image (CRUD)
Route::post('/api/v1/image/create', [ImageController::class, 'create']);
Route::get('/api/v1/image/getOne/{id}', [ImageController::class, 'getOne']);
Route::get('/api/v1/image/getAll', [ImageController::class, 'getAll']);
Route::get('/api/v1/image/getByLink/{link_image}', [ImageController::class, 'getByLink']);
Route::get('/api/v1/image/getByWritingTemplate/{id_writing_template}', [ImageController::class, 'getByWritingTemplate']);
Route::put('/api/v1/image/update/{id}', [ImageController::class, 'update']);
Route::delete('/api/v1/image/deleteOne/{id}', [ImageController::class, 'deleteOne']);
// Verificar si se queda o se va
Route::delete('/api/v1/image/truncate', [ImageController::class, 'truncate']);

// Rutas para ReadingTemplate
Route::post('/api/v1/reading-template/create', [ReadingTemplateController::class, 'create']);
Route::get('/api/v1/reading-template/getOne/{id}', [ReadingTemplateController::class, 'getOne']);
Route::get('/api/v1/reading-template/getAll', [ReadingTemplateController::class, 'getAll']);
Route::get('/api/v1/reading-template/getByLevel/{id_level}', [ReadingTemplateController::class, 'getByLevel']);
Route::put('/api/v1/reading-template/update/{id}', [ReadingTemplateController::class, 'update']);
Route::delete('/api/v1/reading-template/deleteOne/{id}', [ReadingTemplateController::class, 'deleteOne']);
Route::post('/api/v1/reading-template/restore/{id}', [ReadingTemplateController::class, 'restore']);
Route::delete('/api/v1/reading-template/deletePermanent/{id}', [ReadingTemplateController::class, 'deletePermanent']);

// Rutas para Exam
Route::post('/api/v1/exam/create', [ExamController::class, 'create']);
Route::get('/api/v1/exam/getOne/{id}', [ExamController::class, 'getOne']);
Route::get('/api/v1/exam/getAll', [ExamController::class, 'getAll']);
Route::get('/api/v1/exam/getByCreator/{who_created}', [ExamController::class, 'getByCreator']);
Route::put('/api/v1/exam/update/{id}', [ExamController::class, 'update']);
Route::delete('/api/v1/exam/deleteOne/{id}', [ExamController::class, 'deleteOne']);
Route::post('/api/v1/exam/restore/{id}', [ExamController::class, 'restore']);
Route::delete('/api/v1/exam/deletePermanent/{id}', [ExamController::class, 'deletePermanent']);

// Rutas para SignsTemplate
Route::post('/api/v1/signs-template/create', [SignsTemplateController::class, 'create']);
Route::get('/api/v1/signs-template/getOne/{id}', [SignsTemplateController::class, 'getOne']);
Route::get('/api/v1/signs-template/getAll', [SignsTemplateController::class, 'getAll']);
Route::get('/api/v1/signs-template/getByLevel/{id_level}', [SignsTemplateController::class, 'getByLevel']);
Route::put('/api/v1/signs-template/update/{id}', [SignsTemplateController::class, 'update']);
Route::delete('/api/v1/signs-template/deleteOne/{id}', [SignsTemplateController::class, 'deleteOne']);
Route::post('/api/v1/signs-template/restore/{id}', [SignsTemplateController::class, 'restore']);
Route::delete('/api/v1/signs-template/deletePermanent/{id}', [SignsTemplateController::class, 'deletePermanent']);

// Rutas para Signs
Route::post('/api/v1/signs/create', [SignsController::class, 'create']);
Route::get('/api/v1/signs/getOne/{id}', [SignsController::class, 'getOne']);
Route::get('/api/v1/signs/getAll', [SignsController::class, 'getAll']);
Route::get('/api/v1/signs/getBySignTemplate/{id_sign_template}', [SignsController::class, 'getBySignTemplate']);
Route::put('/api/v1/signs/update/{id}', [SignsController::class, 'update']);
Route::delete('/api/v1/signs/deleteOne/{id}', [SignsController::class, 'deleteOne']);
Route::post('/api/v1/signs/restore/{id}', [SignsController::class, 'restore']);
Route::delete('/api/v1/signs/deletePermanent/{id}', [SignsController::class, 'deletePermanent']);

// Rutas para SignOptions
Route::post('/api/v1/sign-options/create', [SignOptionsController::class, 'create']);
Route::get('/api/v1/sign-options/getOne/{id}', [SignOptionsController::class, 'getOne']);
Route::get('/api/v1/sign-options/getAll', [SignOptionsController::class, 'getAll']);
Route::get('/api/v1/sign-options/getBySign/{id_sign}', [SignOptionsController::class, 'getBySign']);
Route::put('/api/v1/sign-options/update/{id}', [SignOptionsController::class, 'update']);
Route::delete('/api/v1/sign-options/deleteOne/{id}', [SignOptionsController::class, 'deleteOne']);
Route::delete('/api/v1/sign-options/truncate', [SignOptionsController::class, 'truncate']);

// Rutas para SimpleMultipleTemplate
Route::post('/api/v1/simple-multiple-template/create', [SimpleMultipleTemplateController::class, 'create']);
Route::get('/api/v1/simple-multiple-template/getOne/{id}', [SimpleMultipleTemplateController::class, 'getOne']);
Route::get('/api/v1/simple-multiple-template/getAll', [SimpleMultipleTemplateController::class, 'getAll']);
Route::get('/api/v1/simple-multiple-template/getByLevel/{id_level}', [SimpleMultipleTemplateController::class, 'getByLevel']);
Route::put('/api/v1/simple-multiple-template/update/{id}', [SimpleMultipleTemplateController::class, 'update']);
Route::delete('/api/v1/simple-multiple-template/deleteOne/{id}', [SimpleMultipleTemplateController::class, 'deleteOne']);
Route::post('/api/v1/simple-multiple-template/restore/{id}', [SimpleMultipleTemplateController::class, 'restore']);
Route::delete('/api/v1/simple-multiple-template/deletePermanent/{id}', [SimpleMultipleTemplateController::class, 'deletePermanent']);

// Rutas para SimpleMultiple
Route::post('/api/v1/simple-multiple/create', [SimpleMultipleController::class, 'create']);
Route::get('/api/v1/simple-multiple/getOne/{id}', [SimpleMultipleController::class, 'getOne']);
Route::get('/api/v1/simple-multiple/getAll', [SimpleMultipleController::class, 'getAll']);
Route::get('/api/v1/simple-multiple/getByTemplate/{id_simple_multiple_template}', [SimpleMultipleController::class, 'getByTemplate']);
Route::put('/api/v1/simple-multiple/update/{id}', [SimpleMultipleController::class, 'update']);
Route::delete('/api/v1/simple-multiple/deleteOne/{id}', [SimpleMultipleController::class, 'deleteOne']);
Route::post('/api/v1/simple-multiple/restore/{id}', [SimpleMultipleController::class, 'restore']);
Route::delete('/api/v1/simple-multiple/deletePermanent/{id}', [SimpleMultipleController::class, 'deletePermanent']);

// Rutas para OptionSimpleMultiple
Route::post('/api/v1/option-simple-multiple/create', [OptionSimpleMultipleController::class, 'create']);
Route::get('/api/v1/option-simple-multiple/getOne/{id}', [OptionSimpleMultipleController::class, 'getOne']);
Route::get('/api/v1/option-simple-multiple/getAll', [OptionSimpleMultipleController::class, 'getAll']);
Route::get('/api/v1/option-simple-multiple/getBySimpleMultiple/{id_simple_multiple}', [OptionSimpleMultipleController::class, 'getBySimpleMultiple']);
Route::put('/api/v1/option-simple-multiple/update/{id}', [OptionSimpleMultipleController::class, 'update']);
Route::delete('/api/v1/option-simple-multiple/deleteOne/{id}', [OptionSimpleMultipleController::class, 'deleteOne']);
Route::delete('/api/v1/option-simple-multiple/truncate', [OptionSimpleMultipleController::class, 'truncate']);

// Rutas para MultipleMatchingTemplate
Route::post('/api/v1/multiple-matching-template/create', [MultipleMatchingTemplateController::class, 'create']);
Route::get('/api/v1/multiple-matching-template/getOne/{id}', [MultipleMatchingTemplateController::class, 'getOne']);
Route::get('/api/v1/multiple-matching-template/getAll', [MultipleMatchingTemplateController::class, 'getAll']);
Route::get('/api/v1/multiple-matching-template/getByLevel/{id_level}', [MultipleMatchingTemplateController::class, 'getByLevel']);
Route::put('/api/v1/multiple-matching-template/update/{id}', [MultipleMatchingTemplateController::class, 'update']);
Route::delete('/api/v1/multiple-matching-template/deleteOne/{id}', [MultipleMatchingTemplateController::class, 'deleteOne']);
Route::post('/api/v1/multiple-matching-template/restore/{id}', [MultipleMatchingTemplateController::class, 'restore']);
Route::delete('/api/v1/multiple-matching-template/deletePermanent/{id}', [MultipleMatchingTemplateController::class, 'deletePermanent']);

// Rutas para OptionMultipleMatching
Route::post('/api/v1/option-multiple-matching/create', [OptionMultipleMatchingController::class, 'create']);
Route::get('/api/v1/option-multiple-matching/getOne/{id}', [OptionMultipleMatchingController::class, 'getOne']);
Route::get('/api/v1/option-multiple-matching/getAll', [OptionMultipleMatchingController::class, 'getAll']);
Route::get('/api/v1/option-multiple-matching/getByTemplate/{id_multiple_matching_template}', [OptionMultipleMatchingController::class, 'getByTemplate']);
Route::put('/api/v1/option-multiple-matching/update/{id}', [OptionMultipleMatchingController::class, 'update']);
Route::delete('/api/v1/option-multiple-matching/deleteOne/{id}', [OptionMultipleMatchingController::class, 'deleteOne']);
Route::post('/api/v1/option-multiple-matching/restore/{id}', [OptionMultipleMatchingController::class, 'restore']);
Route::delete('/api/v1/option-multiple-matching/deletePermanent/{id}', [OptionMultipleMatchingController::class, 'deletePermanent']);

// Rutas para MultipleMatching
Route::post('/api/v1/multiple-matching/create', [MultipleMatchingController::class, 'create']);
Route::get('/api/v1/multiple-matching/getOne/{id}', [MultipleMatchingController::class, 'getOne']);
Route::get('/api/v1/multiple-matching/getAll', [MultipleMatchingController::class, 'getAll']);
Route::get('/api/v1/multiple-matching/getByTemplate/{id_multiple_matching_template}', [MultipleMatchingController::class, 'getByTemplate']);
Route::put('/api/v1/multiple-matching/update/{id}', [MultipleMatchingController::class, 'update']);
Route::delete('/api/v1/multiple-matching/deleteOne/{id}', [MultipleMatchingController::class, 'deleteOne']);
Route::delete('/api/v1/multiple-matching/truncate', [MultipleMatchingController::class, 'truncate']);

// Rutas para ReadingComprehensionTemplate
Route::post('/api/v1/reading-comprehension-template/create', [ReadingComprehensionTemplateController::class, 'create']);
Route::get('/api/v1/reading-comprehension-template/getOne/{id}', [ReadingComprehensionTemplateController::class, 'getOne']);
Route::get('/api/v1/reading-comprehension-template/getAll', [ReadingComprehensionTemplateController::class, 'getAll']);
Route::get('/api/v1/reading-comprehension-template/getByLevel/{id_level}', [ReadingComprehensionTemplateController::class, 'getByLevel']);
Route::put('/api/v1/reading-comprehension-template/update/{id}', [ReadingComprehensionTemplateController::class, 'update']);
Route::delete('/api/v1/reading-comprehension-template/deleteOne/{id}', [ReadingComprehensionTemplateController::class, 'deleteOne']);
Route::post('/api/v1/reading-comprehension-template/restore/{id}', [ReadingComprehensionTemplateController::class, 'restore']);
Route::delete('/api/v1/reading-comprehension-template/deletePermanent/{id}', [ReadingComprehensionTemplateController::class, 'deletePermanent']);

// Rutas para ReadingComprehension
Route::post('/api/v1/reading-comprehension/create', [ReadingComprehensionController::class, 'create']);
Route::get('/api/v1/reading-comprehension/getOne/{id}', [ReadingComprehensionController::class, 'getOne']);
Route::get('/api/v1/reading-comprehension/getAll', [ReadingComprehensionController::class, 'getAll']);
Route::get('/api/v1/reading-comprehension/getByTemplate/{id_reading_comprehension_template}', [ReadingComprehensionController::class, 'getByTemplate']);
Route::put('/api/v1/reading-comprehension/update/{id}', [ReadingComprehensionController::class, 'update']);
Route::delete('/api/v1/reading-comprehension/deleteOne/{id}', [ReadingComprehensionController::class, 'deleteOne']);
Route::post('/api/v1/reading-comprehension/restore/{id}', [ReadingComprehensionController::class, 'restore']);
Route::delete('/api/v1/reading-comprehension/deletePermanent/{id}', [ReadingComprehensionController::class, 'deletePermanent']);

// Rutas para OptionReadingComprehension
Route::post('/api/v1/option-reading-comprehension/create', [OptionReadingComprehensionController::class, 'create']);
Route::get('/api/v1/option-reading-comprehension/getOne/{id}', [OptionReadingComprehensionController::class, 'getOne']);
Route::get('/api/v1/option-reading-comprehension/getAll', [OptionReadingComprehensionController::class, 'getAll']);
Route::get('/api/v1/option-reading-comprehension/getByReading/{id_reading_comprehension}', [OptionReadingComprehensionController::class, 'getByReading']);
Route::put('/api/v1/option-reading-comprehension/update/{id}', [OptionReadingComprehensionController::class, 'update']);
Route::delete('/api/v1/option-reading-comprehension/deleteOne/{id}', [OptionReadingComprehensionController::class, 'deleteOne']);
Route::delete('/api/v1/option-reading-comprehension/truncate', [OptionReadingComprehensionController::class, 'truncate']);

// Rutas para GapFillTemplate
Route::post('/api/v1/gap-fill-template/create', [GapFillTemplateController::class, 'create']);
Route::get('/api/v1/gap-fill-template/getOne/{id}', [GapFillTemplateController::class, 'getOne']);
Route::get('/api/v1/gap-fill-template/getAll', [GapFillTemplateController::class, 'getAll']);
Route::get('/api/v1/gap-fill-template/getByLevel/{id_level}', [GapFillTemplateController::class, 'getByLevel']);
Route::put('/api/v1/gap-fill-template/update/{id}', [GapFillTemplateController::class, 'update']);
Route::delete('/api/v1/gap-fill-template/deleteOne/{id}', [GapFillTemplateController::class, 'deleteOne']);
Route::post('/api/v1/gap-fill-template/restore/{id}', [GapFillTemplateController::class, 'restore']);
Route::delete('/api/v1/gap-fill-template/deletePermanent/{id}', [GapFillTemplateController::class, 'deletePermanent']);

// Rutas para GapFillOption
Route::post('/api/v1/gap-fill-option/create', [GapFillOptionController::class, 'create']);
Route::get('/api/v1/gap-fill-option/getOne/{id}', [GapFillOptionController::class, 'getOne']);
Route::get('/api/v1/gap-fill-option/getAll', [GapFillOptionController::class, 'getAll']);
Route::get('/api/v1/gap-fill-option/getByTemplate/{id_gap_fill_template}', [GapFillOptionController::class, 'getByTemplate']);
Route::put('/api/v1/gap-fill-option/update/{id}', [GapFillOptionController::class, 'update']);
Route::delete('/api/v1/gap-fill-option/deleteOne/{id}', [GapFillOptionController::class, 'deleteOne']);
Route::delete('/api/v1/gap-fill-option/truncate', [GapFillOptionController::class, 'truncate']);

// Rutas para GapFillQuestion
Route::post('/api/v1/gap-fill-question/create', [GapFillQuestionController::class, 'create']);
Route::get('/api/v1/gap-fill-question/getOne/{id}', [GapFillQuestionController::class, 'getOne']);
Route::get('/api/v1/gap-fill-question/getAll', [GapFillQuestionController::class, 'getAll']);
Route::get('/api/v1/gap-fill-question/getByTemplate/{id_gap_fill_template}', [GapFillQuestionController::class, 'getByTemplate']);
Route::put('/api/v1/gap-fill-question/update/{id}', [GapFillQuestionController::class, 'update']);
Route::delete('/api/v1/gap-fill-question/deleteOne/{id}', [GapFillQuestionController::class, 'deleteOne']);
Route::delete('/api/v1/gap-fill-question/truncate', [GapFillQuestionController::class, 'truncate']);

// Rutas para OpenClozeTemplate
Route::post('/api/v1/open-cloze-template/create', [OpenClozeTemplateController::class, 'create']);
Route::get('/api/v1/open-cloze-template/getOne/{id}', [OpenClozeTemplateController::class, 'getOne']);
Route::get('/api/v1/open-cloze-template/getAll', [OpenClozeTemplateController::class, 'getAll']);
Route::get('/api/v1/open-cloze-template/getByLevel/{id_level}', [OpenClozeTemplateController::class, 'getByLevel']);
Route::put('/api/v1/open-cloze-template/update/{id}', [OpenClozeTemplateController::class, 'update']);
Route::delete('/api/v1/open-cloze-template/deleteOne/{id}', [OpenClozeTemplateController::class, 'deleteOne']);
Route::post('/api/v1/open-cloze-template/restore/{id}', [OpenClozeTemplateController::class, 'restore']);
Route::delete('/api/v1/open-cloze-template/deletePermanent/{id}', [OpenClozeTemplateController::class, 'deletePermanent']);

// Rutas para OpenClozeQuestion
Route::post('/api/v1/open-cloze-question/create', [OpenClozeQuestionController::class, 'create']);
Route::get('/api/v1/open-cloze-question/getOne/{id}', [OpenClozeQuestionController::class, 'getOne']);
Route::get('/api/v1/open-cloze-question/getAll', [OpenClozeQuestionController::class, 'getAll']);
Route::get('/api/v1/open-cloze-question/getByTemplate/{id_open_cloze_template}', [OpenClozeQuestionController::class, 'getByTemplate']);
Route::put('/api/v1/open-cloze-question/update/{id}', [OpenClozeQuestionController::class, 'update']);
Route::delete('/api/v1/open-cloze-question/deleteOne/{id}', [OpenClozeQuestionController::class, 'deleteOne']);
Route::post('/api/v1/open-cloze-question/restore/{id}', [OpenClozeQuestionController::class, 'restore']);
Route::delete('/api/v1/open-cloze-question/deletePermanent/{id}', [OpenClozeQuestionController::class, 'deletePermanent']);

// Rutas para GeneratorOpenClozeQuestionKey
Route::post('/api/v1/generator-open-cloze-question-keys/create', [GeneratorOpenClozeQuestionKeyController::class, 'create']);
Route::get('/api/v1/generator-open-cloze-question-keys/getOne/{id}', [GeneratorOpenClozeQuestionKeyController::class, 'getOne']);
Route::get('/api/v1/generator-open-cloze-question-keys/getAll', [GeneratorOpenClozeQuestionKeyController::class, 'getAll']);
Route::get('/api/v1/generator-open-cloze-question-keys/getByQuestion/{id_open_cloze_question}', [GeneratorOpenClozeQuestionKeyController::class, 'getByQuestion']);
Route::put('/api/v1/generator-open-cloze-question-keys/update/{id}', [GeneratorOpenClozeQuestionKeyController::class, 'update']);
Route::delete('/api/v1/generator-open-cloze-question-keys/deleteOne/{id}', [GeneratorOpenClozeQuestionKeyController::class, 'deleteOne']);
Route::post('/api/v1/generator-open-cloze-question-keys/restore/{id}', [GeneratorOpenClozeQuestionKeyController::class, 'restore']);
Route::delete('/api/v1/generator-open-cloze-question-keys/deletePermanent/{id}', [GeneratorOpenClozeQuestionKeyController::class, 'deletePermanent']);
Route::delete('/api/v1/generator-open-cloze-question-keys/truncate', [GeneratorOpenClozeQuestionKeyController::class, 'truncate']);

// Rutas para OpenClozeOption
Route::post('/api/v1/open-cloze-option/create', [OpenClozeOptionController::class, 'create']);
Route::get('/api/v1/open-cloze-option/getOne/{id}', [OpenClozeOptionController::class, 'getOne']);
Route::get('/api/v1/open-cloze-option/getAll', [OpenClozeOptionController::class, 'getAll']);
Route::get('/api/v1/open-cloze-option/getByGeneratorKey/{id_generator_open_cloze_question_keys}', [OpenClozeOptionController::class, 'getByGeneratorKey']);
Route::put('/api/v1/open-cloze-option/update/{id}', [OpenClozeOptionController::class, 'update']);
Route::delete('/api/v1/open-cloze-option/deleteOne/{id}', [OpenClozeOptionController::class, 'deleteOne']);
Route::delete('/api/v1/open-cloze-option/truncate', [OpenClozeOptionController::class, 'truncate']);

// Rutas para TemplateMultipleChoiceCloze
Route::post('/api/v1/template-mcc/create', [TemplateMultipleChoiceClozeController::class, 'create']);
Route::get('/api/v1/template-mcc/getOne/{id}', [TemplateMultipleChoiceClozeController::class, 'getOne']);
Route::get('/api/v1/template-mcc/getAll', [TemplateMultipleChoiceClozeController::class, 'getAll']);
Route::get('/api/v1/template-mcc/getByLevel/{id_level}', [TemplateMultipleChoiceClozeController::class, 'getByLevel']);
Route::put('/api/v1/template-mcc/update/{id}', [TemplateMultipleChoiceClozeController::class, 'update']);
Route::delete('/api/v1/template-mcc/deleteOne/{id}', [TemplateMultipleChoiceClozeController::class, 'deleteOne']);
Route::post('/api/v1/template-mcc/restore/{id}', [TemplateMultipleChoiceClozeController::class, 'restore']);
Route::delete('/api/v1/template-mcc/deletePermanent/{id}', [TemplateMultipleChoiceClozeController::class, 'deletePermanent']);
Route::delete('/api/v1/template-mcc/truncate', [TemplateMultipleChoiceClozeController::class, 'truncate']); // si agregas el método en el controller

// Rutas para MultipleChoiceCloze
Route::post('/api/v1/multiple-choice-cloze/create', [MultipleChoiceClozeController::class, 'create']);
Route::get('/api/v1/multiple-choice-cloze/getOne/{id}', [MultipleChoiceClozeController::class, 'getOne']);
Route::get('/api/v1/multiple-choice-cloze/getAll', [MultipleChoiceClozeController::class, 'getAll']);
Route::get('/api/v1/multiple-choice-cloze/getByTemplate/{id_template_multiple_choice_cloze}', [MultipleChoiceClozeController::class, 'getByTemplate']);
Route::put('/api/v1/multiple-choice-cloze/update/{id}', [MultipleChoiceClozeController::class, 'update']);
Route::delete('/api/v1/multiple-choice-cloze/deleteOne/{id}', [MultipleChoiceClozeController::class, 'deleteOne']);
Route::post('/api/v1/multiple-choice-cloze/restore/{id}', [MultipleChoiceClozeController::class, 'restore']);
Route::delete('/api/v1/multiple-choice-cloze/deletePermanent/{id}', [MultipleChoiceClozeController::class, 'deletePermanent']);
Route::delete('/api/v1/multiple-choice-cloze/truncate', [MultipleChoiceClozeController::class, 'truncate']);

// Rutas para QuestionNumber
Route::post('/api/v1/question-number/create', [QuestionNumberController::class, 'create']);
Route::get('/api/v1/question-number/getOne/{id}', [QuestionNumberController::class, 'getOne']);
Route::get('/api/v1/question-number/getAll', [QuestionNumberController::class, 'getAll']);
Route::get('/api/v1/question-number/getByMultipleChoiceCloze/{id_multiple_choice_cloze}', [QuestionNumberController::class, 'getByMultipleChoiceCloze']);
Route::put('/api/v1/question-number/update/{id}', [QuestionNumberController::class, 'update']);
Route::delete('/api/v1/question-number/deleteOne/{id}', [QuestionNumberController::class, 'deleteOne']);
Route::post('/api/v1/question-number/restore/{id}', [QuestionNumberController::class, 'restore']);
Route::delete('/api/v1/question-number/deletePermanent/{id}', [QuestionNumberController::class, 'deletePermanent']);
Route::delete('/api/v1/question-number/truncate', [QuestionNumberController::class, 'truncate']);

// Rutas para OptionsMultipleChoiceCloze
Route::post('/api/v1/options-multiple-choice-cloze/create', [OptionsMultipleChoiceClozeController::class, 'create']);
Route::get('/api/v1/options-multiple-choice-cloze/getOne/{id}', [OptionsMultipleChoiceClozeController::class, 'getOne']);
Route::get('/api/v1/options-multiple-choice-cloze/getAll', [OptionsMultipleChoiceClozeController::class, 'getAll']);
Route::get('/api/v1/options-multiple-choice-cloze/getByQuestionNumber/{id_question_number}', [OptionsMultipleChoiceClozeController::class, 'getByQuestionNumber']);
Route::put('/api/v1/options-multiple-choice-cloze/update/{id}', [OptionsMultipleChoiceClozeController::class, 'update']);
Route::delete('/api/v1/options-multiple-choice-cloze/deleteOne/{id}', [OptionsMultipleChoiceClozeController::class, 'deleteOne']);
Route::delete('/api/v1/options-multiple-choice-cloze/truncate', [OptionsMultipleChoiceClozeController::class, 'truncate']);
