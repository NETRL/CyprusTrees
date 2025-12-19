<?php

namespace App\Notifications;

use App\Models\CitizenReport;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class CitizenReportStatusChanged extends Notification
{
    use Queueable;

    public function __construct(
        public CitizenReport $report,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function via($notifiable): array
    {
        return ['database'];
    }

    public function toDatabase($notifiable): array
    {
        return [
            'type' => 'citizen_report.status_changed',
            'report_id' => $this->report->report_id,
            'tree_id' => $this->report->tree_id,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'resolved_at' => $this->report->resolved_at,
            'title' => 'Your report status changed',
            'message' => "Report #{$this->report->report_id} is now {$this->newStatus}.",
            'url' => route('reports.index') . '?report_id=' . $this->report->report_id,
        ];
    }
}
