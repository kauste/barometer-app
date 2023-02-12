<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use App\Events\WeatherUpdated;
use App\Events\WeatherUpdateFail;
use Carbon\Carbon;
use Validator;

class Barometer extends Model
{
    use HasFactory;

    const CITIES = ['nida', 'vilnius'];

    protected $fillable = ['city', 'weather_condition', 'pressure', 'previous_pressure', 'is_rising'];

    public static function createOrUpdate()
    {
        foreach(self::CITIES as $key => $city){
            // current weather condition from API
            // TEST visit https://api.openweathermap.org/data/2.5/weather?q=nida,LT&appid=4073f66b93000ba7712dff2f2f0628a5 press ctrl+f find pressure and weather[0].main
            // TEST $city = 'notExistingCity';
            $currentCondition = Http::get('https://api.openweathermap.org/data/2.5/weather',[
                'q' => $city.',LT',
                'appid'=> '4073f66b93000ba7712dff2f2f0628a5'
            ]);
            $lastUpdate = self::orderByDesc('updated_at')
                ?->select('updated_at')
                ?->first()
                ?->updated_at;
            if($currentCondition->failed()){
            // dump('here i am') ;
                //TEST if($lastUpdate !== null && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) >= 0){
                //TEST if(Carbon::parse($lastUpdate)->diffInHours(Carbon::now()) >= 0){
                if($lastUpdate !== null && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) >= 15){   
                    if(Carbon::parse($lastUpdate)->diffInHours(Carbon::now()) >= 3){
                        self::where('id', '>', 0)->delete();
                        $lastUpdate = null;
                    }
                    else{
                        $lastUpdate = Carbon::parse($lastUpdate)->locale('lt')->tz('Europe/Vilnius')->format('Y-m-d H:i');
                    }
                    return WeatherUpdateFail::dispatch($lastUpdate);
                }
                else return;
            }

            $currentWeather = match($currentCondition['weather'][0]['main']){
                'Thunderstorm', 'Dust', 'Sand', 'Tornado', 'Squall' => 'storm',
                'Drizzle', 'Rain' => 'rain',
                'Mist', 'Smoke', 'Haze', 'Fog' => 'change',
                'Clouds', 'Clear' => 'fair',
                'Ash' => 'dry',
                'Snow' => 'snow'
            };
            // TEST $currentWeather = 'rain';
            $currentPressure = $currentCondition['main']['pressure'];
            // TEST $currentPressure = 1070;

            //previous weather condition
            $previousCondition = self::where('city', $city)
                        ?->select('is_rising', 'pressure', 'weather_condition')
                        ?->first();
            // TEST $previousCurrentPressure = 960; 
            // TEST $previousCurrentPressure = 1015; 
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
        //    TEST $currentWeather = 5;
            $dataForUpdate = ['weather_condition' => $currentWeather, 
                              'pressure' => (int) $currentPressure,
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
                // TEST if($lastUpdate !== null && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) >= 0){
                // TEST if(Carbon::parse($lastUpdate)->diffInHours(Carbon::now()) >= 0){
                if($lastUpdate !== null && Carbon::parse($lastUpdate)->diffInMinutes(Carbon::now()) >= 15){   
                    if(Carbon::parse($lastUpdate)->diffInHours(Carbon::now()) >= 3){
                        self::where('id', '>', 0)->delete();
                        $lastUpdate = null;
                    }
                    else{
                        $lastUpdate = Carbon::parse($lastUpdate)->locale('lt')->tz('Europe/Vilnius')->format('Y-m-d H:i');
                    }
                    return WeatherUpdateFail::dispatch($lastUpdate);
                }
                else return;
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
