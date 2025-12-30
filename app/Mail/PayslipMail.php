<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Queue\SerializesModels;

class PayslipMail extends Mailable
{
    use Queueable, SerializesModels;

    public $payroll;
    public $user;
    public $staff;
    public $month;

    /**
     * Create a new message instance.
     */
    public function __construct($payroll, $user, $month = null)
    {
        $this->payroll = $payroll;
        $this->user = $user;
        $this->staff = $user->staff;
        $this->month = $month ?? $payroll->year . '-' . str_pad($payroll->month, 2, '0', STR_PAD_LEFT);
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $monthLabel = \Carbon\Carbon::create($this->month)->format('F Y');
        return new Envelope(
            subject: "Your Payslip for {$monthLabel} - ELMSP",
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'mail.payslip',
            with: [
                'payroll' => $this->payroll,
                'user' => $this->user,
                'staff' => $this->staff,
                'month' => $this->month,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        try {
            // Render payslip HTML
            $html = view('partials.payslip-template', [
                'payroll' => $this->payroll,
                'user' => $this->user,
                'staff' => $this->staff,
                'month' => $this->month
            ])->render();

            // Generate PDF
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadHTML($html);
            $pdf->setPaper('A4', 'portrait');
            
            // Generate filename
            $filename = 'payslip_' . strtolower(str_replace(' ', '_', $this->user->name)) . '_' . $this->month . '.pdf';
            
            return [
                Attachment::fromData(
                    fn () => $pdf->output(),
                    $filename
                )->withMime('application/pdf'),
            ];
        } catch (\Exception $e) {
            \Log::error('Error generating payslip PDF for email: ' . $e->getMessage());
            return [];
        }
    }
}

