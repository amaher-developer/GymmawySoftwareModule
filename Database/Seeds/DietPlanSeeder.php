<?php

namespace Modules\Software\Database\Seeds;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Modules\Software\Models\GymStoreCategory;
use Modules\Software\Models\GymStoreProduct;
use Modules\Software\Models\GymSubscriptionCategory;
use Modules\Software\Models\GymSubscription;
use Modules\Software\Models\GymSubscriptionOptionGroup;
use Modules\Software\Models\GymSubscriptionOption;
use Modules\Software\Models\GymSubscriptionProduct;

/**
 * DietPlanSeeder
 *
 * Seeds a full diet-plan subscription system matching the website / mobile flow:
 *
 *   Step 1 — Subscription options (days, protein weight, carb weight, type, add-ons)
 *   Step 2 — Meal selection per category tab (main meal, snacks, drinks, juices, salads)
 *
 * Run:
 *   php artisan db:seed --class=Modules\\Software\\Database\\Seeds\\DietPlanSeeder
 *
 * Safe to re-run (idempotent — uses firstOrCreate throughout).
 */
class DietPlanSeeder extends Seeder
{
    private int $branchId = 1;
    private int $userId   = 1;

    // Fallback image per store-category key
    private array $categoryImages = [
        'main_meal'     => 'https://images.unsplash.com/photo-1546069901-ba9599a7e63c?w=400&h=300&fit=crop&q=80',
        'snacks'        => 'https://images.unsplash.com/photo-1508061253366-f7da158b6d46?w=400&h=300&fit=crop&q=80',
        'drinks'        => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=400&h=300&fit=crop&q=80',
        'juices'        => 'https://images.unsplash.com/photo-1543163521-1bf539c55dd2?w=400&h=300&fit=crop&q=80',
        'salad'         => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop&q=80',
        'protein_salad' => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&h=300&fit=crop&q=80',
        'fruit_salad'   => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?w=400&h=300&fit=crop&q=80',
    ];

