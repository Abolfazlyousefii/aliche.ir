روی پروژه Laravel فعلی سایت اتاق اصناف مرکز استان گلستان کار کن.

مسیر پروژه:
E:\laragon\www\aliche.ir

این پروژه قبلاً چند مرحله اصلاح شده و working tree ممکن است تغییرات تأییدشده دیگری داشته باشد.

قوانین بسیار مهم:

- هیچ git reset / checkout / restore روی فایل‌های unrelated انجام نده.
- هیچ تغییر قبلی را revert نکن.
- هیچ دیتایی را حذف نکن.
- Migration مخرب نساز.
- Column قدیمی را Drop نکن.
- Routeهای فعلی را بدون ضرورت تغییر نده.
- منطق ارتباط اخبار و اتحادیه را خراب نکن.
- بخش News Row جدید #guild-news که قبلاً تأیید شده باید دقیقاً حفظ شود.
- قبل از تغییر، سورس واقعی فعلی را بخوان و اسم فایل‌ها/فیلدها/Controllerها را از خود پروژه تشخیص بده؛ بر اساس حدس فایل جدید نساز.

هدف این تسک:
تکمیل داینامیک بودن ماژول اتحادیه‌ها، رفع قابلیت‌های نیمه‌کاره، یکسان‌سازی تصویر اتحادیه، تکمیل اطلاعات رئیس و هیئت‌مدیره، اصلاح شبکه‌های اجتماعی و Iconها، رفع باگ Hover و تمیز کردن CSS صفحه تکی اتحادیه.

==================================================
A) مرحله اول: Audit اجباری قبل از کدنویسی
==================================================

ابتدا تمام فایل‌های مرتبط را پیدا و بررسی کن.

حداقل این بخش‌ها را بررسی کن:

resources/views/frontend/guilds/
resources/views/admin/

app/Models/GuildUnion.php
app/Models/UnionType.php
app/Models/Post.php

Controllerهای Frontend اتحادیه
Controllerهای Admin اتحادیه
Controller مدیریت اعضای اتحادیه

app/Http/Requests/Admin/StoreUnionRequest.php
app/Http/Requests/Admin/UpdateUnionRequest.php
app/Http/Requests/Admin/StoreUnionMemberRequest.php
app/Http/Requests/Admin/UpdateUnionMemberRequest.php

Migrationهای مربوط به:
- unions / guild_unions
- union members
- commissions
- related union data

public_html/assets/css/styles.css
public_html/assets/css/home-layout-lock.css
public_html/assets/js/main.js
routes/web.php

همچنین با ripgrep یا ابزار مشابه تمام استفاده‌های این موارد را پیدا کن:

cover_image
logo
manager_name
manager_image
position
sort_order
social_links
president_buttons
services_enabled
show_news
show_news_slider

قبل از تغییر مشخص کن:
- نام واقعی table اتحادیه چیست.
- کدام Model فیلدها را fillable/cast می‌کند.
- Store/Update دقیقاً در کدام Controller انجام می‌شوند.
- فرم Admin اتحادیه دقیقاً کجاست.
- فرم Admin اعضای هیئت‌مدیره دقیقاً کجاست.

بعد بر اساس ساختار واقعی پروژه تغییر را اجرا کن.

==================================================
B) استانداردسازی تصویر اصلی اتحادیه
==================================================

الان پروژه دو مفهوم دارد:

cover_image
logo

این دو را حذف یا Merge نکن.

تعریف استاندارد جدید:

cover_image:
«تصویر اصلی اتحادیه»

logo:
«لوگوی اختیاری اتحادیه»

کاربرد مورد انتظار:

cover_image باید تصویر اصلی مورد استفاده در این موارد باشد:

- کارت اتحادیه در آرشیو/لیست اتحادیه‌ها
- نتایج AJAX اتحادیه‌ها
- نتایج جستجو
- نمایش اتحادیه در بخش‌های عمومی سایت در صورت استفاده از تصویر
- Background/Cover صفحه تکی اتحادیه

