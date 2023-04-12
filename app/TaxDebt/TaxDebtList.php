<?php

namespace App\TaxDebt;

use Illuminate\Support\Facades\DB;

class TaxDebtList
{

    public function forNotifyOnlyNotifiable()
    {
        return $this->forNotify('select');
    }

    public function forCheckOnlyNotifiable()
    {
        return DB::table('tax_debt_inn')
            ->whereis_valid('1')
            ->whereis_notifiable('1')
            ->pluck('inn')->toArray();
    }

    public function setAsNotifiedToday()
    {
        return $this->forNotify('update');
    }

    private function diffDateSQLSt()
    {
        $t = config('taxdebt.notify_period_measure');
        $v = config('taxdebt.notify_period_value');

        return 'TIMESTAMPDIFF(' . $t . ', notified_at, NOW()) > ' . $v;
    }

    private function forNotify($type)
    {
        $q = DB::table('tax_debt_inn')
            ->whereis_valid('1')
            ->whereis_notifiable('1')
            ->whereRaw('curr_debt != prev_debt')
            ->where(function ($q) {
                $q->orWhereNull('notified_at');
                $q->orWhereRaw($this->diffDateSQLSt());
            });
        if ($type == 'update') {
            $q->update([
                'prev_debt' => DB::raw('curr_debt'),
                'notified_at' => DB::raw('NOW()')
            ]);
        }
        if ($type == 'select') {
            return $q->pluck('inn')->toArray();
        }
    }
}
