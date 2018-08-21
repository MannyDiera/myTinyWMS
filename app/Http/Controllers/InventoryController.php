<?php

namespace Mss\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Mss\DataTables\InventoryDataTable;
use Mss\Http\Requests\UnitRequest;
use Illuminate\Http\Request;
use Mss\Models\Article;
use Mss\Models\ArticleQuantityChangelog;
use Mss\Models\Category;
use Mss\Models\Inventory;
use Mss\Services\InventoryService;

class InventoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(InventoryDataTable $inventoryDataTable) {
        return $inventoryDataTable->render('inventory.list');
    }

    /**
     * Display the specified resource.
     *
     * @param Inventory $inventory
     * @return \Illuminate\Http\Response
     */
    public function show(Inventory $inventory, Request $request) {
        $categories = InventoryService::getOpenCategories($inventory);
        $items = $categories->mapWithKeys(function ($category) use ($inventory) {
            return [$category->name => InventoryService::getOpenArticles($inventory, $category)];
        });

        $categoryToPreselect = ($request->has('category_id')) ? Category::find($request->get('category_id')) : null;

        return view('inventory.show', compact('inventory', 'items', 'categoryToPreselect'));
    }

    public function processed(Inventory $inventory, Article $article, Request $request) {
        /* @var $article Article */

        $diff = ($request->get('quantity') - $article->quantity);
        if ($diff !== 0) {
            $article->changeQuantity($diff, ArticleQuantityChangelog::TYPE_INVENTORY, 'Inventurupdate '.date("d.m.Y"));
        }

        $item = $inventory->items->where('article_id', $article->id)->first();

        if ($item) {
            $item->processed_at = now();
            $item->processed_by = Auth::id();
            $item->save();

            flash('Änderung gespeichert')->success();

            return response()->redirectToRoute('inventory.show', [$inventory, 'category_id' => $article->category_id]);
        }

        flash('Fehler beim Speichern')->error();

        return response()->redirectToRoute('inventory.show', [$inventory, 'category_id' => $article->category_id]);
    }

    public function correct(Inventory $inventory, Article $article) {
        /* @var $article Article */
        $item = $inventory->items->where('article_id', $article->id)->first();
        if ($item) {
            $item->processed_at = now();
            $item->processed_by = Auth::id();
            $item->save();

            flash('Änderung gespeichert')->success();

            return response()->redirectToRoute('inventory.show', [$inventory, 'category_id' => $article->category_id]);
        }

        flash('Fehler beim Speichern')->error();

        return response()->redirectToRoute('inventory.show', [$inventory, 'category_id' => $article->category_id]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $inventory = InventoryService::createNewInventory();

        return response()->redirectToRoute('inventory.show', [$inventory]);
    }

    public function categoryDone(Inventory $inventory, Category $category) {
        $inventory->load(['items' => function ($query) {
            $query->unprocessed()->with('article.category');
        }]);
        InventoryService::markCategoryAsDone($inventory, $category);

        flash('Kategorie abgeschlossen')->success();

        return response()->redirectToRoute('inventory.show', [$inventory]);
    }

    public function finish(Inventory $inventory) {
        $inventory->load(['items' => function ($query) {
            $query->unprocessed()->with('article.category');
        }]);

        InventoryService::getOpenCategories($inventory)->each(function ($category) use ($inventory) {
            InventoryService::markCategoryAsDone($inventory, $category);
        });

        flash('Inventur abgeschlossen')->success();

        return response()->redirectToRoute('inventory.index');
    }
}