    // Per-product overrides keyed by Arabic name
    private array $mealImages = [
        'دجاج ديناميت مقلي'        => 'https://images.unsplash.com/photo-1562967914-608f82629710?w=400&h=300&fit=crop&q=80',
        'دجاج كلاسيك بالكريما'     => 'https://images.unsplash.com/photo-1632778149955-e80f8ceca2e8?w=400&h=300&fit=crop&q=80',
        'دجاج مقلقل'               => 'https://images.unsplash.com/photo-1598103442097-8b74394b95c8?w=400&h=300&fit=crop&q=80',
        'دجاج ديناميت مشوي'        => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?w=400&h=300&fit=crop&q=80',
        'دجاج هندي سبايسي بالكريما'=> 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400&h=300&fit=crop&q=80',
        'دجاج مشوي بالملوخية'      => 'https://images.unsplash.com/photo-1603360946369-dc9bb6258143?w=400&h=300&fit=crop&q=80',
        'دجاج تكة أعواد مشوي'      => 'https://images.unsplash.com/photo-1529193591184-b1d58069ecdd?w=400&h=300&fit=crop&q=80',
        'دجاج إيطالي مشوي'         => 'https://images.unsplash.com/photo-1580476262798-bddd9f4b7369?w=400&h=300&fit=crop&q=80',
        'لحم مشوي سادة'            => 'https://images.unsplash.com/photo-1558030006-b2a7cffaa61b?w=400&h=300&fit=crop&q=80',
        'لحم مشوي بالكريما'        => 'https://images.unsplash.com/photo-1558030006-b2a7cffaa61b?w=400&h=300&fit=crop&q=80',
        'سمك مشوي سادة'            => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400&h=300&fit=crop&q=80',
        'سمك مشوي بالأعشاب'       => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400&h=300&fit=crop&q=80',
        'ستيك لحم بقري'            => 'https://images.unsplash.com/photo-1544025162-d76538897a07?w=400&h=300&fit=crop&q=80',
        'دجاج مشوي بالكاري'        => 'https://images.unsplash.com/photo-1585937421612-70a008356fbe?w=400&h=300&fit=crop&q=80',
        'صدر دجاج مشوي سادة'       => 'https://images.unsplash.com/photo-1604908176997-125f25cc6f3d?w=400&h=300&fit=crop&q=80',
        'دجاج مشوي بالليمون'       => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?w=400&h=300&fit=crop&q=80',
        'دجاج مشوي بالثوم'         => 'https://images.unsplash.com/photo-1532550907401-a500c9a57435?w=400&h=300&fit=crop&q=80',
        'تونة مشوية بالأعشاب'      => 'https://images.unsplash.com/photo-1519708227418-c8fd9a32b7a2?w=400&h=300&fit=crop&q=80',
        'بار بروتين'               => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?w=400&h=300&fit=crop&q=80',
        'مكسرات مشكلة'             => 'https://images.unsplash.com/photo-1508061253366-f7da158b6d46?w=400&h=300&fit=crop&q=80',
        'شوكولاتة داركة'           => 'https://images.unsplash.com/photo-1606312619070-d48b4c652a52?w=400&h=300&fit=crop&q=80',
        'حمص بالطحينة'             => 'https://images.unsplash.com/photo-1571197190672-39f8f6b1b3e5?w=400&h=300&fit=crop&q=80',
        'فول مدمس'                 => 'https://images.unsplash.com/photo-1546793665-c74683f339c1?w=400&h=300&fit=crop&q=80',
        'عصير الرمان'              => 'https://images.unsplash.com/photo-1576673442511-7e39b6545c87?w=400&h=300&fit=crop&q=80',
        'عصير برتقال'              => 'https://images.unsplash.com/photo-1600271886742-f049cd451bba?w=400&h=300&fit=crop&q=80',
        'عصير الافوكادو'           => 'https://images.unsplash.com/photo-1623065422902-30a2d299bbe4?w=400&h=300&fit=crop&q=80',
        'عصير المانجو'             => 'https://images.unsplash.com/photo-1553361371-9b22f78e8b1d?w=400&h=300&fit=crop&q=80',
        'مانجو بالحليب'            => 'https://images.unsplash.com/photo-1553361371-9b22f78e8b1d?w=400&h=300&fit=crop&q=80',
        'كوكتيل فراولة وموز'       => 'https://images.unsplash.com/photo-1638176066959-e27aa2c69e90?w=400&h=300&fit=crop&q=80',
        'سلطة خضار مشكلة'          => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=400&h=300&fit=crop&q=80',
        'سلطة تبولة'               => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400&h=300&fit=crop&q=80',
        'سلطة السيزر'              => 'https://images.unsplash.com/photo-1550304943-4f24f54ddde9?w=400&h=300&fit=crop&q=80',
        'سلطة رجة'                 => 'https://images.unsplash.com/photo-1573879541250-58ae8b322b40?w=400&h=300&fit=crop&q=80',
        'سلطة التونة'              => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&h=300&fit=crop&q=80',
        'سلطة الدجاج المشوي'       => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&h=300&fit=crop&q=80',
        'سلطة البيض'               => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&h=300&fit=crop&q=80',
        'سلطة السلمون'             => 'https://images.unsplash.com/photo-1547592166-23ac45744acd?w=400&h=300&fit=crop&q=80',
        'فواكه مشكلة'              => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?w=400&h=300&fit=crop&q=80',
        'سلطة فواكه بالعسل'        => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?w=400&h=300&fit=crop&q=80',
        'مياه غازية'               => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=400&h=300&fit=crop&q=80',
        'ليموناضة غازية'           => 'https://images.unsplash.com/photo-1621263764928-df1444c5e859?w=400&h=300&fit=crop&q=80',
        'كولا دايت'                => 'https://images.unsplash.com/photo-1554866585-cd94860890b7?w=400&h=300&fit=crop&q=80',
    ];

    // Image per subscription-category key (for GymSubscriptionCategory records)
    private array $subscriptionCategoryImages = [
        'amplification'  => 'https://images.unsplash.com/photo-1547592180-85f173990554?w=600&h=400&fit=crop&q=80',
        'cutting'        => 'https://images.unsplash.com/photo-1490645935967-10de6ba17061?w=600&h=400&fit=crop&q=80',
        'business_lunch' => 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=600&h=400&fit=crop&q=80',
        'keto'           => 'https://images.unsplash.com/photo-1555939594-58d7cb561ad1?w=600&h=400&fit=crop&q=80',
        'diabetes'       => 'https://images.unsplash.com/photo-1512621776951-a57141f2eefd?w=600&h=400&fit=crop&q=80',
    ];

