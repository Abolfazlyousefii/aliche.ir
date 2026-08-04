<?php
namespace Database\Seeders;
use App\Models\SiteSetting;use Illuminate\Database\Seeder;use Illuminate\Support\Facades\Cache;
class SiteSettingSeeder extends Seeder{public function run():void{$settings=[
'site.site_title'=>['site','اتاق اصناف مرکز استان گلستان'],'site.site_description'=>['site','پایگاه اطلاع‌رسانی و خدمات الکترونیک اتاق اصناف مرکز استان گلستان'],'site.phone'=>['site','۰۱۷۳۲۱۵۲۹۱۲'],'site.address'=>['site','گرگان، خیابان مطهری جنوبی، روبروی پمپ بنزین، ساختمان اتاق اصناف'],'site.email'=>['site','info@asnaf-gorgan.ir'],
'tourism.intro_title'=>['tourism','به شهر گرگان خوش آمدید'],
'tourism.intro_text'=>['tourism','گرگان با طبیعت هیرکانی، بافت تاریخی و بازارهای فعال، یکی از مقصدهای مهم گردشگری و خرید در استان گلستان است.'],
'tourism.intro_subtext'=>['tourism','اتاق اصناف مرکز استان گلستان با همراهی اتحادیه‌های صنفی، معرفی کسب‌وکارهای معتبر و خدمات مورد نیاز مسافران را پشتیبانی می‌کند.'],
'header.logo'=>['header','assets/img/asnaf-wordmark.svg'],'header.top_text'=>['header','اتاق اصناف مرکز استان گلستان؛ پشتیبان کسب‌وکارهای صنفی'],'header.service_button_text'=>['header','سامانه خدمات صنفی'],'header.service_button_link'=>['header',route('systems.index')],'header.contact_button_text'=>['header','تماس با اتاق'],
'footer.logo'=>['footer','assets/img/asnaf-footer-mark.svg'],'footer.description'=>['footer','اتاق اصناف مرکز استان گلستان، خانه مشترک اتحادیه‌های صنفی و پشتیبان فعالان اقتصادی شهر گرگان است.'],'footer.copyright'=>['footer','تمام حقوق مادی و معنوی این وبسایت متعلق به اتاق اصناف مرکز استان گلستان می‌باشد'],'footer.social_links'=>['footer',['اینستاگرام'=>'https://instagram.com/asnaf.gorgan','تلگرام'=>'https://t.me/asnaf_gorgan','واتساپ'=>'https://wa.me/981732152912','ایتا'=>'https://eitaa.com/asnaf_gorgan']],
'prices.gold'=>['prices',['label'=>'طلا ۱۸ عیار','value'=>'—','unit'=>'تومان','trend'=>'به‌روزرسانی از تنظیمات']],
'prices.coin'=>['prices',['label'=>'سکه امامی','value'=>'—','unit'=>'تومان','trend'=>'به‌روزرسانی از تنظیمات']],
'prices.silver'=>['prices',['label'=>'نقره','value'=>'—','unit'=>'تومان','trend'=>'به‌روزرسانی از تنظیمات']],
'prices.usd'=>['prices',['label'=>'دلار','value'=>'—','unit'=>'تومان','trend'=>'به‌روزرسانی از تنظیمات']]
];foreach($settings as $key=>$data){SiteSetting::updateOrCreate(['key'=>$key],['group'=>$data[0],'value'=>$data[1]]);}Cache::forget('site_settings.all');}}
