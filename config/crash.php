<?php

return [
    // House edge: RTP is 1 - edge (0.03 => 97%) at every cash-out target.
    'house_edge' => env('CRASH_HOUSE_EDGE', 0.03),
    'max_multiplier' => env('CRASH_MAX_MULTIPLIER', 1000),
    // 0 = no cap on a single payout
    'max_win' => env('CRASH_MAX_WIN', 0),
    'min_bet' => env('CRASH_MIN_BET', 10),
    'max_bet' => env('CRASH_MAX_BET', 100000),
];