    // Image per subscription-category key (for GymSubscription records — one file shared across all tiers)
    private array $subscriptionImages = [
        'amplification'  => 'https://images.unsplash.com/photo-1605296867304-46d5465a13f1?w=600&h=400&fit=crop&q=80',
        'cutting'        => 'https://images.unsplash.com/photo-1598515213513-30ee4a68c3d1?w=600&h=400&fit=crop&q=80',
        'business_lunch' => 'https://images.unsplash.com/photo-1504674900247-0877df9cc836?w=600&h=400&fit=crop&q=80',
        'keto'           => 'https://images.unsplash.com/photo-1604908177522-2cbe9e9f7e40?w=600&h=400&fit=crop&q=80',
        'diabetes'       => 'https://images.unsplash.com/photo-1490474418585-ba9bad8fd0ea?w=600&h=400&fit=crop&q=80',
    ];

    // ─────────────────────────────────────────────────────────────────────────
    public function run(): void
    {
        $this->clearOldData();

        $storeCats = $this->seedStoreCategories();
        $subCats   = $this->seedSubscriptionCategories();
        $products  = $this->seedStoreProducts($storeCats);
        $this->seedSubscriptions($subCats, $storeCats, $products);

        $this->command->info('✅ DietPlanSeeder completed successfully.');
    }

    // ── 0. Clear previously seeded data ──────────────────────────────────────

    private function clearOldData(): void
    {
        $this->command->warn('  Clearing old diet plan data...');

        // ── Subscription tree (options → groups → products → subscriptions → categories)
        $subCatNames = ['باقة الضخامة', 'باقة النشافة', 'باقة غداء العمل', 'باقة الكيتو', 'باقة السكري'];
        $categories  = GymSubscriptionCategory::withTrashed()
            ->where('branch_setting_id', $this->branchId)
            ->whereIn('name_ar', $subCatNames)
            ->get();

        $categoryIds     = $categories->pluck('id');
        $subscriptionIds = GymSubscription::withTrashed()
            ->whereIn('subscription_category_id', $categoryIds)
            ->pluck('id');
        $groupIds = GymSubscriptionOptionGroup::withTrashed()
            ->whereIn('subscription_id', $subscriptionIds)
            ->pluck('id');

        GymSubscriptionOption::withTrashed()->whereIn('option_group_id', $groupIds)->forceDelete();
        GymSubscriptionOptionGroup::withTrashed()->whereIn('subscription_id', $subscriptionIds)->forceDelete();
        GymSubscriptionProduct::withTrashed()->whereIn('subscription_id', $subscriptionIds)->forceDelete();

        // Delete subscription image files from disk
        GymSubscription::withTrashed()->whereIn('id', $subscriptionIds)->get()
            ->each(function ($sub) {
                $img = $sub->getRawOriginal('image');
                if ($img && !filter_var($img, FILTER_VALIDATE_URL)) {
                    $path = base_path('uploads/subscriptions/' . $img);
                    if (file_exists($path)) @unlink($path);
                }
            });

        GymSubscription::withTrashed()->whereIn('id', $subscriptionIds)->forceDelete();

        foreach ($categories as $cat) {
            $img = $cat->getRawOriginal('image');
            if ($img) {
                $path = base_path('uploads/subscription_categories/' . $img);
                if (file_exists($path)) @unlink($path);
            }
        }
        GymSubscriptionCategory::withTrashed()->whereIn('id', $categoryIds)->forceDelete();

        // ── Store products seeded by us (DIET- code prefix)
        $products = GymStoreProduct::withTrashed()
            ->where('branch_setting_id', $this->branchId)
            ->where('code', 'LIKE', 'DIET-%')
            ->get();

        foreach ($products as $product) {
            $img = $product->getRawOriginal('image');
            if ($img && !filter_var($img, FILTER_VALIDATE_URL)) {
                $path = base_path('uploads/products/' . $img);
                if (file_exists($path)) @unlink($path);
            }
        }
        GymStoreProduct::withTrashed()
            ->where('branch_setting_id', $this->branchId)
            ->where('code', 'LIKE', 'DIET-%')
            ->forceDelete();

        // ── Store categories we own
        $storeCatNames = ['وجبة رئيسية', 'السناكات الفخمه', 'مشروبات غازية', 'عصيرات', 'سلطة', 'سلطة البروتين', 'سلطة الفواكه'];
        GymStoreCategory::withTrashed()
            ->where('branch_setting_id', $this->branchId)
            ->whereIn('name_ar', $storeCatNames)
            ->forceDelete();

        $this->command->info('  ✓ Old data cleared.');
    }

    // ── 1. Store categories (meal types) ─────────────────────────────────────

