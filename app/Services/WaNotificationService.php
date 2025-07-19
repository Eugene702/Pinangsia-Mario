<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Log;

class WaNotificationService
{
    protected string $apiUrl = 'https://api.fonnte.com/send';

    public function sendCleaningAssignmentNotification(User $staff, string $roomNumber, string $scheduledTime): bool
    {
        $phoneNumber = $this->formatPhoneNumber($staff->no_telp);
        $message = "Hai {$staff->name},\n\nAnda telah ditugaskan untuk membersihkan kamar {$roomNumber} pada {$scheduledTime}.\n\nSilahkan periksa dashboard untuk detail lebih lanjut.";
        return $this->sendMessage($phoneNumber, $message);
    }

    public function sendMessage(string $target, string $message): bool
    {
        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ],
            CURLOPT_HTTPHEADER => [
                'Authorization: ' . env('WA_API_TOKEN'),
            ],
        ]);

        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            Log::error('WhatsApp API Error: ' . curl_error($curl));
            curl_close($curl);
            return false;
        }

        curl_close($curl);
        Log::info('WhatsApp API Response: ' . $response);
        return true;
    }

    protected function formatPhoneNumber(string $phone): string
    {
        $phone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($phone, '0')) {
            return '62' . substr($phone, 1);
        }

        return $phone;
    }
}