logo فقط برای هویت/Emblem کوچک اتحادیه استفاده شود، مخصوصاً داخل صفحه تکی.

Fallback:

اگر cover_image وجود نداشت:
logo استفاده شود.

اگر logo برای Emblem صفحه تکی وجود نداشت:
از fallback فعلی پروژه استفاده شود و ساختار fallback خراب نشود.

--------------------------------------------------
B-1) Accessor مرکزی
--------------------------------------------------

برای جلوگیری از تکرار منطق:

cover_image ?: logo

در چند View/Controller مختلف، در Model اتحادیه یک accessor یا method واضح ایجاد کن.

ترجیح نام:

primary_image

مثلاً با API سازگار با نسخه Laravel فعلی پروژه.

رفتار:

primary_image = cover_image اگر موجود بود
در غیر این صورت logo
در غیر این صورت null

بعد بخش‌هایی که «تصویر اصلی اتحادیه» می‌خواهند از همین accessor استفاده کنند.

اما:
Hero Emblem صفحه تکی همچنان باید مستقیم logo را بخواند، نه primary_image.

--------------------------------------------------
B-2) Admin label
--------------------------------------------------

در فرم مدیریت اتحادیه label فعلی cover_image را شفاف کن.

مثلاً:

«تصویر اصلی اتحادیه»

توضیح:

«این تصویر در کارت اتحادیه و کاور صفحه اختصاصی اتحادیه استفاده می‌شود.»

Label لوگو:

«لوگوی اتحادیه (اختیاری)»

توضیح:

«لوگوی کوچک هویتی اتحادیه در صفحه اختصاصی.»

هیچ تصویر موجودی حذف نشود.

==================================================
C) تکمیل اطلاعات رئیس اتحادیه
==================================================

در ساختار فعلی حداقل این موارد وجود دارند:

manager_name
manager_image

این دو حفظ شوند.

دو قابلیت جدید اضافه کن:

manager_position
manager_description

--------------------------------------------------
C-1) Migration
--------------------------------------------------

اگر این دو Column در DB فعلی وجود ندارند، یک Migration جدید، کوچک و Safe بساز.

مثلاً:

manager_position
nullable string

manager_description
nullable text

هیچ Column دیگری تغییر یا Drop نشود.

Migration باید فقط additive باشد.

down() فقط همان دو Column جدید را حذف کند.

--------------------------------------------------
C-2) Model
--------------------------------------------------

Model اتحادیه را با ساختار واقعی پروژه هماهنگ کن:

- fillable / guarded
- casts در صورت نیاز

هیچ cast unrelated را تغییر نده.

--------------------------------------------------
C-3) Validation
--------------------------------------------------

در StoreUnionRequest و UpdateUnionRequest اضافه کن:

manager_position:
nullable
string
max مناسب، مثلاً 190

manager_description:
nullable
string
حد طول منطقی و غیرمحدود عجیب

Validation موجود manager_image را حفظ کن.

--------------------------------------------------
C-4) Admin form
--------------------------------------------------

در فرم ایجاد/ویرایش اتحادیه، بخش رئیس را مرتب کن:

نام رئیس
سمت رئیس
تصویر رئیس
معرفی کوتاه رئیس

manager_description بهتر است textarea باشد.

در edit:
مقدار قبلی نمایش داده شود.

برای manager_image:
Preview تصویر موجود حفظ/اضافه شود.

حذف تصویر موجود را فقط اگر پروژه از قبل مکانیزم استانداردی برای حذف فایل دارد اضافه کن.
سیستم جدید و پیچیده برای حذف نساز.

--------------------------------------------------
C-5) Persistence
--------------------------------------------------

Controller Store/Update باید:

manager_position
manager_description

را ذخیره کند.

منطق upload فعلی manager_image را خراب نکن.

--------------------------------------------------
C-6) Frontend
--------------------------------------------------

در صفحه تکی اتحادیه:

نام رئیس:
manager_name

