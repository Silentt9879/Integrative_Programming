<?php
namespace App\Http\Controllers\Observer\Events;

use App\Models\User;

class ReportGeneratedEvent extends BaseEvent
{
    private string $reportType;
    private User $generatedBy;
    private array $reportData;
    private array $generationData;

    public function __construct(string $reportType, User $generatedBy, array $reportData = [], array $generationData = [])
    {
        $this->reportType = $reportType;
        $this->generatedBy = $generatedBy;
        $this->reportData = $reportData;
        $this->generationData = $generationData;
        
        parent::__construct([
            'report_type' => $reportType,
            'generated_by' => $generatedBy->id,
            'report_data_size' => count($reportData),
            'generation_data' => $generationData
        ]);
    }

    public function getReportType(): string
    {
        return $this->reportType;
    }

    public function getGeneratedBy(): User
    {
        return $this->generatedBy;
    }

    public function getReportData(): array
    {
        return $this->reportData;
    }

    public function getGenerationData(): array
    {
        return $this->generationData;
    }
}