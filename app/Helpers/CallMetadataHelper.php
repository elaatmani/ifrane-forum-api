<?php

namespace App\Helpers;

class CallMetadataHelper
{
    /**
     * Create metadata for missed call
     */
    public static function missedCall(array $data = []): array
    {
        return array_merge([
            'call_type' => 'missed',
            'timestamp' => now()->toISOString(),
            'duration' => 0,
            'reason' => 'missed'
        ], $data);
    }

    /**
     * Create metadata for video call request
     */
    public static function videoCallRequest(array $data = []): array
    {
        return array_merge([
            'call_type' => 'video',
            'status' => 'requesting',
            'timestamp' => now()->toISOString(),
            'request_id' => uniqid('video_'),
            'expires_at' => now()->addMinutes(5)->toISOString()
        ], $data);
    }

    /**
     * Create metadata for voice call request
     */
    public static function voiceCallRequest(array $data = []): array
    {
        return array_merge([
            'call_type' => 'voice',
            'status' => 'requesting',
            'timestamp' => now()->toISOString(),
            'request_id' => uniqid('voice_'),
            'expires_at' => now()->addMinutes(5)->toISOString()
        ], $data);
    }

    /**
     * Create metadata for call ended
     */
    public static function callEnded(array $data = []): array
    {
        return array_merge([
            'call_type' => $data['call_type'] ?? 'unknown',
            'status' => 'ended',
            'timestamp' => now()->toISOString(),
            'duration' => $data['duration'] ?? 0,
            'reason' => $data['reason'] ?? 'ended'
        ], $data);
    }

    /**
     * Create metadata for call rejected
     */
    public static function callRejected(array $data = []): array
    {
        return array_merge([
            'call_type' => $data['call_type'] ?? 'unknown',
            'status' => 'rejected',
            'timestamp' => now()->toISOString(),
            'reason' => $data['reason'] ?? 'rejected'
        ], $data);
    }

    /**
     * Create metadata for call accepted
     */
    public static function callAccepted(array $data = []): array
    {
        return array_merge([
            'call_type' => $data['call_type'] ?? 'unknown',
            'status' => 'accepted',
            'timestamp' => now()->toISOString(),
            'call_id' => $data['call_id'] ?? uniqid('call_')
        ], $data);
    }

    /**
     * Get call duration from metadata
     */
    public static function getCallDuration(array $metadata): int
    {
        return $metadata['duration'] ?? 0;
    }

    /**
     * Get call type from metadata
     */
    public static function getCallType(array $metadata): string
    {
        return $metadata['call_type'] ?? 'unknown';
    }

    /**
     * Get call status from metadata
     */
    public static function getCallStatus(array $metadata): string
    {
        return $metadata['status'] ?? 'unknown';
    }

    /**
     * Check if call request is expired
     */
    public static function isCallRequestExpired(array $metadata): bool
    {
        if (!isset($metadata['expires_at'])) {
            return false;
        }

        return now()->isAfter($metadata['expires_at']);
    }
} 