    private function seedStoreCategories(): array
    {
        $defs = [
            'main_meal'     => ['name_ar' => 'وجبة رئيسية',    'name_en' => 'Main Meal'],
            'snacks'        => ['name_ar' => 'السناكات الفخمه', 'name_en' => 'Premium Snacks'],
            'drinks'        => ['name_ar' => 'مشروبات غازية',   'name_en' => 'Carbonated Drinks'],
            'juices'        => ['name_ar' => 'عصيرات',          'name_en' => 'Juices'],
            'salad'         => ['name_ar' => 'سلطة',            'name_en' => 'Salad'],
            'protein_salad' => ['name_ar' => 'سلطة البروتين',   'name_en' => 'Protein Salad'],
            'fruit_salad'   => ['name_ar' => 'سلطة الفواكه',    'name_en' => 'Fruit Salad'],
        ];

        $result = [];
        foreach ($defs as $key => $d) {
            $result[$key] = GymStoreCategory::firstOrCreate(
                ['branch_setting_id' => $this->branchId, 'name_ar' => $d['name_ar']],
                array_merge($d, ['user_id' => $this->userId, 'branch_setting_id' => $this->branchId])
            );
        }
        return $result;
    }

    // ── 2. Subscription categories (diet plan types) ──────────────────────────

    private function seedSubscriptionCategories(): array
    {
        $defs = [
            'amplification'  => ['name_ar' => 'باقة الضخامة',    'name_en' => 'Amplification Package'],
            'cutting'        => ['name_ar' => 'باقة النشافة',    'name_en' => 'Cutting Package'],
            'business_lunch' => ['name_ar' => 'باقة غداء العمل', 'name_en' => 'Business Lunch Package'],
            'keto'           => ['name_ar' => 'باقة الكيتو',     'name_en' => 'Keto Package'],
            'diabetes'       => ['name_ar' => 'باقة السكري',     'name_en' => 'Diabetes Package'],
        ];

        $result = [];
        foreach ($defs as $key => $d) {
            $remoteUrl = $this->subscriptionCategoryImages[$key] ?? null;
            $imageFile = $remoteUrl ? $this->downloadImage($remoteUrl, 'subcat_' . $key, 'subscription_categories') : null;

            $result[$key] = GymSubscriptionCategory::firstOrCreate(
                ['branch_setting_id' => $this->branchId, 'name_ar' => $d['name_ar']],
                array_merge($d, [
                    'user_id'           => $this->userId,
                    'branch_setting_id' => $this->branchId,
                    'image'             => $imageFile,
                ])
            );
        }
        return $result;
    }

    // ── 3. Store products (meals, drinks, snacks, salads) ────────────────────

