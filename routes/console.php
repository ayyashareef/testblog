<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('news:fetch')->everyTenMinutes()->withoutOverlapping();
Schedule::command('news:snapshot')->hourly();
