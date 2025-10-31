<?php

Route::prefix('api/nonmember')
    ->middleware(['api'])
    ->group(function () {
});
