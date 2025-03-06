<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Определение команд Artisan.
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }

    /**
     * Регистрация задач по расписанию.
     */
    protected function schedule(Schedule $schedule)
    {
        // Добавляем автоматическое обновление статуса неоплаченных заказов
        $schedule->command('orders:cancel-unpaid')->everyMinute();
    }
}

