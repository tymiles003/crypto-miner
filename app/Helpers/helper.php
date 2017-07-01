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
            $value = percentage($value, $amount);
        }

        return $percentage;
    }
}

if (!function_exists('percentage'))
{

    function percentage($child, $mother)
    {
        return floor($child / $mother * pow(10, 5)) / pow(10, 5);;
    }
}

if (!function_exists('decimal_mul'))
{

    function decimal_mul($valueA, $valueB)
    {
        return number_format($valueA * $valueB, decimal($valueA) + decimal($valueB));
    }
}

if (!function_exists('revenue_diff_percentage'))
{

    function revenue_diff_percentage($currency)
    {
        $day = revenue_chart_day($currency);

        $latestAmount = Log::currencyType($currency)
            ->whereDate('created_at', '=', $day->toDateString())
            ->sum('amount');

        $previousAmount = Log::currencyType($currency)
            ->whereDate('created_at', '=', $day->subDay()->toDateString())
            ->sum('amount') ?: 1;

        $diffAmount = $latestAmount ? 1 : 0;

        return round((($latestAmount / $previousAmount) - $diffAmount) * 100, 2);
    }
}

if (!function_exists('revenue_chart_day'))
{

    function revenue_chart_day($currency)
    {
        return Log::currencyType($currency)->latest()->first()->created_at;
    }
}

if (!function_exists('revenue_diff_chart'))
{

    function revenue_diff_chart($currency)
    {
        $twoWeeks = revenue_chart_day($currency)->subDays(14)->toDateString();

        $revenue = Log::currencyType($currency)
            ->latest()
            ->whereDate('created_at', '>=', $twoWeeks)
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
        $amount = str_replace('-', '', $amount);

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

if (!function_exists('decimal'))
{

    function decimal($value)
    {
        return strlen(substr(rtrim($value, '0'), strpos(rtrim($value, '0'), '.') + 1));
    }
}