سمت:
manager_position

اگر manager_position خالی بود:
fallback فعلی مناسب مانند «رئیس اتحادیه» استفاده شود.

توضیح:
manager_description

متن Hard-code فعلی شبیه:

«اطلاعات تکمیلی و شرح سوابق رئیس اتحادیه پس از ثبت...»

باید حذف شود.

اگر manager_description خالی است:
هیچ Placeholder طولانی نمایش داده نشود.
بهتر است عنصر p اصلاً Render نشود.

عکس:
manager_image

Fallback فعلی تصویر را حفظ کن.

==================================================
D) تکمیل مدیریت هیئت‌مدیره
==================================================

طبق ساختار موجود پروژه، اعضای اتحادیه دارای قابلیت/فیلدهای:

position
sort_order

هستند یا برایشان پیش‌بینی شده‌اند، اما مدیریت فعلی ناقص است.

اول ساختار DB و Model را تأیید کن.

اگر Columnها از قبل هستند:
Migration نساز.

اگر واقعاً نیستند:
قبل از ساخت Migration گزارش بده و فقط در صورت ضرورت Migration additive ایجاد کن.

--------------------------------------------------
D-1) Requestها
--------------------------------------------------

StoreUnionMemberRequest
UpdateUnionMemberRequest

باید position و sort_order را Validate کنند:

position:
nullable
string
max حدود 190

sort_order:
nullable
integer
min 0

--------------------------------------------------
D-2) Form اعضا
--------------------------------------------------

در فرم Admin عضو اتحادیه اضافه کن:

سمت عضو
ترتیب نمایش

مثلاً سمت:

رئیس هیئت‌مدیره
نائب رئیس
دبیر
خزانه‌دار
عضو هیئت‌مدیره

ولی input آزاد باشد، Select محدود نساز مگر پروژه همین حالا taxonomy مشخصی داشته باشد.

sort_order:
input number
min=0

--------------------------------------------------
D-3) Controller
--------------------------------------------------

در Store/Update اعضا این دو مقدار واقعاً ذخیره شوند.

اگر Controller helper مثل:

memberData()

دارد، همان را اصلاح کن.

--------------------------------------------------
D-4) Frontend ordering
--------------------------------------------------

هیئت‌مدیره در صفحه تکی باید با sort_order مرتب شود.

ترجیح:

sort_order ASC
سپس id ASC

اما اگر Query فعلی ترتیب دیگری دارد، بدون ایجاد N+1 یا Query اضافی معماری آن را تمیز اصلاح کن.

position در Frontend نمایش داده شود.

اگر position خالی بود:
fallback فعلی «عضو اتحادیه» حفظ شود.

در این تسک عکس جدید برای اعضای هیئت‌مدیره اضافه نکن.

==================================================
E) شبکه‌های اجتماعی و Icon واقعی
==================================================

social_links فعلی حفظ شود.

شبکه‌های پشتیبانی‌شده فعلی پروژه را از فرم Admin استخراج کن.

حداقل موارد شناخته‌شده:

instagram
telegram
whatsapp
eitaa
bale
rubika
website

در Frontend دیگر همه شبکه‌ها نباید یک Icon زنجیر عمومی داشته باشند.

برای هر شبکه Icon متناسب نمایش بده.

--------------------------------------------------
E-1) Component آیکون
--------------------------------------------------

یک Component کوچک و reusable ایجاد کن.

ترجیحاً:

resources/views/components/social-icon.blade.php

یا در naming convention واقعی پروژه.

ورودی:

name

پشتیبانی:

instagram
telegram
whatsapp
eitaa
bale
rubika
website

Default:
link/globe icon

از SVG inline استفاده کن.

Dependency جدید نصب نکن.
FontAwesome نصب نکن.
CDN جدید اضافه نکن.

برای برندهایی که SVG دقیق موجود پروژه دارد، همان را reuse کن.
اگر ندارد، SVG ساده و معتبر inline استفاده کن.

