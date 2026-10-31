<?php

return [
    // KYC level needed to bet or deposit: 'submitted' (18+ DOB and ID number on file)
    // or 'verified' (an admin has checked the ID). BCLB expects verified identities,
    // so switch to 'verified' once you have an ID-check process or provider.
    'play_requires' => env('RG_PLAY_REQUIRES', 'submitted'),
];
