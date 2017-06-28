<?php

use App\{Investment, Revenue, Log};

if (!function_exists('investors'))
{

    function investors($currency)
    {
        $investors   = [];
        $investments = Investment::valid()->currencyType($currency)->get();

        foreach ($investments as $investment) {
            $investors[$investment->user_id]   = $investors[$investment->user_id] ?? [];
            $investors[$investment->user_id][] = $investment;
        }

        $percentage = [];
        $amount     = 0;

        foreach ($investors as $userId => $investor) {
            /**
             * calculate sum of investment
             */
            $pluck = collect($investor)->sum('amount');

            /**
             * raw percentage data
             */
            $percentage[$userId] = $pluck;

            /**
             * total amount
             */
            $amount += $pluck;
        }

        foreach ($percentage as &$value) {
            $value = floor($value / $amount * pow(10, 5)) / pow(10, 5);
        }

        return $percentage;
    }
}

if (!function_exists('revenue_diff_percentage'))
{

    function revenue_diff_percentage($currency)
    {
        $latest   = Log::currencyType($currency)
            ->latest()
            ->first();
        $previous = Log::currencyType($currency)
            ->latest()
            ->skip(1)
            ->take(1)
            ->first();

        $latestAmount   = $latest->amount ?? 0;
        $previousAmount = $previous->amount ?? 1;
        $diffAmount     = $latestAmount ? 1 : 0;

        return round((($latestAmount / $previousAmount) - $diffAmount) * 100, 2);
    }
}

if (!function_exists('revenue_diff_chart'))
{

    function revenue_diff_chart($currency)
    {
        $revenue = Log::currencyType($currency)
            ->latest()
            ->take(14)
            ->get();

        $chart = [];
        foreach ($revenue as $value) {
            $createdAt = $value->created_at->format('m/d');

            $chart[$createdAt] = ($chart[$createdAt] ?? 0) + $value->amount;
        }

        ksort($chart);

        return $chart;
    }
}

if (!function_exists('amount_output'))
{

    function amount_output($amount)
    {
        $html   = '<font color="%s">%s</font>';
        $color  = $amount < 0 ? 'red' : 'green';
        $color  = $amount == 0 ? 'black' : $color;
        $amount = abs($amount);
        $pos    = strpos($amount, 'E');
        if ($amount < 1 && $pos !== -1) {
            $suffix = (float) substr($amount, 0, $pos);
            $suffix = (float) str_replace('.', '', $suffix);
            $suffix = bcadd(0, $suffix);
            $zero   = (int) substr($amount, $pos + 2) - 1;
            $amount = '0.'.str_repeat('0', $zero).$suffix;
        }

        return sprintf($html, $color, $amount);
    }
}

if (!function_exists('javascript'))
{

    function javascript(array $input)
    {
        JavaScript::put($input);
    }
}

if (!function_exists('form'))
{

    function form(array $input)
    {
        javascript(['form' => $input]);
    }
}
