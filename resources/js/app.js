import './bootstrap';
const nidaDOM = document.querySelector('.nida--city');
const vilniusDOM = document.querySelector('.vilnius--city');
const bothCitiesDOM = nidaDOM.closest('.both--js');
const controlsDOM = document.querySelector('.controls--js');
const navNidaDOM = controlsDOM.querySelector('.nav--nida');
const navVilniusDOM = controlsDOM.querySelector('.nav--vilnius');
const navBothDOM = controlsDOM.querySelector('.nav--both');
const zeroDeg = 1015;

const showCity = (navElement, nidaDisplay, vilniusDisplay) => {
    navElement.addEventListener('click', () => {
        [...controlsDOM.children].forEach(control => {
            control.classList.remove('active');
        });
        navElement.classList.add('active');
        nidaDOM.style.display = nidaDisplay;
        vilniusDOM.style.display = vilniusDisplay;
    })
}
const barometerArrow = (city) => {
    // is rising DOM
    const isRisingDOM = bothCitiesDOM.querySelector(`.is--rising--${city}`);
    const isRisingSvgDOM = isRisingDOM.querySelector('svg');
    const isRisingTextDOM = isRisingDOM.querySelector('span');
    const isRising = parseInt(isRisingDOM.dataset.isRising);

    // weather condition DOM
    const cityWeatherConditionDOM = bothCitiesDOM.querySelector(`.weather--${city}`)
    const weatherCondition = cityWeatherConditionDOM.dataset.weatherCondition;

    // barometer DOM
    const barometerDOM = document.querySelector(`.${city}--barometer`);
    const hectopascalPressure = barometerDOM.dataset.pressure;
    const arrowBoxDOM = barometerDOM.querySelector('div');
    
    //is rising
    isRisingSvgDOM.style.transform = isRising ? 'rotate(0deg)' : 'rotate(180deg)';
    isRisingTextDOM.innerText = isRising ? 'Pressure rising' : 'Pressure falling';

    // weather condition
    cityWeatherConditionDOM.querySelector('use').setAttribute('xlink:href', `#${weatherCondition}`);
    cityWeatherConditionDOM.querySelector('span').innerText = weatherCondition !== 'dry' ? weatherCondition : `very ${weatherCondition}`;

    //barometer
    // TEST console.log(hectopascalPressure);
    if(hectopascalPressure !== zeroDeg){
        const rotateDeg = (hectopascalPressure - zeroDeg) * 90 / 33;
        // TEST console.log(rotateDeg);
        arrowBoxDOM.style.transform = `rotate(${rotateDeg}deg)`;
        // TEST arrowBoxDOM.style.transform = `rotate(180deg)`;
    }
    else{
        arrowBoxDOM.style.transform = 'rotate(0deg)';
    }
}

showCity(navNidaDOM, 'flex', 'none')
showCity(navVilniusDOM, 'none', 'flex');
showCity(navBothDOM, 'flex', 'flex');

window.addEventListener('load', () =>{
    bothCitiesDOM.animate([{gap: '100px', opacity:0},
                           {gap: '30px', opacity:1}],
                          {duration: 1000, iterations: 1,})
    
    barometerArrow('vilnius');
    barometerArrow('nida');
    // barometerArrow('makarena'); ---> test
})

Echo.channel('public.weather.update')
    .listen('WeatherUpdated', (e) => {
        const city = e.updatedCity.city;
        const weatherCondition = e.updatedCity.weather_condition;
        // TEST const weatherCondition = 'dry';
        const pressure = e.updatedCity.pressure;
        // TEST  const pressure = 999;
        const isRising = e.updatedCity.is_rising;
         //TEST const isRising = 1;

        //set weather
        const cityWeatherConditionDOM = bothCitiesDOM.querySelector(`.weather--${city}`)
        cityWeatherConditionDOM.dataset.weatherCondition = weatherCondition;

        // set is rising or falling
        const isRisingDOM = bothCitiesDOM.querySelector(`.is--rising--${city}`);
        isRisingDOM.dataset.isRising = isRising;

        // set barometer arrow
        const barometerDOM = document.querySelector(`.${city}--barometer`);
        barometerDOM.dataset.pressure = pressure;
        // TEST barometerDOM.dataset.pressure = 999

        barometerArrow(city);

    })






