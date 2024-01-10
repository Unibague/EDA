<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Laravel\Socialite\Facades\Socialite;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/


/* >>>>>>>>>>>>>>>>>>>>>>>  Assessments routes >>>>>>>><<<<<< */
Route::resource('api/assessments', \App\Http\Controllers\AssessmentController::class, [
    'as' => 'api'
])->middleware('auth');


/* >>>>>>>>>>>>>>>>>>>>>>>  Assessment Periods routes >>>>>>>><<<<<< */
Route::inertia('/assessmentPeriods', 'AssessmentPeriods/Index')->middleware(['auth', 'isAdmin'])->name('assessmentPeriods.index.view');
Route::resource('api/assessmentPeriods', \App\Http\Controllers\AssessmentPeriodController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/assessmentPeriods/{assessmentPeriod}/setActive', [\App\Http\Controllers\AssessmentPeriodController::class, 'setActive'])->middleware(['auth', 'isAdmin'])->name('api.assessmentPeriods.setActive');

/* >>>>>Commitments routes <<<<<< */
Route::inertia('/commitments', 'Commitments/Index')->name('commitments.index.view');
//Route::get('/commitments', [\App\Http\Controllers\Roles\RoleController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('roles.index');
//Route::resource('api/roles', \App\Http\Controllers\Roles\ApiRoleController::class, [
//    'as' => 'api'
//])->middleware('auth');

/* >>>>>Competences routes <<<<<< */
Route::inertia('/competences', 'Competences/Index')->middleware(['auth', 'isAdmin'])->name('competences.index.view');
Route::resource('api/competences', \App\Http\Controllers\CompetenceController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('competences/updateOrder', [\App\Http\Controllers\CompetenceController::class, 'updateOrder'])->middleware('auth')->name('competences.updateOrder');

/* >>>>>Dependencies routes <<<<<< */
Route::inertia('/dependencies', 'Dependencies/Index')->middleware(['auth', 'isAdmin'])->name('dependencies.index.view');
Route::resource('api/dependencies', \App\Http\Controllers\DependencyController::class, [
    'as' => 'api'])->middleware('auth');
Route::get('/api/dependencies/{dependency}', [\App\Http\Controllers\DependencyController::class, 'edit'])->middleware(['auth'])->name('api.dependencies.edit');
Route::get('/api/dependencies/{dependency}/assessmentStatus', [\App\Http\Controllers\DependencyController::class, 'assessmentStatus'])
    ->middleware(['auth', 'isAdminOrDependencyAdmin'])->name('api.dependencies.assessmentStatus');
Route::post('/api/dependencies/sync', [\App\Http\Controllers\DependencyController::class, 'sync'])->middleware(['auth'])->name('api.dependencies.sync');
Route::get('/api/dependencies/{dependency}/admins', [\App\Http\Controllers\DependencyController::class, 'getAdmins'])->middleware(['auth'])->name('api.dependencies.admins');
Route::inertia('/dependencies/admin/landing', 'Dependencies/LandingMultipleDependenciesAdmin')->middleware(['auth'])->name('dependencies.landing');


/* >>>>>DependencyAdmin routes <<<<<< */
Route::resource('api/{dependency}/dependencyAdmins', \App\Http\Controllers\DependencyAdminController::class, [
    'as' => 'api'])->middleware('auth');

/* >>>>>>>>>>>>>>>>>>>>>>>  ExternalClients routes >>>>>>>><<<<<< */
Route::inertia('/externalClients', 'ExternalClients/Index')->middleware(['auth', 'isAdmin'])->name('externalClients.index.view');
Route::resource('api/externalClients', \App\Http\Controllers\ExternalClientController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('/api/externalClient/updatePassword', [\App\Http\Controllers\ExternalClientController::class, 'updatePassword'])->middleware(['auth'])
    ->name('api.externalClients.updatePassword');


/* >>>>>>>>>>>>>>>>>>>>> Forms routes <<<<<<<<<<<<<<<<<<<< */
Route::get('api/forms/withoutQuestions', [\App\Http\Controllers\FormController::class, 'getWithoutQuestions'])->middleware(['auth', 'isAdmin'])->name('api.forms.withoutQuestions');
Route::get('api/forms/copyFromPeriod/{assessmentPeriod}', [\App\Http\Controllers\FormController::class, 'copyFromPeriod'])->name('api.forms.copyFromPeriod')
    ->middleware(['auth', 'isAdmin']);
Route::inertia('/forms', 'Forms/Index')->middleware(['auth', 'isAdmin'])->name('forms.index.view');
Route::inertia('/forms/{form}', 'Forms/Show')->middleware(['auth', 'isAdmin'])->name('forms.show.view');
Route::resource('api/forms', \App\Http\Controllers\FormController::class, [
    'as' => 'api'
])->middleware('auth');
Route::get('borrarForm/{form}', [\App\Http\Controllers\FormController::class, 'destroy']);
Route::post('api/forms/{form}/copy', [\App\Http\Controllers\FormController::class, 'copy'])->name('api.forms.copy')->middleware(['auth']);
Route::patch('api/forms/{form}/formQuestions', [\App\Http\Controllers\FormQuestionController::class, 'storeOrUpdate'])->name('api.forms.questions.store')->middleware(['auth']);
Route::get('api/forms/{form}/formQuestions', [\App\Http\Controllers\FormQuestionController::class, 'getByFormId'])->name('api.forms.questions.show')->middleware(['auth']);


/* >>>>>FunctionaryProfile routes <<<<<< */
Route::inertia('/functionaries', 'Functionaries/Index')->middleware(['auth', 'isAdmin'])->name('functionaries.index.view');
Route::post('/api/functionaryProfiles/sync', [\App\Http\Controllers\FunctionaryProfileController::class, 'sync'])->middleware(['auth'])
    ->name('api.functionaryProfiles.sync');
Route::post('/api/functionaryProfiles/{functionaryProfile}/changeStatus', [\App\Http\Controllers\FunctionaryProfileController::class, 'changeStatus'])->middleware(['auth'])
    ->name('api.functionaryProfiles.changeStatus');
Route::resource('api/functionaries', \App\Http\Controllers\FunctionaryProfileController::class, [
    'as' => 'api'
])->middleware('auth');
Route::get('/api/{dependency}/functionaryProfiles/{functionaryProfile}', [\App\Http\Controllers\FunctionaryProfileController::class, 'edit'])
    ->middleware(['auth'])->name('api.functionaryProfiles.edit');
Route::get('/functionaries/changes', [\App\Http\Controllers\FunctionaryProfileController::class, 'getPendingChanges'])->middleware(['auth'])->name('functionaryProfiles.pendingChanges');
Route::post('/functionaries/changes/{userId}/approve',[\App\Http\Controllers\FunctionaryProfileController::class, 'approveChange'])
    ->middleware(['auth'])->name('functionaryProfiles.change.approve');
Route::post('/functionaries/changes/{userId}/delete',[\App\Http\Controllers\FunctionaryProfileController::class, 'declineChange'])
    ->middleware(['auth'])->name('functionaryProfiles.change.decline');



/* >>>>>>>>>>>>>>>>>>>>>>>  Positions routes >>>>>>>><<<<<< */
Route::inertia('/positions', 'Positions/Index')->middleware(['auth', 'isAdmin'])->name('positions.index.view');
Route::get('api/positions/ableToAssign/', [\App\Http\Controllers\PositionController::class, 'ableToAssign'])->middleware(['auth', 'isAdmin'])->name('api.positions.ableToAssign');
Route::resource('api/positions', \App\Http\Controllers\PositionController::class, [
    'as' => 'api'
])->middleware('auth');

/* >>>>>>>>>>>>>>>>>>>>>>>  Positions Assignment routes >>>>>>>><<<<<< */
Route::inertia('/positions/assignment', 'PositionAssignment/Index')->middleware(['auth', 'isAdmin'])->name('positions.assignment.index.view');
Route::resource('api/positionsAssignment', \App\Http\Controllers\PositionAssignmentController::class, [
    'as' => 'api'
])->middleware('auth');
Route::post('api/positionsAssignment/create', [\App\Http\Controllers\PositionAssignmentController::class, 'createAssignment'])->middleware(['auth', 'isAdmin'])->name('api.positionsAssignment.create');
Route::post('api/positionsAssignment/destroy', [\App\Http\Controllers\PositionAssignmentController::class, 'deleteAssignment'])->middleware(['auth', 'isAdmin'])->name('api.positionsAssignment.destroy');

/* >>>>>ResponseIdeals routes<<<<<< */
Route::inertia('/responseIdeals', 'ResponseIdeals/Index')->middleware(['auth', 'isAdmin'])->name('responseIdeals.index.view');
Route::resource('api/responseIdeals', \App\Http\Controllers\ResponseIdealController::class, [
    'as' => 'api'
])->middleware('auth');

/* >>>>>Roles routes <<<<<< */
Route::get('/roles', [\App\Http\Controllers\Roles\RoleController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('roles.index');
Route::resource('api/roles', \App\Http\Controllers\Roles\ApiRoleController::class, [
    'as' => 'api'
])->middleware('auth');


/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Test routes <<<<<<<<<<<<<<<<<<<<<<<<<<< */
Route::get('/tests', [\App\Http\Controllers\TestController::class, 'indexView'])->middleware(['auth'])->name('tests.index.view');
Route::post('/tests/{testId}', [\App\Http\Controllers\TestController::class, 'startTest'])->middleware(['auth'])->name('tests.startTest');
Route::get('/tests/{testId}/preview', [\App\Http\Controllers\TestController::class, 'preview'])->middleware(['auth'])->name('tests.preview');
//Change teacher status
Route::resource('api/tests', \App\Http\Controllers\TestController::class, [
    'as' => 'api'])->middleware('auth');


/* >>>>>User routes <<<<<< */
Route::get('/users', [\App\Http\Controllers\Users\UserController::class, 'index'])->middleware(['auth', 'isAdmin'])->name('users.index');
Route::get('api/users/sync', [\App\Http\Controllers\UserController::class, 'getWithoutQuestions'])->middleware(['auth', 'isAdmin'])->name('api.forms.withoutQuestions');
Route::resource('api/users', \App\Http\Controllers\Users\ApiUserController::class, [
    'as' => 'api'
])->middleware('auth');
//Update user role
Route::patch('/api/users/{user}/roles', [\App\Http\Controllers\Users\ApiUserController::class, 'updateUserRoles'])->middleware('auth')->name('api.users.roles.update');
Route::get('/api/users/{user}/roles', [\App\Http\Controllers\Users\ApiUserController::class, 'getUserRoles'])->middleware('auth')->name('api.users.roles.show');
Route::post('/api/roles/select', [\App\Http\Controllers\Users\ApiUserController::class, 'selectRole'])->middleware('auth')->name('api.roles.selectRole');
Route::post('/users/{userId}/impersonate', [\App\Http\Controllers\Users\UserController::class, 'impersonate'])->middleware(['auth', 'isAdmin'])->name('users.impersonate');


/* >>>>>>>>>>>>>>>>>>>>>>>>>>>> Auth routes <<<<<<<<<<<<<<<<<<<<<<<< */
Route::get('/login', [\App\Http\Controllers\AuthController::class, 'landing'])->name('login');
Route::post('/loginExternal', [\App\Http\Controllers\AuthController::class, 'externalClientLogin'])->name('externalClient.login');
Route::get('/', [\App\Http\Controllers\AuthController::class, 'handleRoleRedirect'])->middleware(['auth'])->name('redirect');
Route::get('/googleLogin', [\App\Http\Controllers\AuthController::class, 'redirectGoogleLogin'])->name('googleLogin');
Route::post('/logout', [\App\Http\Controllers\AuthController::class, 'logout'])->name('logout');
Route::get('/google/callback', [\App\Http\Controllers\AuthController::class, 'handleGoogleCallback']);
Route::get('/pickRole', [\App\Http\Controllers\AuthController::class, 'pickRole'])->name('pickRole');


Route::inertia('/assessmentStatus', 'Dependencies/AssessmentStatus')->middleware(['auth', 'isAdmin']);
Route::post('/testWebService', [\App\Http\Controllers\AuthController::class, 'webService'])->name('webService.test');
