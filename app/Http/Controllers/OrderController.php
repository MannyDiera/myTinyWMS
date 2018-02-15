<?php

namespace Mss\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Mss\DataTables\OrderDataTable;
use Mss\Http\Requests\OrderRequest;
use Mss\Models\Article;
use Mss\Models\ArticleQuantityChangelog;
use Mss\Models\Order;
use Mss\Models\OrderItem;
use Mss\Models\Supplier;

class OrderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(OrderDataTable $orderDataTable) {
        return $orderDataTable->render('order.list');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create() {
        $articles = Article::with('suppliers')->orderBy('name')->get()
            ->transform(function ($article) {
                /*@var $article Article */
                return [
                    'id' => $article->id,
                    'name' => $article->name/*.(!empty($article->unit) ? ' ('.$article->unit->name.')' : '')*/,
                    'supplier_id' => $article->currentSupplier()->id
                ];
            });

        $order = new Order();
        $order->internal_order_number = $order->getNextInternalOrderNumber();
        $order->save();

        return view('order.create', compact('order', 'articles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(OrderRequest $request) {
        /* @var $order Order */
        $order = Order::findOrFail($request->get('order_id'));

        $order->status = $request->get('status');
        $order->supplier_id = $request->get('supplier');
        $order->external_order_number = $request->get('external_order_number');
        $order->total_cost = parsePrice($request->get('total_cost'));
        $order->shipping_cost = parsePrice($request->get('shipping_cost'));
        $order->order_date = Carbon::parse($request->get('order_date'));
        $order->expected_delivery = Carbon::parse($request->get('expected_delivery'));
        $order->notes = $request->get('notes');

        if ($order->status === Order::STATUS_NEW) {
            $order->status = Order::STATUS_ORDERED;
        }

        $order->save();

        $order->items()->delete();
        collect($request->get('article'))->each(function ($article, $key) use ($order, $request) {
            $quantity = intval($request->get('quantity')[$key] ?: 0);
            $price = $request->get('price')[$key] ?: null;

            if (empty($article) || empty($quantity) || empty($price)) {
                return true;
            }

            $order->items()->create([
                'article_id' => $article,
                'price' => parsePrice($price),
                'quantity' => $quantity
            ]);
        });

        flash('Bestellung gespeichert', 'success');
        return response()->redirectToRoute('order.show', $order);
    }

    public function cancel(Order $order) {
        $order->delete();

        return response()->redirectToRoute('order.index');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id) {
        $order = Order::with('items.order.items')->findOrFail($id);
        $audits = $order->getAudits();

        return view('order.show', compact('order', 'audits'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id) {
        $articles = Article::with('suppliers')->withCurrentSupplier()->orderBy('name')->get()
            ->transform(function ($article) {
                /*@var $article Article */
                return [
                    'id' => $article->id,
                    'name' => $article->name/*.(!empty($article->unit) ? ' ('.$article->unit->name.')' : '')*/,
                    'supplier_id' => $article->currentSupplier->id
                ];
            });

        $order = Order::findOrFail($id);

        return view('order.edit', compact('order', 'articles'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id) {
        Order::findOrFail($id)->delete();

        flash('Bestellung gelöscht', 'success');
        return response()->redirectToRoute('order.index');
    }

    public function articleList(Supplier $supplier) {
        return response()->json($supplier->articles->pluck(['name', 'id']));
    }

    public function createDelivery(Order $order) {
        return view('order.delivery_form', compact('order'));
    }

    public function storeDelivery(Order $order, Request $request) {
        $delivery = $order->deliveries()->create([
            'delivery_date' => Carbon::parse($request->get('delivery_date')),
            'delivery_note_number' => $request->get('delivery_note_number'),
            'notes' => $request->get('notes')
        ]);

        $quantities = collect($request->get('quantities'));
        $order->items->each(function ($orderItem) use ($quantities, $delivery, $order) {
            $quantity = intval($quantities->get($orderItem->article->id));
            if ($quantities->has($orderItem->article->id) && $quantity > 0) {

                $delivery->items()->create([
                    'article_id' => $orderItem->article->id,
                    'quantity' => $quantity
                ]);

                $orderItem->article->changeQuantity($quantity, ArticleQuantityChangelog::TYPE_INCOMING, 'Bestellung '.$order->internal_order_number);
            }
        });

        if ($order->isFullyDelivered()) {
            $order->status = Order::STATUS_DELIVERED;
            $order->save();
        } else {
            $order->status = Order::STATUS_PARTIALLY_DELIVERED;
            $order->save();
        }

        return response()->redirectToRoute('order.show', $order);
    }
}