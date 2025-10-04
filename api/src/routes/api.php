<?php

use Taskboard\Interface\Http\TaskController;


return [
    ['GET',    '/tasks',        [TaskController::class, 'index']],
    ['POST',   '/tasks',        [TaskController::class, 'store']],
    ['PUT',    '/tasks/{id}',   [TaskController::class, 'update']],
    ['DELETE', '/tasks/{id}',   [TaskController::class, 'destroy']],
];
