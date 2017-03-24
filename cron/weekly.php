<?php

include __DIR__ . '/../include/class/common.php';
include __DIR__ . '/../include/class/cron.php';

App::Initialize();


// Delete messages that are read and already lasted a week

DB::query('DELETE FROM pkm_myinbox WHERE rdateline < ' . (time() - 604800));

Cron::LogInsert('Update user\'s currency');


Cron::LogSave('weekly', 'Y');