--------------------------------------------------
E-2) Render شبکه اجتماعی
--------------------------------------------------

اگر URL شبکه خالی است:
آن شبکه اصلاً Render نشود.

target:
_blank

و:

rel="noopener noreferrer"

برای لینک خارجی حفظ شود.

Label فارسی فعلی شبکه‌ها حفظ شود.

--------------------------------------------------
E-3) Hover شبکه‌ها
--------------------------------------------------

Hover شبکه اجتماعی:

- خوانا
- رسمی
- بدون transform شدید
- بدون shadow سنگین

می‌توان accent ملایم متناسب با شبکه داشت، اما طراحی کلی سایت سازمانی باقی بماند.

==================================================
F) president_buttons را کامل کن
==================================================

سیستم فعلی president_buttons را حفظ کن.

طبق Requestها این داده‌ها وجود دارند:

title
url
icon
target
is_active

مشکل:
icon در Frontend کامل استفاده نمی‌شود.

--------------------------------------------------
F-1) Icon mapping
--------------------------------------------------

برای Iconهای عمومی یک Component reusable بساز یا از Component Icon موجود پروژه استفاده کن.

پشتیبانی حداقل:

phone
mobile
email
website
link
external
telegram
whatsapp
document
message

اگر نام Icon ناشناخته بود:
fallback مناسب مثل link.

--------------------------------------------------
F-2) Render
--------------------------------------------------

هر دکمه فعال رئیس:

Icon + Title

نمایش دهد.

URL و target فعلی حفظ شوند.

اگر is_active false:
Render نشود.

==================================================
G) Icon کمیسیون‌ها
==================================================

Commission data فعلی دارای icon است.

Frontend فعلی احتمالاً فقط شماره:

1
2
3
...

را نمایش می‌دهد.

رفتار جدید:

اگر commission.icon مقدار معتبر داشت:
Icon نمایش بده.

اگر icon خالی بود:
شماره ترتیب کمیسیون مثل وضعیت فعلی نمایش داده شود.

Icon ثبت‌شده در Admin نباید بلااستفاده بماند.

از همان UI icon mapper reusable استفاده کن.

==================================================
H) Icon قوانین و آموزش‌ها
==================================================

بررسی کن کدام Related Contentها مثل:

rules
educations

دارای icon قابل مدیریت در Admin هستند.

اگر Frontend آن را نادیده می‌گیرد:
از icon ثبت‌شده استفاده کن.

Fallback SVG فعلی حفظ شود.

برای هر بخش Component جدا و تکراری نساز.
ترجیحاً یک mapper مرکزی UI Icon داشته باش.

==================================================
I) services_enabled را واقعاً فعال کن
==================================================

فیلد:

services_enabled

در Admin/Validation وجود دارد.

بررسی کن الان دقیقاً کجا ذخیره می‌شود.

Section:

#guild-services

باید به این setting احترام بگذارد.

اگر services_enabled false است:
Section خدمات و محتوای اتحادیه Render نشود.

اگر true است:
رفتار فعلی حفظ شود.

اما:
خود Section اگر هیچ آیتم قابل نمایش ندارد نباید یک Box خالی ایجاد کند.

شرط موجود data availability را با services_enabled ترکیب کن.

==================================================
J) show_news_slider قدیمی
==================================================

فیلد/Setting قدیمی:

show_news_slider

را Drop نکن.
Migration حذف نساز.
داده قدیمی را پاک نکن.

ابتدا بررسی کن دقیقاً:
- کجا در Admin نمایش داده می‌شود.
- کجا ذخیره می‌شود.
- کجا در Frontend استفاده می‌شود.

چون اخبار اتحادیه اکنون News List است نه Slider، UI مدیریت نباید دو گزینه گیج‌کننده برای یک مفهوم داشته باشد.

هدف:

در Admin یک گزینه واضح داشته باش:

«نمایش اخبار اتحادیه»

که با show_news کار کند.

show_news_slider را به عنوان compatibility legacy نگه دار ولی در UI اصلی نمایش نده، مگر ساختار پروژه به آن وابسته باشد.

