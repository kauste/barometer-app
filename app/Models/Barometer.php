<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use App\Events\WeatherUpdated;
use App\Events\WeatherUpdateFail;

class Barometer extends Model
{
    use HasFactory;

    const CITIES = ['nida', 'vilnius'];

    protected $fillable = ['city', 'weather_condition', 'pressure', 'previous_pressure', 'is_rising'];

    public static function createOrUpdate(){
        foreach(self::CITIES as $key => $city){
            // current weather condition
            // TEST visit https://api.openweathermap.org/data/2.5/weather?q=nida,LT&appid=4073f66b93000ba7712dff2f2f0628a5 press ctrl+f find pressure and weather[0].main
            // TEST $city = 'k.sjdj';
            $currentCondition = Http::get('https://api.openweathermap.org/data/2.5/weather',[
                'q' => $city.',LT',
                'appid'=> '4073f66b93000ba7712dff2f2f0628a5'
            ]);
            
            if($currentCondition->failed()){
                $lastUpdate = self::orderByDesc('updated_at')
                                   ?->select('updated_at')
                                   ?->first()
                                   ?->updated_at;
                
                return WeatherUpdateFail::dispatch($lastUpdate);
            }

            //TEST  $currentWeather = 'rain'; command line: php artisan schedule:run
            $currentWeather = match($currentCondition['weather'][0]['main']){
                'Thunderstorm', 'Dust', 'Sand', 'Tornado', 'Squall' => 'storm',
                'Drizzle', 'Rain' => 'rain',
                'Mist', 'Smoke', 'Haze', 'Fog' => 'change',
                'Clouds', 'Clear' => 'fair',
                'Ash' => 'dry',
                'Snow' => 'snow'
            };
            // TEST $currentPressure = 1070
            $currentPressure = $currentCondition['main']['pressure'];

            //previous weather condition
            
            $previousCondition = self::where('city', $city)
                        ?->select('is_rising', 'pressure', 'weather_condition')
                        ?->first();
            // TEST $previousCurrentPressure = 960; command line: php artisan schedule:run , php artisan migrate:fresh; php artisan schedule:work
            $previousCurrentPressure = $previousCondition?->pressure; 
            $previousIsRisig = $previousCondition?->is_rising;
            $previousWeather = $previousCondition?->weather_condition;
            $previousPreviousPressure = $previousCondition?->previous_pressure;
            
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
            // data for creating or updating
            $dataForUpdate = ['weather_condition' => $currentWeather, 
                              'pressure' => $currentPressure,
                              'previous_pressure' => $previousCurrentPressure,
                              'is_rising' => $isRising];

            // validation
            $validator = Validator::make($dataForUpdate, 
                                        [
                                            'weather_condition' => 'required|string|min:3|max:20',
                                            'pressure' => 'required|integer|min:600|max:1080',
                                            'previous_pressure' => 'nullable|integer|min:600|max:1080',
                                            'is_rising' => 'boolean|nullable',
                                        ]);
            if ($validator->fails()) {
                    $lastUpdate = self::orderByDesc('updated_at')
                    ?->select('updated_at')
                    ?->first()
                    ?->updated_at;

                    return WeatherUpdateFail::dispatch($lastUpdate);
            }
            // to database
            self::updateOrCreate(
                ['city' => $city],
                $dataForUpdate
            );
            $thisCity = self::where('city', $city)->first();
            WeatherUpdated::dispatch($thisCity); // event(new WeatherUpdated($thisCity));
        }
    }
}
