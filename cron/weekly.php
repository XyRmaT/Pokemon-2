<?php

include dirname(__FILE__) . '/../include/class_cron.php';


// Delete messages that are read and already lasted a week

DB::query('DELETE FROM pkm_myinbox WHERE rdateline < ' . ($_SERVER['REQUEST_TIME'] - 604800));

Cron::LogInsert('Update user\'s currency');


Cron::LogSave('weekly', 'Y');