Frontend News List جدید باید حفظ شود.

مهم:
اگر رکوردهای قدیمی فقط show_news_slider=true دارند و show_news=false، باعث ناپدید شدن ناگهانی اخبار قدیمی نشو.

برای backward compatibility یک strategy امن اجرا کن.

ترجیح:

- show_news منبع اصلی جدید باشد.
- legacy value فقط برای رکوردهای قدیمی fallback باشد.
- هنگام Save جدید، وضعیت را به شکل سازگار normalize کن، بدون حذف Column.

قبل از کدنویسی منطق دقیق موجود را بررسی کن و کم‌ریسک‌ترین راه را انتخاب کن.

==================================================
K) ترتیب Sectionهای صفحه تکی اتحادیه
==================================================

ترتیب مورد انتظار محتوای اصلی:

guild-services
↓
guild-manager
↓
guild-board
↓
guild-commissions
↓
guild-news
↓
guild-complaint
↓
guild-contact

مهم:

#guild-manager
و
#guild-board

باید پشت سر هم باشند.

هیچ Section دیگری بین رئیس و هیئت‌مدیره نباشد.

شرط‌های نمایش داینامیک هر Section حفظ شوند.

==================================================
L) اخبار اتحادیه را خراب نکن
==================================================

بخش:

#guild-news

قبلاً به News Row جدید تبدیل شده و تأیید شده است.

ساختار فعلی شامل Classهایی شبیه:

guild-profile-news-list

و selectorهای scoped:

#guild-news ...

را حفظ کن.

به هیچ وجه اخبار اتحادیه را دوباره به Card Grid قبلی برنگردان.

ظاهر فعلی News Row:
- تصویر کوچک سمت راست
- تاریخ
- عنوان
- خلاصه
- separator
- بدون shadow سنگین

باید حفظ شود.

Query خبرهای مرتبط هم تغییر نکند مگر برای compatibility تنظیمات نمایش.

==================================================
M) رفع باگ Hover دکمه‌ها
==================================================

تمام دکمه‌های صفحه تکی اتحادیه را Audit کن.

مشکل فعلی:
در بعضی دکمه‌ها مثل «ثبت شکایت»، هنگام hover متن سفید می‌شود ولی background سفید باقی می‌ماند.

ریشه CSS را پیدا کن.

احتمالاً Rule عمومی مانند:

a:hover {
    color: inherit;
}

با Parent سفید/تیره conflict ایجاد می‌کند.

فقط symptom را با !important زیاد مخفی نکن.
Cascade را درست حل کن.

برای Buttonهای صفحه اتحادیه Stateهای زیر را صریح تعریف کن:

default
hover
focus-visible
active

حداقل برای:

.guild-profile-btn--light
.guild-profile-btn--outline
دکمه‌های Manager
Social buttons
Quickbar links
Contact links

--------------------------------------------------
M-1) Light button
--------------------------------------------------

رفتار نمونه:

default:
background سفید
text سرمه‌ای

hover:
background خاکستری/آبی بسیار روشن
text همچنان سرمه‌ای

هیچ حالت سفید روی سفید ایجاد نشود.

--------------------------------------------------
M-2) Outline button
--------------------------------------------------

hover:
background روشن یا نیمه‌شفاف مناسب
text contrast مناسب

--------------------------------------------------
M-3) Accessibility
--------------------------------------------------

focus-visible:
outline یا box-shadow ظریف و واضح

Contrast مناسب حفظ شود.

==================================================
N) CSS Consolidation
==================================================

این بخش بسیار مهم است.

styles.css در طول توسعه چند بار برای صفحه اتحادیه Override گرفته است.

قبل از اضافه‌کردن CSS:

تمام تعریف‌های تکراری این selectorها را پیدا کن:

