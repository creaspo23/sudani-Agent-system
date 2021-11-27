<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\{Supplier, Expense, Payment, Safe};
use App\Bill;
use App\{Store, Customer, Invoice, InvoiceItem};
use App\ItemStoreUnit;
use App\BillItem;
use Illuminate\Support\Carbon;

class BillController extends Controller
{
    public function __construct()
    {
        $this->middleware('permission:bills-create')->only(['create', 'store']);
        $this->middleware('permission:bills-read')->only(['index', 'show']);
        $this->middleware('permission:bills-update')->only(['edit', 'update']);
        $this->middleware('permission:bills-delete')->only('destroy');
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        // dd($request->all());
        $stores = auth()->user()->getStores();
        
        $suppliers = auth()->user()->suppliers();
        $stores_ids = $stores->pluck('id')->toArray();
        $suppliers_ids = $suppliers->pluck('id')->toArray();
         $store_id = $request->store_id ? $request->store_id : 'all';
        $supplier_id = $request->supplier_id ? $request->supplier_id : 'all';
        // dd([$stores_ids, $suppliers_ids, $store_id, $supplier_id]);

        $from_date = Carbon::parse($request->from_date ?? now()->startOfDay());
        $to_date = Carbon::parse($request->to_date ?? now())->endOfDay();

        $bills = Bill::whereNull('bill_id')
            ->whereBetween('created_at', [$from_date, $to_date])
            ->when($store_id != 'all', function ($q) use ($request) {
                return $q->where('store_id', $request->store_id);
            })
            ->when($supplier_id != 'all', function ($q) use ($request) {
                return $q->where('supplier_id', $request->supplier_id);
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        $is_payed = isset($request->is_payed);
        if (isset($request->is_payed)) {
            $bills = $bills->filter(function ($bill) use ($is_payed) {
                return (bool) $is_payed ? !$bill->isPayed() : $bill->isPayed();
            });
        }

        $is_delivered = isset($request->is_delivered) ? $request->is_delivered : 'both';
        if (isset($request->is_delivered) && $request->is_delivered != 'both') {
            $bills = $bills->filter(function ($bill) use ($is_delivered) {
                return (bool) $is_delivered ? $bill->isDelivered() : !$bill->isDelivered();
            });
        }


        $advanced_search = isset($request->from_date) || isset($request->store_id) || isset($request->supplier_id) || isset($request->is_delivered) || isset($request->is_payed);
        return view('dashboard.bills.index', compact('advanced_search', 'stores', 'store_id', 'suppliers', 'supplier_id', 'bills', 'from_date', 'to_date'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $suppliers = auth()->user()->suppliers();
        $stores = auth()->user()->getStores();
        $bill = $request->bill_id ? Bill::find($request->bill_id) : null;
        $store = $request->store_id ? Store::find($request->store_id) : null;
        $store = count($stores) && $store == null ? $stores->first() : $store;
        $billsSafes = Safe::billsSafes()->filter(function ($safe) {
            return $safe->balance() > 0;
        });
        $expensesSafes = Safe::expensesSafes()->filter(function ($safe) {
            return $safe->balance() > 0;
        });
        return view('dashboard.bills.create', compact('billsSafes', 'expensesSafes', 'stores', 'store', 'suppliers', 'bill'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if (!$request->type) {
            $request->validate([
                'items'     => 'required',
                'units'     => 'required',
                'store_id'  => 'required',
                'supplier_id'  => 'required',
            ]);
        }
        $request->validate([
            'store_id'  => 'required',
            'supplier_id'  => 'required',
        ]);
        $bill = $this->setBill($request);
     
        return redirect()->route('bills.show', compact('bill'))->with('success', 'تم انشاء الفاتورة بنجاح');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Bill $bill)
    {

        $title = $bill->bill ? 'فاتورة استلام' : 'فاتورة شراء';
        $customers = auth()->user()->customers();
        $billsSafes = Safe::billsSafes();
        $expensesSafes = Safe::expensesSafes();

        return view('dashboard.bills.show', compact('bill', 'title', 'customers', 'expensesSafes', 'billsSafes'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(Request $request, Bill $bill)
    {
        // dd($bill);
        $suppliers = auth()->user()->suppliers();
        $stores = auth()->user()->getStores();
        $store = $request->store_id ? Store::find($request->store_id) : null;
        $store = count($stores) && $store == null ? $bill->store : $store;
        $cheque = $bill->cheques->count() ? $bill->cheques->first() : null;
        $billsSafes = Safe::billsSafes();
        $expensesSafes = Safe::expensesSafes();

        return view('dashboard.bills.edit', compact('bill', 'stores', 'store', 'billsSafes', 'expensesSafes', 'suppliers', 'cheque'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bill $bill)
    {
          
        $this->setBill($request, $bill);

        return back()->with('success', 'تم تعديل الفاتورة بنجاح');
    }

    private function setBill($request, $bill = null)
    {
        $receivedCount = $request->receives ? count(array_filter($request->receives)) : 0;
        $payed = isset($request->payments_amounts) ? array_sum($request->payments_amounts) : 0;



        $chargeAmount = isset($request->expenses_amounts) ? array_sum($request->expenses_amounts) : 0;
        $chargePerItem = $receivedCount ? $chargeAmount / $receivedCount : 0;


        $amounts = $request->amount - $chargeAmount;


        if ($bill) {
            $bill->reset();
            $bill->update($request->except(['_token', '_method']));
        } else {
            $bill = Bill::create($request->except(['_token', '_method']));
        }
        $total = 0;
        $totalQ = 0;
        $totalP = 0;
        $remain = 0;

        //todo chek creation
        $receiveBill = Bill::create([
            'bill_id' => $bill->id
        ]);

        for ($i = 0; $i < count($request->items); $i++) {
            $item = $request->items[$i];
            $unit = $request->units[$i];
            $quantity = $request->quantities[$i];
            $price = $request->prices[$i];
            $receive = $request->receives ? $request->receives[$i] : 0;
            if ($quantity > 0 && $price > 0) {
                $itemStoreUnit = ItemStoreUnit::where('item_store_id', $item)->where('item_unit_id', $unit)->first();
                $itemStoreUnit = $itemStoreUnit ? $itemStoreUnit : ItemStoreUnit::create(['item_store_id' => $item, 'item_unit_id' => $unit]);
                $totalQ += $quantity;
                $totalP += $price;
                $total += $quantity * $price;


                $billItem = BillItem::create([
                    'item_store_unit_id' => $itemStoreUnit->id,
                    'item_id' => $itemStoreUnit->itemStore->item_id,
                    'unit_id' => $itemStoreUnit->itemUnit->unit_id,
                    'bill_id' => $bill->id,
                    'quantity' => $quantity,
                    'price' => $price,
                ]);

                if ($receive) {
                    $chargePerUnit = $receive ? ($chargePerItem / $receive) : 0;

                    $receiveBillItem = BillItem::create([
                        'item_store_unit_id' => $itemStoreUnit->id,
                        'item_id' => $itemStoreUnit->itemStore->item_id,
                        'unit_id' => $itemStoreUnit->itemUnit->unit_id,
                        'bill_id' => $receiveBill->id,
                        'bill_item_id' => $billItem->id,
                        'quantity' => $receive,
                        'price' => $price,
                        'expense' => $chargePerUnit,
                        'expenses' => $chargePerItem,
                    ]);
                    $itemStoreUnit->update(['quantity' => $itemStoreUnit->quantity + $receive, 'price_purchase' => $billItem->totalPrice()]);
                    $total += $chargePerItem;
                }
            }
        }
        $bill->withEntry($amounts);
        if (count($receiveBill->items) < 1) {
            $receiveBill->delete();
        } else {
            if ($request->expenses_safes) {
                for ($i = 0; $i < count($request->expenses_safes); $i++) {
                    $safe = $request->expenses_safes[$i];
                    $amount = $request->expenses_amounts[$i];
                    if ($amount) {
                        $expense = Expense::create([
                            'amount' => $amount,
                            'details' => 'مصاريف لفاتورة استلام رقم: ' . $receiveBill->id,
                            'safe_id' => $safe,
                            'bill_id' => $receiveBill->id,
                        ]);
                    }
                }
            }
        }

        if ($request->payments_safes) {
            for ($i = 0; $i < count($request->payments_safes); $i++) {
                $safe = $request->payments_safes[$i];

                $amount = $request->payments_amounts[$i];
                if ($amount) {
                    $payment = Payment::create([

                        'amount' => $amount,
                        'details' => 'دفعة لفاتورة شراء رقم: ' . $bill->id,
                        'from_id' => $bill->supplier->account->id,
                        'to_id' => $safe,
                        'safe_id' => $safe,
                        'bill_id' => $bill->id,
                    ]);
                }
            }
        }
        // dd([$amount,$payed]);

        $remain = $amount - $payed;

        if ($request->chequeAmount) {
            
            $bill->cheque($request->chequeAmount, $request->chequeBank, $request->chequeNumber, $request->chequeDueDate, $request->chequeAccount);
        }


        if ($bill->amount !== $total || $bill->payed !== $payed || $bill->remain !== $remain) {
            $bill->update([
                'amount' => $amount,
                'payed' => $payed,
                'remain' => $remain,
            ]);
        }

        return $bill;
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, Bill $bill)
    {
        $primaryBill = $bill->bill;
        $bill->delete();

        if ($primaryBill)
            return redirect()->route('bills.show', $primaryBill)->with('success', 'تم إلغاء فاتورة الاستلام بنجاح');
        if (route('bills.show', $bill->id) == url()->previous())
            return redirect()->route('bills.index')->with('success', 'تم إلغاء الفاتورة بنجاح');
        else
            return back()->with('success', 'تم إلغاء الفاتورة بنجاح');
    }
}
