<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$rows = \App\Models\EmailTemplate::select('id','template_name','user_type','logo','image','banner_image')
    ->whereIn('template_name',['order-place','order-received','registration','digital-product-download'])
    ->get();
foreach ($rows as $r) {
    echo $r->id.' | '.$r->template_name.' | '.$r->user_type.' | logo=['.($r->logo?:'EMPTY').'] | image=['.($r->image?:'EMPTY').'] | banner=['.($r->banner_image?:'EMPTY').']'.PHP_EOL;
}