    private function seedStoreProducts(array $storeCats): array
    {
        $defs = [

            // ── Main Meals ────────────────────────────────────────────────────
            'main_meal' => [
                // Name (AR)                              // Name (EN)                        Cal  Pro Car Fat
                ['دجاج ديناميت مقلي',        'Fried Dynamite Chicken',           330, 31, 20,  6],
                ['دجاج كلاسيك بالكريما',      'Classic Cream Chicken',            230, 33,  8,  5],
                ['دجاج مقلقل',               'Shakshuka Chicken',                180, 31,  3,  2],
                ['دجاج ديناميت مشوي',         'Grilled Dynamite Chicken',         195, 32,  3,  5],
                ['دجاج هندي سبايسي بالكريما', 'Spicy Indian Cream Chicken',       235, 33,  8,  5],
                ['دجاج مشوي بالملوخية',       'Grilled Chicken with Molokhia',    190, 31,  3,  3],
                ['دجاج تكة أعواد مشوي',       'Grilled Chicken Tikka Skewers',    185, 31,  4,  4],
                ['دجاج إيطالي مشوي',          'Italian Grilled Chicken',          175, 31,  2,  2],
                ['لحم مشوي سادة',             'Plain Grilled Beef',               210, 32,  2,  8],
                ['لحم مشوي بالكريما',          'Cream Grilled Beef',               250, 30,  5, 10],
                ['سمك مشوي سادة',             'Plain Grilled Fish',               170, 32,  1,  4],
                ['سمك مشوي بالأعشاب',        'Herb Grilled Fish',                185, 31,  2,  5],
                ['ستيك لحم بقري',             'Beef Steak',                       320, 35,  0, 12],
                ['دجاج مشوي بالكاري',         'Curry Grilled Chicken',            225, 32,  6,  6],
                ['صدر دجاج مشوي سادة',        'Plain Grilled Chicken Breast',     165, 34,  0,  3],
                ['دجاج مشوي بالليمون',        'Lemon Grilled Chicken',            195, 32,  3,  5],
                ['دجاج مشوي بالثوم',          'Garlic Grilled Chicken',           200, 32,  3,  6],
                ['تونة مشوية بالأعشاب',       'Herb Grilled Tuna',                180, 34,  0,  4],
            ],

            // ── Premium Snacks ────────────────────────────────────────────────
            'snacks' => [
                ['بار بروتين',         'Protein Bar',        250, 25, 20,  6],
                ['مكسرات مشكلة',       'Mixed Nuts',         200, 10,  8, 15],
                ['شوكولاتة داركة',     'Dark Chocolate',     180,  3, 18, 11],
                ['حمص بالطحينة',       'Hummus',             140,  7, 14,  6],
                ['فول مدمس',           'Foul Medames',       160,  9, 22,  4],
            ],

            // ── Carbonated Drinks ─────────────────────────────────────────────
            'drinks' => [
                ['مياه غازية',       'Sparkling Water',    0,  0,  0,  0],
                ['ليموناضة غازية',   'Sparkling Lemonade', 45, 0, 12,  0],
                ['كولا دايت',        'Diet Cola',          1,  0,  0,  0],
            ],

            // ── Juices ────────────────────────────────────────────────────────
            'juices' => [
                ['عصير الرمان',         'Pomegranate Juice',          170,  2, 38,  4],
                ['عصير برتقال',         'Orange Juice',               188,  1, 47,  4],
                ['عصير الافوكادو',      'Avocado Juice',              209, 14, 19,  4],
                ['عصير المانجو',        'Mango Juice',                120,  1, 31,  1],
                ['مانجو بالحليب',       'Mango Milk',                 155,  2, 35,  4],
                ['كوكتيل فراولة وموز',  'Strawberry Banana Cocktail', 154,  1, 35,  4],
            ],

            // ── Salad ─────────────────────────────────────────────────────────
            'salad' => [
                ['سلطة خضار مشكلة',  'Mixed Vegetable Salad',  85,  4, 12,  2],
                ['سلطة تبولة',       'Tabbouleh Salad',         95,  3, 15,  3],
                ['سلطة السيزر',      'Caesar Salad',           230, 13, 16,  9],
                ['سلطة رجة',         'Greek Salad',            110,  5,  9,  7],
            ],

            // ── Protein Salad ─────────────────────────────────────────────────
            'protein_salad' => [
                ['سلطة التونة',          'Tuna Salad',            180, 22,  5,  8],
                ['سلطة الدجاج المشوي',   'Grilled Chicken Salad', 210, 25,  8,  9],
                ['سلطة البيض',           'Egg Salad',             165, 15,  3, 11],
                ['سلطة السلمون',         'Salmon Salad',          220, 20,  4, 12],
            ],

            // ── Fruit Salad ───────────────────────────────────────────────────
            'fruit_salad' => [
                ['فواكه مشكلة',          'Mixed Fruits',          90,  1, 22,  0],
                ['سلطة فواكه بالعسل',    'Honey Fruit Salad',    120,  1, 30,  0],
            ],
        ];

        $result = [];
        foreach ($defs as $catKey => $items) {
            $storeCategory = $storeCats[$catKey];
            $result[$catKey] = [];

            foreach ($items as [$nameAr, $nameEn, $cal, $pro, $car, $fat]) {
                // Stable unique code derived from the Arabic name
                $code        = 'DIET-' . substr(md5($nameAr), 0, 8);
                $remoteUrl   = $this->mealImages[$nameAr] ?? $this->categoryImages[$catKey] ?? null;
                $imageFile   = $remoteUrl ? $this->downloadImage($remoteUrl, $nameAr, 'products') : null;

                $product = GymStoreProduct::withTrashed()
                    ->where('branch_setting_id', $this->branchId)
                    ->where('code', $code)
                    ->first();

                if ($product) {
                    if ($product->trashed()) {
                        $product->restore();
                    }
                    // Backfill image if the record has none yet
                    if ($imageFile && !$product->getRawOriginal('image')) {
                        $product->update(['image' => $imageFile]);
                    }
                } else {
                    $product = GymStoreProduct::create([
                        'user_id'           => $this->userId,
                        'branch_setting_id' => $this->branchId,
                        'name_ar'           => $nameAr,
                        'name_en'           => $nameEn,
                        'display_name_ar'   => $nameAr,
                        'display_name_en'   => $nameEn,
                        'price'             => 0,
                        'quantity'          => 9999,
                        'code'              => $code,
                        'is_meal'           => true,
                        'calories'          => $cal,
                        'protein'           => $pro,
                        'carbs'             => $car,
                        'fat'               => $fat,
                        'is_web'            => true,
                        'is_mobile'         => true,
                        'is_system'         => true,
                        'category_id'       => $storeCategory->id,
                        'image'             => $imageFile,
                    ]);
                }

                $result[$catKey][] = $product;
            }
        }

        return $result;
    }

