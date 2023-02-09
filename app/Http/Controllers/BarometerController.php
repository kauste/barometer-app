<?php

namespace App\Http\Controllers;

use App\Models\Barometer;


class BarometerController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $barometerData = Barometer::all();
        return view('barometer', ['barometerData' => $barometerData]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \App\Http\Requests\StoreBarometerRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreBarometerRequest $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Barometer  $barometer
     * @return \Illuminate\Http\Response
     */
    public function show(Barometer $barometer)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Barometer  $barometer
     * @return \Illuminate\Http\Response
     */
    public function edit(Barometer $barometer)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \App\Http\Requests\UpdateBarometerRequest  $request
     * @param  \App\Models\Barometer  $barometer
     * @return \Illuminate\Http\Response
     */
    public function update(UpdateBarometerRequest $request, Barometer $barometer)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Barometer  $barometer
     * @return \Illuminate\Http\Response
     */
    public function destroy(Barometer $barometer)
    {
        //
    }
}
