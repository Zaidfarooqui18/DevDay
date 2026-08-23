<?php

namespace DevDay\Helpers;

class Sanitizer
{
    public static function e(?string $value): string
    {
        if ($value === null) {
            return '';
        }
        return htmlspecialchars($value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }

    public static function formatMinutes(int|float|null $minutes): string
    {
        if (!$minutes || $minutes <= 0) {
            return '0m';
        }

        $totalMinutes = (int)round($minutes);
        $hours = intdiv($totalMinutes, 60);
        $mins = $totalMinutes % 60;

        if ($hours > 0 && $mins > 0) {
            return "{$hours}h {$mins}m";
        } elseif ($hours > 0) {
            return "{$hours}h";
        } else {
            return "{$mins}m";
        }
    }

    public static function formatSeconds(int|null $seconds): string
    {
        if (!$seconds || $seconds <= 0) {
            return '00:00:00';
        }

        $hours = intdiv($seconds, 3600);
        $remainder = $seconds % 3600;
        $minutes = intdiv($remainder, 60);
        $secs = $remainder % 60;

        return sprintf('%02d:%02d:%02d', $hours, $minutes, $secs);
    }

    public static function formatDate(string|null $dateStr, string $format = 'l, F j, Y'): string
    {
        if (!$dateStr) {
            return '';
        }
        $ts = strtotime($dateStr);
        return $ts ? date($format, $ts) : $dateStr;
    }
}