    // ── 4. Subscriptions (25 plans: 5 categories × 5 meal-count tiers) ───────

    private function seedSubscriptions(array $subCats, array $storeCats, array $products): void
    {
        // Meal tiers ─ number of main meals included per day
        $tiers = [
            1 => ['ar' => '1 وجبة رئيسية',  'en' => '1 Main Meal'],
            2 => ['ar' => '2 وجبة رئيسية',  'en' => '2 Main Meals'],
            3 => ['ar' => '3 وجبة رئيسية',  'en' => '3 Main Meals'],
            4 => ['ar' => '4 وجبة رئيسية',  'en' => '4 Main Meals'],
            5 => ['ar' => '5 وجبة رئيسية',  'en' => '5 Main Meals'],
        ];

        // Daily rate (SAR) per meal per category — calibrated so:
        //   النشافة, 2 meals, 26 days = 16 × 2 × 26 = 832 SAR ✓
        $dailyRatePerMeal = [
            'amplification'  => 17.6,   // higher-calorie bulk plan
            'cutting'        => 16.0,   // base cutting plan
            'business_lunch' => 13.0,   // lighter / single-meal focus
            'keto'           => 20.0,   // premium ingredients
            'diabetes'       => 21.0,   // specialised medical-grade
        ];

        $dayOptions = [20, 26, 30];

        foreach ($subCats as $catKey => $category) {
            $rate = $dailyRatePerMeal[$catKey] ?? 16.0;

            // Download one image per category, shared across all tier subscriptions
            $remoteUrl = $this->subscriptionImages[$catKey] ?? null;
            $imageFile = $remoteUrl ? $this->downloadImage($remoteUrl, 'sub_' . $catKey, 'subscriptions') : null;

            foreach ($tiers as $mealCount => $tier) {
                $nameAr = $category->name_ar . ' - ' . $tier['ar'];
                $nameEn = $category->name_en . ' - ' . $tier['en'];

                $subscription = GymSubscription::firstOrCreate(
                    ['branch_setting_id' => $this->branchId, 'name_ar' => $nameAr],
                    [
                        'user_id'                  => $this->userId,
                        'branch_setting_id'        => $this->branchId,
                        'name_ar'                  => $nameAr,
                        'name_en'                  => $nameEn,
                        'price'                    => 0,
                        'period'                   => 30,
                        'workouts'                 => $mealCount * 30,
                        'freeze_limit'             => 5,
                        'number_times_freeze'      => 2,
                        'is_expire_changeable'     => true,
                        'is_system'                => true,
                        'is_web'                   => true,
                        'is_mobile'                => true,
                        'subscription_category_id' => $category->id,
                        'content_ar'               => 'باقة غذائية متكاملة تشمل ' . $tier['ar'] . ' يومياً',
                        'content_en'               => 'Complete diet plan including ' . $tier['en'] . ' daily',
                        'image'                    => $imageFile,
                    ]
                );

                $this->seedOptionGroups($subscription, $storeCats, $products, $mealCount, $rate, $dayOptions);
                $this->seedSubscriptionProducts($subscription, $products['main_meal']);
            }
        }
    }

    // ── Option groups for one subscription ───────────────────────────────────

