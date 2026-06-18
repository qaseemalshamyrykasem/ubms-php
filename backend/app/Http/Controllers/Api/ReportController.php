<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $service)
    {
    }

    public function attendance(Request $request, int $batchId, string $format)
    {
        $batch = \App\Models\Batch::findOrFail($batchId);
        $this->authorize('view', $batch);
        $filters = $request->only(['course_id', 'from', 'to']);

        return $format === 'pdf'
            ? $this->service->exportAttendancePdf($batch, $filters)
            : $this->service->exportAttendanceExcel($batch, $filters);
    }

    public function students(int $batchId)
    {
        $batch = \App\Models\Batch::findOrFail($batchId);
        $this->authorize('view', $batch);
        return $this->service->exportStudentsExcel($batch);
    }

    public function announcements(int $batchId)
    {
        $batch = \App\Models\Batch::findOrFail($batchId);
        $this->authorize('view', $batch);
        return $this->service->exportAnnouncementsExcel($batch);
    }

    public function assignments(int $batchId)
    {
        $batch = \App\Models\Batch::findOrFail($batchId);
        $this->authorize('view', $batch);
        return $this->service->exportAssignmentsExcel($batch);
    }

    public function statistics(int $batchId, string $format = 'excel')
    {
        $batch = \App\Models\Batch::findOrFail($batchId);
        $this->authorize('view', $batch);
        return $format === 'excel'
            ? $this->service->exportStatisticsExcel($batch)
            : response()->json($this->service->generateStats($batch));
    }

    public function stats(int $batchId)
    {
        $batch = \App\Models\Batch::findOrFail($batchId);
        $this->authorize('view', $batch);
        return response()->json($this->service->generateStats($batch));
    }
}
