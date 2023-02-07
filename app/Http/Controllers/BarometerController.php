<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BarometerController extends Controller
{


    public static function index(Request $request){

        $previousPressure = $request->session()->get('previousPressure', []);
        $isRising = $request->session()->get('isRising', []);
        $cities = $request->cities ?? ['nida', 'vilnius'];

        foreach($cities as $city){

            $$city = Http::get('https://api.openweathermap.org/data/2.5/weather',[
                'q' => $city.',LT',
                'appid'=> '4073f66b93000ba7712dff2f2f0628a5'
            ]);

            if($$city->failed()){
                //ka daryt? return kazkoks...
            }
            $$city->json();

            $$city = ['main' => $$city['weather'][0]['main'], 'pressure' => $$city['main']['pressure']];
            
            if(count($previousPressure) == count($cities)){
                if($previousPressure[$city] <  $$city['pressure']){
                    $isRising[$city] = true;
                }
                elseif($previousPressure[$city] >  $$city['pressure']){
                    $isRising[$city] = false;
                }
            }
            $previousPressure[$city] =  $$city['pressure'];
        }
        $request->session()->put('previousPressure', $previousPressure);
        $request->session()->put('isRising', $isRising);
        
        return view('barometer', ['nida' => $nida,
                                  'vilnius' => $vilnius,
                                  'isPressureRising' => $isRising]);
    }

    public static function pressureState(Request $request){
        
    }
}