.guild-profile-hero
.guild-profile-btn
.guild-profile-btn--light
.guild-profile-btn--outline
.guild-profile-quickbar
.guild-profile-section
.guild-profile-manager
.guild-profile-manager__actions
.guild-profile-members
.guild-profile-post
.guild-profile-contact-card
.guild-profile-socials
.guild-profile-sidebar

و selectorهای responsive مرتبط.

فقط یک Block override جدید در آخر فایل اضافه نکن.

قوانین مربوط به صفحه اتحادیه را تا حد امن consolidate کن.

هدف:
برای هر component یک source-of-truth مشخص در CSS وجود داشته باشد.

اما:

CSS بخش‌های دیگر سایت را refactor نکن.

به این‌ها دست نزن:

Homepage
#latest-news
#representatives
#chamber-members
#multimedia
Single Post
Header
Footer
Ticker

home-layout-lock.css را فقط در صورت وجود Rule واقعی مربوط به guild بررسی کن.
قوانین Homepage آن را تغییر نده.

از !important جدید تا حد ممکن استفاده نکن.

==================================================
O) وضعیت خالی داده‌ها
==================================================

صفحه اتحادیه باید با داده ناقص هم تمیز بماند.

بررسی کن:

بدون cover:
fallback درست

بدون logo:
fallback درست

بدون manager_image:
avatar/fallback فعلی

بدون manager_description:
p خالی نمایش داده نشود

بدون social link:
دکمه آن شبکه Render نشود

بدون commission icon:
شماره نمایش داده شود

بدون president button:
container خالی Render نشود

services_enabled=false:
services section مخفی

بدون news:
guild-news خالی Render نشود مگر رفتار فعلی عمدی باشد

هیچ empty card یا empty heading ایجاد نشود.

==================================================
P) Admin UX
==================================================

فرم مدیریت اتحادیه را پیچیده نکن.

فیلدهای مرتبط را Group کن:

1. هویت اتحادیه
- عنوان
- نوع
- تصویر اصلی
- لوگو

2. رئیس اتحادیه
- نام
- سمت
- تصویر
- معرفی کوتاه
- دکمه‌های رئیس

3. اطلاعات تماس

4. شبکه‌های اجتماعی

5. محتوای صفحه
- خدمات
- اخبار
- کمیسیون‌ها
- ...

Labelها فارسی و شفاف باشند.

هیچ قابلیت موجودی را حذف نکن.

==================================================
Q) Security / Validation
==================================================

Validation upload موجود را حفظ کن.

برای manager_image:
image
حداکثر حجم منطقی فعلی

URLهای Social:
validation فعلی URL حفظ شود.

هیچ HTML خام از manager_description بدون escaping چاپ نکن، مگر پروژه برای متن Rich Text سیستم Sanitization مشخص دارد.

اگر textarea ساده است:
با Blade escaped output نمایش بده.

==================================================
R) فایل‌هایی که ممکن است تغییر کنند
==================================================

بر اساس ساختار واقعی پروژه، احتمالاً فایل‌هایی از این دسته‌ها تغییر می‌کنند:

GuildUnion Model

Admin Union Controller
Admin Union Member Controller

StoreUnionRequest
UpdateUnionRequest
StoreUnionMemberRequest
UpdateUnionMemberRequest

Admin union create/edit form
Admin union member create/edit form

resources/views/frontend/guilds/show.blade.php
Views مربوط به list/archive/search unions

Componentهای جدید Icon

public_html/assets/css/styles.css

یک Migration additive برای:
manager_position
manager_description

اما فقط فایل‌هایی را تغییر بده که واقعاً لازم هستند.

main.js نباید تغییر کند مگر ضرورت واقعی پیدا کنی.

==================================================
S) تست‌های اجباری Backend
==================================================

بعد از تغییر:

php artisan migrate --pretend

را بررسی کن.

Migration را روی DB واقعی خودکار اجرا نکن مگر محیط پروژه مشخصاً لوکال و امن باشد.
فقط دستور لازم را گزارش بده.

سپس:

php artisan route:list

و:

php artisan view:cache

اگر موفق بود:

php artisan view:clear

