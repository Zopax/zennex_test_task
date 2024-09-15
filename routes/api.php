    <?php

    use Illuminate\Support\Facades\Route;
    use Illuminate\Http\Request;
    use App\Http\Controllers\Api\V1\UserController;

    Route::post('/register', [UserController::class, 'register']);
    Route::post('/login', [UserController::class, 'login']);
    Route::post('/logout', [UserController::class, 'logout'])->middleware('auth:sanctum');

    Route::group(['prefix' => 'v1', 'middleware' => 'auth:sanctum', 'namespace' => 'App\Http\Controllers\Api\V1'], function() {
        Route::apiResource('notes', NoteController::class);
        Route::apiResource('tags', TagController::class);
    });