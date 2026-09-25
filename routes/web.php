<?php

use App\Http\Controllers\Auth\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ClassRoomController;
use App\Http\Controllers\SubjectController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\AssignmentController;
use App\Http\Controllers\ScheduleController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\AcademicYearController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AttendanceReviewController;
use App\Http\Controllers\StageReportController;
use App\Http\Controllers\ClassReportController;
use App\Http\Controllers\TeacherReportController;
use App\Http\Controllers\SupervisorController;
use App\Http\Controllers\SupervisorAttendanceController;
use App\Http\Controllers\SupervisorReportController;
use App\Http\Controllers\EducationalSupervisorController;
use App\Http\Controllers\StageController;

Route::get('/', function () {
    return redirect(auth()->check() ? '/dashboard' : '/login');
});
Route::get('/login', [LoginController::class, 'create'])->name('login');
Route::post('/login', [LoginController::class, 'store']);
Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

Route::middleware(['auth', 'admin'])->group(function () {
    Route::get('/classes', [ClassRoomController::class, 'index'])->name('classes.index');
    Route::post('/classes', [ClassRoomController::class, 'store']);
    Route::get('/classes/{classRoom}', [ClassRoomController::class, 'show'])->name('classes.show');
    Route::delete('/classes/{classRoom}', [ClassRoomController::class, 'destroy']);

    Route::get('/stages', [StageController::class, 'index'])->name('stages.index');
    Route::post('/stages', [StageController::class, 'store']);
    Route::delete('/stages/{stage}', [StageController::class, 'destroy']);

    Route::get('/subjects', [SubjectController::class, 'index'])->name('subjects.index');
    Route::post('/subjects', [SubjectController::class, 'store']);
    Route::delete('/subjects/{subject}', [SubjectController::class, 'destroy']);

    Route::get('/students', [StudentController::class, 'index'])->name('students.index');
    Route::post('/students', [StudentController::class, 'store']);
    Route::delete('/students/{student}', [StudentController::class, 'destroy']);

    Route::get('/teachers', [TeacherController::class, 'index'])->name('teachers.index');
    Route::post('/teachers', [TeacherController::class, 'store']);
    Route::delete('/teachers/{teacher}', [TeacherController::class, 'destroy']);

    Route::get('/assignments', [AssignmentController::class, 'index'])->name('assignments.index');
    Route::post('/assignments', [AssignmentController::class, 'store']);
    Route::delete('/assignments/{assignment}', [AssignmentController::class, 'destroy']);

    Route::post('/assignments/{assignment}/schedules', [ScheduleController::class, 'store'])->name('schedules.store');
    Route::delete('/schedules/{schedule}', [ScheduleController::class, 'destroy'])->name('schedules.destroy');

    Route::get('/academic-years', [AcademicYearController::class, 'index'])->name('academic-years.index');
    Route::post('/academic-years', [AcademicYearController::class, 'store']);
    Route::delete('/academic-years/{academicYear}', [AcademicYearController::class, 'destroy']);
    Route::post('/academic-years/{academicYear}/terms', [AcademicYearController::class, 'storeTerm'])->name('terms.store');
    Route::delete('/terms/{term}', [AcademicYearController::class, 'destroyTerm'])->name('terms.destroy');
    Route::post('/terms/{term}/activate', [AcademicYearController::class, 'activateTerm'])->name('terms.activate');

    Route::get('/attendance-review', [AttendanceReviewController::class, 'index'])->name('attendance-review.index');
    Route::patch('/attendance-review/{attendance}', [AttendanceReviewController::class, 'update'])->name('attendance-review.update');
    Route::patch('/attendance-review/supervisor/{supervisorAttendance}', [AttendanceReviewController::class, 'updateSupervisor'])->name('attendance-review.update-supervisor');
    Route::post('/attendance-review/approve-group', [AttendanceReviewController::class, 'approveGroup'])->name('attendance-review.approve-group');
    Route::post('/attendance-review/approve-supervisor-group', [AttendanceReviewController::class, 'approveSupervisorGroup'])->name('attendance-review.approve-supervisor-group');

    Route::get('/supervisors', [SupervisorController::class, 'index'])->name('supervisors.index');
    Route::post('/supervisors', [SupervisorController::class, 'store']);
    Route::delete('/supervisors/{supervisor}', [SupervisorController::class, 'destroy']);

    Route::get('/educational-supervisors', [EducationalSupervisorController::class, 'index'])->name('educational-supervisors.index');
    Route::post('/educational-supervisors', [EducationalSupervisorController::class, 'store']);
    Route::delete('/educational-supervisors/{educationalSupervisor}', [EducationalSupervisorController::class, 'destroy']);
});

// صفحات التقارير: متاحة للأدمن وللمشرف التربوي (اطّلاع فقط، من غير أي صلاحية تعديل)
Route::middleware(['auth', 'reports.viewer'])->group(function () {
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/stages', [StageReportController::class, 'index'])->name('reports.stages');
    Route::get('/reports/classes', [ClassReportController::class, 'index'])->name('reports.classes');
    Route::get('/reports/teachers', [TeacherReportController::class, 'index'])->name('reports.teachers');
    Route::get('/reports/supervisor', [SupervisorReportController::class, 'index'])->name('reports.supervisor');
});

Route::middleware(['auth', 'supervisor'])->group(function () {
    Route::get('/supervisor-attendance', [SupervisorAttendanceController::class, 'index'])->name('supervisor-attendance.index');
    Route::post('/supervisor-attendance', [SupervisorAttendanceController::class, 'store'])->name('supervisor-attendance.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::get('/attendance/session/{schedule}', [AttendanceController::class, 'create'])->name('attendance.create');
    Route::post('/attendance/session/{schedule}', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/attendance/{assignment}/stats', [AttendanceController::class, 'stats'])->name('attendance.stats');
});
