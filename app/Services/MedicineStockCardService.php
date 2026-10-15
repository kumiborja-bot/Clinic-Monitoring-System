<?php

namespace App\Services;

use App\Models\Medicine;
use App\Models\MedicineStockMovement;

class MedicineStockCardService
{
    public function build(
        Medicine $medicine,
        ?string $dateFrom = null,
        ?string $dateTo = null
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Opening Balance
        |--------------------------------------------------------------------------
        |
        | If a report starts in the middle of the medicine's history,
        | calculate the remaining stock BEFORE the selected start date.
        |--------------------------------------------------------------------------
        */

        $openingBalance = 0;

        if ($dateFrom) {

            $previousIn =
                MedicineStockMovement::where(
                    'medicine_id',
                    $medicine->id
                )
                ->whereDate(
                    'transaction_date',
                    '<',
                    $dateFrom
                )
                ->where(
                    'type',
                    'in'
                )
                ->sum('quantity');

            $previousOut =
                MedicineStockMovement::where(
                    'medicine_id',
                    $medicine->id
                )
                ->whereDate(
                    'transaction_date',
                    '<',
                    $dateFrom
                )
                ->where(
                    'type',
                    'out'
                )
                ->sum('quantity');

            $openingBalance =
                (int) $previousIn
                - (int) $previousOut;
        }

        /*
        |--------------------------------------------------------------------------
        | Get Transactions
        |--------------------------------------------------------------------------
        */

        $query =
            MedicineStockMovement::where(
                'medicine_id',
                $medicine->id
            );

        if ($dateFrom) {
            $query->whereDate(
                'transaction_date',
                '>=',
                $dateFrom
            );
        }

        if ($dateTo) {
            $query->whereDate(
                'transaction_date',
                '<=',
                $dateTo
            );
        }

        $movements = $query
            ->orderBy(
                'transaction_date'
            )
            ->orderByRaw(
                "CASE WHEN type = 'in' THEN 0 ELSE 1 END"
            )
            ->orderBy('id')
            ->get();

        $runningBalance =
            $openingBalance;

        $transactions =
            collect();

        /*
        |--------------------------------------------------------------------------
        | Process Day By Day
        |--------------------------------------------------------------------------
        |
        | IN:
        | Every stock delivery remains its own row.
        |
        | OUT:
        | All dispensing records on the same date are combined.
        |--------------------------------------------------------------------------
        */

        $movementsByDate =
            $movements->groupBy(
                fn ($movement) =>
                    $movement
                        ->transaction_date
                        ->format('Y-m-d')
            );

        foreach (
            $movementsByDate
            as $date => $dayMovements
        ) {

            /*
            |--------------------------------------------------------------------------
            | IN TRANSACTIONS
            |--------------------------------------------------------------------------
            */

            $inMovements =
                $dayMovements
                    ->where(
                        'type',
                        'in'
                    )
                    ->values();

            foreach (
                $inMovements
                as $movement
            ) {

                $runningBalance +=
                    (int) $movement->quantity;

                $transactions->push([
                    'date' =>
                        $date,

                    'supplier' =>
                        $movement->supplier ?? '',

                    'in' =>
                        (int) $movement->quantity,

                    'requester' =>
                        '',

                    'out' =>
                        '',

                    'balance' =>
                        $runningBalance,

                    'is_opening' =>
                        false,
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | OUT TRANSACTIONS
            |--------------------------------------------------------------------------
            |
            | All medicine releases for this date become ONE row.
            |--------------------------------------------------------------------------
            */

            $outMovements =
                $dayMovements
                    ->where(
                        'type',
                        'out'
                    )
                    ->values();

            if ($outMovements->isNotEmpty()) {

                $totalOut =
                    (int) $outMovements
                        ->sum('quantity');

                $requesters =
                    $outMovements
                        ->pluck('requester')
                        ->filter()
                        ->unique()
                        ->values();

                /*
                |--------------------------------------------------------------------------
                | Requester Rule
                |--------------------------------------------------------------------------
                |
                | One requester:
                | JUAN DELA CRUZ
                |
                | Two or more different requesters:
                | VARIOUS STUDENT/PERSONNEL
                |--------------------------------------------------------------------------
                */

                if (
                    $requesters->count() >= 2
                ) {

                    $requesterName =
                        'Various Student/Personnel';

                } elseif (
                    $requesters->count() === 1
                ) {

                    $requesterName =
                        $requesters->first();

                } else {

                    $requesterName =
                        'Unknown';
                }

                $runningBalance -=
                    $totalOut;

                $transactions->push([
                    'date' =>
                        $date,

                    'supplier' =>
                        '',

                    'in' =>
                        '',

                    'requester' =>
                        $requesterName,

                    'out' =>
                        $totalOut,

                    'balance' =>
                        $runningBalance,

                    'is_opening' =>
                        false,
                ]);
            }
        }

        return [
            'transactions' =>
                $transactions,

            'openingBalance' =>
                $openingBalance,

            'currentStock' =>
                (int) $medicine->stock_quantity,
        ];
    }
}