PHP syntax تمام فایل‌های PHP تغییرکرده را بررسی کن.

==================================================
T) تست‌های سناریویی
==================================================

حداقل این سناریوها را بررسی کن:

1.
اتحادیه با:
cover + logo

Archive:
cover

Single Hero background:
cover

Single Emblem:
logo

2.
اتحادیه:
cover دارد
logo ندارد

کارت:
cover

Hero:
cover

Emblem:
fallback مناسب

3.
اتحادیه:
cover ندارد
logo دارد

کارت:
logo fallback

Hero:
logo fallback یا رفتار مناسب فعلی

4.
رئیس:
نام + سمت + عکس + توضیح کامل

همه نمایش داده شوند.

5.
رئیس:
فقط نام

هیچ متن Placeholder مصنوعی نمایش داده نشود.

6.
هیئت‌مدیره:
چند عضو با sort_order متفاوت

ترتیب صحیح باشد.

7.
Social:
Telegram + WhatsApp + Bale

هر کدام Icon صحیح داشته باشند.

8.
Social URL خالی:
Render نشود.

9.
Commission با icon:
icon نمایش داده شود.

10.
Commission بدون icon:
شماره نمایش داده شود.

11.
services_enabled=false:
guild-services Render نشود.

12.
اخبار اتحادیه:
News Row فعلی بدون Regression.

13.
دکمه ثبت شکایت:
Default/Hover/Focus خوانا باشد.

==================================================
U) تست Responsive
==================================================

صفحه تکی اتحادیه را حداقل در:

1440
1200
1024
768
506
375

بررسی کن.

موارد مهم:

- Hero سالم
- Manager card سالم
- Board سالم
- Commission سالم
- News Row سالم
- Contact سالم
- Social buttons wrap مناسب
- Button hover/focus
- هیچ horizontal overflow
- متن شبکه‌های اجتماعی و Iconها overlap نکنند

==================================================
V) Regression Test
==================================================

بعد از تغییر حتماً بررسی کن:

Homepage تغییر نکرده.

آخرین اخبار Homepage تغییر نکرده.

صفحه تکی خبر تغییر نکرده.

صفحه آرشیو اخبار تغییر نکرده.

Header تغییر نکرده.

Footer تغییر نکرده.

Ticker تغییر نکرده.

اخبار اتحادیه همچنان مرتبط با همان اتحادیه هستند.

هیچ داده‌ای حذف نشده.

==================================================
W) Git Safety
==================================================

در پایان:

git status --short
git diff --stat
git diff --check

را اجرا کن.

git diff را بررسی کن که هیچ تغییر unrelated وجود نداشته باشد.

هیچ commit نساز.

هیچ git reset اجرا نکن.

هیچ فایل unrelated را restore نکن.

==================================================
X) خروجی نهایی Codex
==================================================

در پایان یک گزارش ساختاریافته بده:

1. فایل‌های تغییرکرده
2. Migration ساخته‌شده و Columnهای آن
3. تغییر Model
4. تغییر Requestها
5. تغییر Controllerها
6. تغییر Admin form
7. منطق جدید تصویر اتحادیه
8. وضعیت manager fields
9. وضعیت هیئت‌مدیره position/sort_order
10. شبکه‌های اجتماعی و Icon mapping
11. president_buttons
12. commission/rule/education icons
13. services_enabled
14. نحوه compatibility با show_news_slider
15. تغییر ترتیب Sectionها
16. CSSهایی که consolidate شدند
17. نتیجه Hover buttons
18. نتیجه view:cache
19. نتیجه git diff --check
20. تست‌های دستی باقی‌مانده

همچنین مشخص کن:

- آیا JS تغییر کرد؟
- آیا Route تغییر کرد؟
- آیا DB Migration لازم است؟
- چه دستور دقیقی باید بعداً روی سرور اجرا شود؟

هیچ ZIP نساز.
هیچ commit انجام نده.
هیچ Migration را روی Production اجرا نکن.<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');
