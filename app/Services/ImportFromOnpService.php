<?php

namespace Mss\Services;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Mss\Models\Article;
use Mss\Models\ArticleNote;
use Mss\Models\ArticleQuantityChangelog;
use Mss\Models\Category;
use Mss\Models\Legacy\Category as LegacyCategory;
use Mss\Models\Legacy\Supplier as LegacySupplier;
use Mss\Models\Legacy\Material as LegacyArticle;
use Mss\Models\Legacy\MaterialLog as LegacyArticleLog;
use Mss\Models\Supplier;
use Mss\Models\ArticleSupplier;
use Mss\Models\Tag;
use Mss\Models\Unit;
use Mss\Models\User;

class ImportFromOnpService {

    protected $command;

    public function __construct(Command $command) {
        $this->command = $command;
        Category::unguard();
        Supplier::unguard();
        Article::unguard();
        ArticleSupplier::unguard();
        Tag::unguard();
        ArticleQuantityChangelog::unguard();
        ArticleNote::unguard();
    }

    public function importCategories($bar) {
        LegacyCategory::all()->each(function ($category) use ($bar) {
            /* @var $category LegacyCategory */
            Category::create([
                'id' => $category->id,
                'name' => utf8_encode($category->name),
                'notes' => utf8_encode($category->bemerkung)
            ]);
            $bar->advance();
        });
    }

    public function importSuppliers($bar) {
        LegacySupplier::all()->each(function ($supplier) use ($bar) {
            /* @var $supplier LegacySupplier */
            Supplier::create([
                'id' => $supplier->id,
                'name' => utf8_encode($supplier->company_name),
                'email' => utf8_encode($supplier->email),
                'phone' => utf8_encode($supplier->phone),
                'contact_person' => utf8_encode($supplier->contact_person),
                'website' => utf8_encode($supplier->website),
                'notes' => utf8_encode($supplier->comment)
            ]);
            $bar->advance();
        });
    }

    public function importLog($bar) {
        $articleCache = [];
        $userCache = [];

        LegacyArticleLog::chunk(1000, function ($items) use ($bar, $articleCache, $userCache) {
            foreach ($items as $log) {
                /* @var $log LegacyArticleLog */

                if (array_key_exists($log->material_id, $articleCache)) {
                    $article = $articleCache[$log->material_id];
                } else {
                    $article = $articleCache[$log->material_id] = Article::find($log->material_id);
                }

                if (!$article) {
                    continue;
                }

                if (array_key_exists($log->user_name, $userCache)) {
                    $user = $userCache[$log->user_name];
                } else {
                    $user = $userCache[$log->user_name] = User::firstOrCreate([
                        'name' => $log->user_name
                    ], [
                        'email' => $log->user_name,
                        'password' => bcrypt('password')
                    ]);
                }

                $article->quantityChangelogs()->create([
                    'type' => $log->type,
                    'user_id' => $user->id,
                    'created_at' => Carbon::parse($log->time_stamp),
                    'updated_at' => Carbon::parse($log->time_stamp),
                    'change' => ($log->type == ArticleQuantityChangelog::TYPE_OUTGOING ? (-1 * $log->count) : $log->count),
                    'new_quantity' => $log->ist_count,
                    'note' => utf8_encode($log->comment)
                ]);
                $bar->advance();
            }
        });
    }

    public function importArticles($bar) {
        LegacyArticle::all()->each(function ($article) use ($bar) {
            /* @var $article LegacyArticle */
            /* @var $newArticle Article */
            $unit = !is_null($article->einheit) ? Unit::find($article->einheit + 1) : null;
            $newArticle = Article::create([
                'id' => $article->id,
                'name' => utf8_encode($article->artikelbezeichnung),
                'quantity' => $article->bestand,
                'min_quantity' => $article->mindbestand,
                'usage_quantity' => $article->verbrauch,
                'notes' => utf8_encode($article->bemerkung),
                'status' => $article->status,
                'issue_quantity' => $article->entnahmemenge,
                'sort_id' => $article->sort_id,
                'inventory' => $article->inventur,
                'unit_id' => $unit ? optional($unit)->id : null,
                'category_id' => $article->type
            ]);

            $newArticle->addTag(utf8_encode($article->maschinenzugehoerigkeit));

            $newArticle->suppliers()->attach($article->hersteller, [
                'order_number' => utf8_encode($article->bestnr),
                'price' => $article->preis,
                'order_quantity' => $article->bestellmenge,
                'delivery_time' => utf8_encode($article->lieferzeit),
            ]);

            $bar->advance();
        });

    }
}