    private function seedOptionGroups(
        GymSubscription $subscription,
        array $storeCats,
        array $products,
        int $mealCount,
        float $ratePerMealPerDay,
        array $dayOptions
    ): void {
        // ── Group 1: عدد الأيام ───────────────────────────────────────────────
        $daysGroup = $this->upsertGroup($subscription, [
            'name_ar'        => 'عدد الأيام',
            'name_en'        => 'Number of Days',
            'selection_type' => GymSubscriptionOptionGroup::SELECTION_SINGLE,
            'is_required'    => true,
            'list_order'     => 1,
            'source_type'    => 'fixed',
        ]);

        $dayLabelAr = [20 => '20 يوم', 26 => '26 يوم', 30 => '30 يوم'];
        $dayLabelEn = [20 => '20 Days', 26 => '26 Days', 30 => '30 Days'];

        foreach ($dayOptions as $i => $days) {
            $price = (int) round($mealCount * $ratePerMealPerDay * $days);
            $this->upsertFixedOption($daysGroup, $dayLabelAr[$days], $dayLabelEn[$days], $price, $i + 1);
        }

        // ── Group 2: وزن البروتين ─────────────────────────────────────────────
        $proteinGroup = $this->upsertGroup($subscription, [
            'name_ar'        => 'وزن البروتين (جرام/الوجبة)',
            'name_en'        => 'Protein Weight (g/meal)',
            'selection_type' => GymSubscriptionOptionGroup::SELECTION_SINGLE,
            'is_required'    => true,
            'list_order'     => 2,
            'source_type'    => 'fixed',
        ]);

        foreach ([
            [1, '100 جرام/الوجبة', '100g/meal',   0],
            [2, '150 جرام/الوجبة', '150g/meal',  50],
            [3, '200 جرام/الوجبة', '200g/meal', 100],
            [4, '250 جرام/الوجبة', '250g/meal', 150],
        ] as [$ord, $ar, $en, $price]) {
            $this->upsertFixedOption($proteinGroup, $ar, $en, $price, $ord);
        }

        // ── Group 3: وزن الكارب ───────────────────────────────────────────────
        $carbGroup = $this->upsertGroup($subscription, [
            'name_ar'        => 'وزن الكارب (جرام/الوجبة)',
            'name_en'        => 'Carb Weight (g/meal)',
            'selection_type' => GymSubscriptionOptionGroup::SELECTION_SINGLE,
            'is_required'    => true,
            'list_order'     => 3,
            'source_type'    => 'fixed',
        ]);

        foreach ([
            [1, '100 جرام/الوجبة', '100g/meal',   0],
            [2, '150 جرام/الوجبة', '150g/meal',  40],
            [3, '200 جرام/الوجبة', '200g/meal',  80],
            [4, '250 جرام/الوجبة', '250g/meal', 120],
        ] as [$ord, $ar, $en, $price]) {
            $this->upsertFixedOption($carbGroup, $ar, $en, $price, $ord);
        }

        // ── Group 4: نوع الاشتراك ─────────────────────────────────────────────
        $typeGroup = $this->upsertGroup($subscription, [
            'name_ar'        => 'نوع الاشتراك',
            'name_en'        => 'Subscription Type',
            'selection_type' => GymSubscriptionOptionGroup::SELECTION_SINGLE,
            'is_required'    => true,
            'list_order'     => 4,
            'source_type'    => 'fixed',
        ]);

        $this->upsertFixedOption($typeGroup, 'توصيل', 'Delivery', 208, 1);
        $this->upsertFixedOption($typeGroup, 'استلام', 'Pickup',     0, 2);

        // ── Group 5: وجبات اضافية (category add-ons, per period) ─────────────
        $addOnGroup = $this->upsertGroup($subscription, [
            'name_ar'        => 'وجبات اضافية',
            'name_en'        => 'Additional Meals',
            'selection_type' => GymSubscriptionOptionGroup::SELECTION_MULTIPLE,
            'is_required'    => false,
            'list_order'     => 5,
            'source_type'    => 'fixed',
        ]);

        foreach ([
            [1, 'السناكات الفخمه', 'Premium Snacks',    364],
            [2, 'مشروبات غازية',   'Carbonated Drinks',  65],
            [3, 'سلطة',            'Salad',             130],
            [4, 'سلطة الفواكه',    'Fruit Salad',       156],
            [5, 'عصيرات',          'Juices',            260],
            [6, 'سلطة البروتين',   'Protein Salad',     312],
            [7, 'سناك',            'Snack',             104],
        ] as [$ord, $ar, $en, $price]) {
            $this->upsertFixedOption($addOnGroup, $ar, $en, $price, $ord);
        }

        // ── Groups 6-12: Product selection (Step 2 meal-picker tabs) ─────────
        // Each group is linked to a store category; products in that category
        // become the individual options the customer selects their daily meals from.

        $productGroupDefs = [
            [6,  'وجبة رئيسية',    'Main Meal',         'main_meal',     true],
            [7,  'السناكات الفخمه', 'Premium Snacks',    'snacks',        false],
            [8,  'مشروبات غازية',   'Carbonated Drinks', 'drinks',        false],
            [9,  'عصيرات',          'Juices',            'juices',        false],
            [10, 'سلطة',            'Salad',             'salad',         false],
            [11, 'سلطة البروتين',   'Protein Salad',     'protein_salad', false],
            [12, 'سلطة الفواكه',    'Fruit Salad',       'fruit_salad',   false],
        ];

        foreach ($productGroupDefs as [$ord, $ar, $en, $catKey, $required]) {
            $storeCategory = $storeCats[$catKey];

            $group = $this->upsertGroup($subscription, [
                'name_ar'        => $ar . ' (اختيار الوجبات)',
                'name_en'        => $en . ' (Meal Selection)',
                'category_id'    => $storeCategory->id,
                'source_type'    => 'product',
                'selection_type' => GymSubscriptionOptionGroup::SELECTION_MULTIPLE,
                'is_required'    => $required,
                'list_order'     => $ord,
            ]);

            foreach ($products[$catKey] as $pIdx => $product) {
                GymSubscriptionOption::firstOrCreate(
                    [
                        'option_group_id' => $group->id,
                        'product_id'      => $product->id,
                    ],
                    [
                        'branch_setting_id' => $this->branchId,
                        'option_group_id'   => $group->id,
                        'product_id'        => $product->id,
                        'price_modifier'    => 0,
                        'list_order'        => $pIdx + 1,
                    ]
                );
            }
        }
    }

