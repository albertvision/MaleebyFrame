<?php

use Maleeby\Libraries\DB;

function test() {
    print_r(DB::query('SELECT * FROM `users`')->fetchAssoc());
}
?>