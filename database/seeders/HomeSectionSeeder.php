<?php
namespace Database\Seeders;
use App\Models\HomeSection;use Illuminate\Database\Seeder;
class HomeSectionSeeder extends Seeder{public function run():void{$labels=HomeSection::keyLabels();foreach(HomeSection::DEFAULT_KEYS as $i=>$key){HomeSection::updateOrCreate(['key'=>$key],['title'=>$labels[$key]??$key,'subtitle'=>'آخرین اطلاعات و محتوای به‌روزرسانی‌شده اتاق اصناف گرگان','settings'=>['limit'=>in_array($key,['hero_slider','important_news','daily_news'])?6:8,'position'=>$key==='advertisements'?'home_top':null],'is_active'=>true,'sort_order'=>($i+1)*10]);}}}