    // ── Link main-meal products to subscription (subscription_products) ───────

    private function seedSubscriptionProducts(GymSubscription $subscription, array $mainMeals): void
    {
        foreach ($mainMeals as $i => $product) {
            $exists = GymSubscriptionProduct::where('subscription_id', $subscription->id)
                ->where('product_id', $product->id)
                ->exists();

            if (!$exists) {
                GymSubscriptionProduct::create([
                    'branch_setting_id' => $this->branchId,
                    'subscription_id'   => $subscription->id,
                    'product_id'        => $product->id,
                    'list_order'        => $i + 1,
                    'is_replaceable'    => true,
                ]);
            }
        }
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    /** firstOrCreate an option group (identified by subscription_id + name_ar) */
    private function upsertGroup(GymSubscription $subscription, array $attrs): GymSubscriptionOptionGroup
    {
        return GymSubscriptionOptionGroup::firstOrCreate(
            [
                'subscription_id' => $subscription->id,
                'name_ar'         => $attrs['name_ar'],
            ],
            array_merge($attrs, [
                'branch_setting_id' => $this->branchId,
                'subscription_id'   => $subscription->id,
                'is_system'         => true,
                'is_web'            => true,
                'is_mobile'         => true,
            ])
        );
    }

    /**
     * Download a remote image and save it to uploads/products/.
     * Returns the filename (e.g. "diet_abc123.jpg") on success, null on failure.
     * Re-uses an already-downloaded file on subsequent seeder runs.
     */
    private function downloadImage(string $url, string $key, string $subdir = 'products'): ?string
    {
        $filename = 'diet_' . substr(md5($key), 0, 16) . '.jpg';
        $destDir  = base_path('uploads/' . $subdir);
        $destPath = $destDir . DIRECTORY_SEPARATOR . $filename;

        if (file_exists($destPath)) {
            return $filename;
        }

        if (!is_dir($destDir)) {
            mkdir($destDir, 0755, true);
        }

        try {
            $response = Http::timeout(20)->withOptions(['verify' => false])->get($url);
            if ($response->successful() && strlen($response->body()) > 1024) {
                file_put_contents($destPath, $response->body());
                $this->command->line("  <info>↓</info> Downloaded: {$filename} → uploads/{$subdir}/");
                return $filename;
            }
        } catch (\Throwable $e) {
            $this->command->warn("  Could not download image for '{$key}': " . $e->getMessage());
        }

        return null;
    }

    /** firstOrCreate a fixed (text) option inside an option group */
    private function upsertFixedOption(
        GymSubscriptionOptionGroup $group,
        string $nameAr,
        string $nameEn,
        float $priceModifier,
        int $listOrder
    ): GymSubscriptionOption {
        return GymSubscriptionOption::firstOrCreate(
            [
                'option_group_id' => $group->id,
                'name_ar'         => $nameAr,
            ],
            [
                'branch_setting_id' => $this->branchId,
                'option_group_id'   => $group->id,
                'name_ar'           => $nameAr,
                'name_en'           => $nameEn,
                'price_modifier'    => $priceModifier,
                'list_order'        => $listOrder,
            ]
        );
    }
}
