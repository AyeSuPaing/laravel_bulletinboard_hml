# # Laravel Api

# # # app/
Laravel ရဲ့ အဓိက Application Logic တွေကို စုထားတဲ့ folder ဖြစ်ပါတယ်။ ဒီထဲမှာ subfolders များစွာရှိပါတယ်။

- Constants/ - GeneralConst.php: App တစ်ခုလုံးမှာ အသုံးပြုတဲ့ constant value (e.g. status codes, user roles) တွေကို ထားရာ file ဖြစ်ပါတယ်။
- Console/ - Artisan command များ (php artisan ...) ကို custom လုပ်ဖို့ Command classes များကို ထားတတ်သည်။
- Exceptions/ - Error/Exception Handling ကို စီမံတဲ့နေရာ။ Handler.php ပါဝင်သည်။

- Http/
- - Controllers/: Controller classes များကို ထားသည်။
- - Controllers/Controller.php: အခြား controller တွေအားလုံးရဲ့ base class ဖြစ်တတ်ပါတယ်။ဖြစ်ပါတယ်။
- - Controllers/Api/: Api တွေနဲ့ဆိုင်တဲ့ controller များထားဖို့နေရာ ဖြစ်နိုင်ပါတယ်။
- - Middleware/: Request တစ်ခုလာသောအခါ ထိန်းချုပ်ရန် middleware များ။
- - Middleware/Authenticate.php: Laravel built-in middleware တစ်ခုဖြစ်ပြီး authentication မရှိရင် login page သို့ redirect လုပ်ပါတယ်။
- - Middleware/Api/: Api Request တစ်ခုလာသောအခါ ထိန်းချုပ်ရန် middleware များ။
- - Requests/Api/: Api Request တစ်ခုအတွက် validationစစ်ရန် Request File များ။
- - Resources/Api/: Api Response တစ်ခုအတွက် formatပြင်ရန် Resource File များ။

- Lib/
- - Lib/DateFormat.php: Custom date formatting logic များကို ထားတဲ့ helper class တစ်ခုဖြစ်နိုင်ပါတယ်။

- Models/
- - Models/Post.php: Post table နဲ့ဆက်နွယ်တဲ့ Eloquent Model
- - Models/User.php: User table နဲ့ဆက်နွယ်တဲ့ Model (users table)

- Providers/
- - Providers/AppServiceProvider.php: Laravel service provider တစ်ခု ဖြစ်နိုင်ပြီး, App bootstrapping အတွက် service binding များလုပ်ပါတယ်။

- Services/: Controller တွေကို ပေါ့ပါးအောင်ထားပြီး logic ကို service class ထဲတွင် centralize ပြုလုပ်ခြင်း။
- - Services/Api/: Business Logic (ဥပမာ: custom validation, user-specific permission check, external API call) များကို ထည့်သွင်းထားနိုင်တယ်။

- Traits/: classတွေအတွက် Avoid Code Duplication, Clean & Modular, Easy Maintenanceဖြစ်အောင် Featuresအသစ်တွေကို အလွယ်တကူ ပြုလုပ်ပေးနိုင်တယ်
- - Traits/ApiResponseTrait/: Api response အတွက် success, paginate, error, etcတို့ကို  ကြိုက်တဲ့ နေရာက ခေါ်သုံးနိုင်ဖို့ traitနဲ့ ရေးထားတာ


# # # public/
Web Server Access ပြုနိုင်သောနေရာ

- img/: Website သုံးတဲ့ ပုံများအတွက် folder

- index.php: Laravel app entry point
- .htaccess: Apache server config
- favicon.ico, robots.txt: SEO နှင့် browser support


# # # resources/
- css/, js/: Laravel Mix or Vite မှ Compile ပြုလုပ်မယ့် source files

- views/: Blade Templates

# # # routes/
- routes/: web.php, api.php စသည်ဖြင့် route definition files


# # # storage/
- storage/: Logs, Cache, File Upload အတွက်


# # # tests/
- tests/: PHP Unit Testing files

# # # others
- .env: App config for current environment

- composer.json: PHP packages list

- package.json: JS packages (Vue/React etc.) list

- artisan: Laravel CLI command file

- .gitignore, .editorconfig: Git နှင့် coding standard

- README.md: Project Description

- vite.config.js: Laravel application တွင် Vite ကိုအသုံးပြုသည့်အခါ frontend asset bundling (JavaScript, CSS, Vue, React…) အတွက် configuration ဖိုင်ဖြစ်ပါတယ်။
