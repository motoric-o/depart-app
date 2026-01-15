<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$request = Illuminate\Http\Request::create('/api/admin/schedules', 'GET');
$controller = new App\Http\Controllers\Api\Admin\ScheduleController();

try {
    // Determine admin user to impersonate for Gate checks
    // We need a user with 'Owner' role effectively.
    // Assuming ID 1 is Super Admin or Owner from database seeder.
    // Or we can create a dummy user instance.
    
    // Actually, we relaxed the Gate checks in the code, but there might be 'Auth::user()' calls?
    // Let's check ScheduleController logic.
    // It doesn't use Auth::user() in index.
    // But ApiSearchable might? No.
    // But middleware 'role' is NOT executed when calling controller directly.
    // So this proves if the CONTROLLER logic works.
    
    $response = $controller->index($request);
    
    // If it returns a JsonResponse
    if ($response instanceof \Illuminate\Http\JsonResponse) {
        echo $response->getContent();
    } else {
        // If it returns a Paginator directly (it shouldn't, verify return type)
        // ApiSearchable returns Paginator.
        // ScheduleController returns: return response()->json($expenses); wait, that was Expense.
        // ScheduleController returns: ???
        // Let's check ScheduleController return line.
        echo json_encode($response);
    }
    
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString();
}
