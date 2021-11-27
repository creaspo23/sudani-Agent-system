<?php

namespace App\Http\Controllers;

use App\Credit;

use Illuminate\Http\Request;

class CreditController extends Controller
{
    public function __construct() {
        $this->middleware('permission:credits-create')->only(['create', 'store']);
        $this->middleware('permission:credits-read')->only(['index', 'show']);
        $this->middleware('permission:credits-update')->only(['edit', 'update']);
        $this->middleware('permission:credits-delete')->only('destroy');
    }
    
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $credits = Credit::all();
        return view('dashboard.credits.index', compact('credits'));
    }

    /**
     * Unit a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // return $request->all();
        $request->validate([
            'month' => 'required | string',
            'date' => 'required | string',
            'bonus' => 'required | numeric',
        ]);
        $credit = Credit::create($request->all());

        session()->flash('success', 'تمت العملية بنجاح');

        return back();
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Credit  $credit
     * @return \Illuminate\Http\Response
     */
    public function show(Credit $credit)
    {
        return view('dashboard.credits.show', compact('credit'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Credit  $credit
     * @return \Illuminate\Http\Response
     */
    public function edit(Credit $credit)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Credit  $credit
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Credit $credit)
    {
        $request->validate([
            'month' => 'required | string',
            'date' => 'required | string',
            'bonus' => 'required | numeric',
        ]);
        $credit->update($request->except(['_token']));

        session()->flash('success', 'تمت العملية بنجاح');

        return back();
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Credit  $credit
     * @return \Illuminate\Http\Response
     */
    public function destroy(Credit $credit)
    {
        $credit->delete();

        session()->flash('success', 'تمت العملية بنجاح');

        return redirect()->route('credits.index');
    }
}
