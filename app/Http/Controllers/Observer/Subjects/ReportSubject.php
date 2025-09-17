<?php
namespace App\Http\Controllers\Observer\Subjects;

use App\Http\Controllers\Observer\Events\ReportGeneratedEvent;
use App\Models\User;

class ReportSubject extends BaseSubject
{
    public function __construct()
    {
        parent::__construct('ReportSubject');
    }

    public function notifyReportGenerated(string $reportType, User $generatedBy, array $reportData = [], array $additionalData = []): void
    {
        $event = new ReportGeneratedEvent($reportType, $generatedBy, $reportData, $additionalData);
        $this->notify($event);
    }
}