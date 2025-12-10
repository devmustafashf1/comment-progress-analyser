use App\Http\Controllers\TaskAnalysisController;

Route::post('/analyze-task', [TaskAnalysisController::class, 'analyze']);
