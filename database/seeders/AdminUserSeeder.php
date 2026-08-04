<?php
namespace Database\Seeders;
use App\Models\Role;use App\Models\User;use Illuminate\Database\Seeder;use Illuminate\Support\Facades\Hash;
class AdminUserSeeder extends Seeder{public function run():void{if(!app()->environment(['local','testing'])){return;}$u=User::updateOrCreate(['email'=>env('SEED_ADMIN_EMAIL','admin@asnaf-gorgan.ir')],['name'=>env('SEED_ADMIN_NAME','مدیرکل سامانه اتاق اصناف گرگان'),'mobile'=>env('SEED_ADMIN_MOBILE','09110000000'),'password'=>Hash::make(env('SEED_ADMIN_PASSWORD','ChangeMe123!')),'is_active'=>true,'email_verified_at'=>now()]);if($r=Role::where('name','super-admin')->first()){$u->roles()->syncWithoutDetaching([$r->id]);}}}
