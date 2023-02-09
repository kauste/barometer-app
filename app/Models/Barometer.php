<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use App\Events\WeatherUpdated;

class Barometer extends Model
{
    use HasFactory;

    const CITIES = ['nida', 'vilnius'];

    protected $fillable = ['city', 'weather_condition', 'pressure', 'previous_pressure', 'is_rising'];

    public static function createOrUpdate(){
        foreach(self::CITIES as $key => $city){
            // current weather condition
            $currentCondition = Http::get('https://api.openweathermap.org/data/2.5/weather',[
                'q' => $city.',LT',
                'appid'=> '4073f66b93000ba7712dff2f2f0628a5'
            ]);
            if($currentCondition->failed()){
                //return kazkoks...
            }
            $currentWeather = match($currentCondition['weather'][0]['main']){
                'Thunderstorm', 'Dust', 'Sand', 'Tornado', 'Squall' => 'storm',
                'Drizzle', 'Rain' => 'rain',
                'Mist', 'Smoke', 'Haze', 'Fog' => 'change',
                'Clouds', 'Clear' => 'fair',
                'Ash' => 'dry',
                'Snow' => 'snow'
            };
            $currentPressure = $currentCondition['main']['pressure'];

            //previous weather condition
            $previousCondition = self::where('city', $city)
                        ?->select('is_rising', 'pressure', 'weather_condition')
                        ?->first();
            $previousCurrentPressure = $previousCondition?->pressure;
            $previousIsRisig = $previousCondition?->is_rising;
            $previousWeather = $previousCondition?->weather_condition;
            
           // detection if is rising
            if($previousCurrentPressure > $currentPressure){
                $isRising = false;
            }
            elseif($previousCurrentPressure !== null && $previousCurrentPressure < $currentPressure ){
                $isRising = true;
            }
            else{
                $isRising = $previousIsRisig;
            }
            // create or update database
            self::updateOrCreate(
                ['city' => $city],
                ['weather_condition' => $currentWeather, 
                 'pressure' => $currentPressure,
                 'previous_pressure' => $previousCurrentPressure,
                 'is_rising' => $isRising]
            );
            $thisCity = self::where('city', $city)->first();

            // event(new WeatherUpdated($thisCity));
            WeatherUpdated::dispatch($thisCity);


            
            // if($previousCurrentPressure != $currentPressure 
            //     || $previousWeather !== $currentWeather){
                    // $condition = ['weather_condition' => $currentWeather, 
                    //               'pressure' => $currentPressure,
                    //               'is_rising' => $isRising];
                    
                // if(count(self::CITIES - 1) == $key){
                
                // }                    
            // }
            
        }
    }

}
