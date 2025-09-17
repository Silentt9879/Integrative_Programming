<?php
// app/Strategy/ReportStrategies/ReportGenerationStrategyInterface.php

namespace App\Strategy\ReportStrategies;

use App\Models\User;
use Illuminate\Support\Collection;

interface ReportGenerationStrategyInterface
{
    /**
     * Generate report for the user
     *
     * @param User $user
     * @param Collection $bookings
     * @param array $options
     * @return mixed
     */
    public function generateReport(User $user, Collection $bookings, array $options = []);

    /**
     * Get report format name
     *
     * @return string
     */
    public function getFormatName(): string;

    /**
     * Get file extension for this format
     *
     * @return string
     */
    public function getFileExtension(): string;

    /**
     * Get MIME type for this format
     *
     * @return string
     */
    public function getMimeType(): string;

    /**
     * Check if format is available
     *
     * @return bool
     */
    public function isAvailable(): bool;
}