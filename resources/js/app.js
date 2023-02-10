import "./bootstrap";
const bothCitiesDOM = document.querySelector(".both--js");
const controlsDOM = document.querySelector(".controls--js");
const navNidaDOM = controlsDOM.querySelector(".nav--nida");
const navVilniusDOM = controlsDOM.querySelector(".nav--vilnius");
const navBothDOM = controlsDOM.querySelector(".nav--both");
const messageDOM = document.querySelector('.message--js');
const zeroDeg = 1015;

const showCity = (navElement, nidaDisplay, vilniusDisplay) => {
    const nidaDOM = bothCitiesDOM.querySelector(".nida--city");
    const vilniusDOM = bothCitiesDOM.querySelector(".vilnius--city");
    navElement.addEventListener("click", () => {
        [...controlsDOM.children].forEach((control) => {
            control.classList.remove("active");
        });
        navElement.classList.add("active");
        nidaDOM.style.display = nidaDisplay;
        vilniusDOM.style.display = vilniusDisplay;
    });
};
const addMessage = (updateTime) => {
    const errorMessage = `Pardon, information cannot be renewed. Last updated on ${updateTime}.`;
    messageDOM.style.display = 'block';
    messageDOM.animate(
        [
           {opacity: 0},
           {opacity: 1}
        ],
        {duration: 5000, iterations: 1, easing: 'linear'}
    )
    messageDOM.innerText = errorMessage;
}
const removeMessage = () => {
    messageDOM.animate(
        [
           {opacity: 1},
           {opacity: 0}
        ],
        {duration: 5000, iterations: 1}
    )
    setTimeout(() => {messageDOM.style.display = 'none'}, 5000);
}

if (document.querySelector(".no--data")) 
    {
        let a = 0;
        Echo.channel("public.weather.update").listen("WeatherUpdated", () => {
            ++a;
            if (a == 2) {
                location.reload();
            }
        });
    } 
else 
    {
        showCity(navNidaDOM, 'flex', 'none');
        showCity(navVilniusDOM, 'none', 'flex');
        showCity(navBothDOM, 'flex', 'flex');

        window.addEventListener('load', () => {
            if(messageDOM.dataset.updateCondition){
                addMessage(messageDOM.dataset.updateCondition);
            }
            bothCitiesDOM.animate(
                [
                    { gap: "100px", opacity: 0 },
                    { gap: "30px", opacity: 1 },
                ],
                { duration: 1000, iterations: 1, easing: 'linear'}
            );
            setData("vilnius");
            setData("nida");
            // barometerArrow('makarena'); ---> test
        });
        const isRising = (city) => 
            {
                const isRisingDOM = bothCitiesDOM.querySelector(`.is--rising--${city}`);
                if(isRisingDOM !== null){
                    const isRisingSvgDOM = isRisingDOM.querySelector("svg");
                    const isRisingTextDOM = isRisingDOM.querySelector("span");
                    const isRising = parseInt(isRisingDOM.dataset.isRising);
                
                    isRisingSvgDOM.style.transform = isRising ? "rotate(0deg)" : "rotate(180deg)";
                    isRisingTextDOM.innerText = isRising ? "Pressure rising" : "Pressure falling";
                }
            }
        const weatherCondition = (city) => 
            {
                const cityWeatherConditionDOM = bothCitiesDOM.querySelector(`.weather--${city}`);
                const weatherCondition = cityWeatherConditionDOM.dataset.weatherCondition;
            
                cityWeatherConditionDOM.querySelector("use").setAttribute("xlink:href", `#${weatherCondition}`);
                cityWeatherConditionDOM.querySelector("span").innerText = weatherCondition !== "dry" ? weatherCondition : `very ${weatherCondition}`;
            }
        const barometerArrow = (city) => {
            const barometerDOM = document.querySelector(`.${city}--barometer`);
            const hectopascalPressure = barometerDOM.dataset.pressure;
            const arrowBoxDOM = barometerDOM.querySelector("div");
        
            if (hectopascalPressure !== zeroDeg) {
                const rotateDeg = ((hectopascalPressure - zeroDeg) * 90) / 33;
                // TEST console.log(rotateDeg);
                arrowBoxDOM.style.transform = `rotate(${rotateDeg}deg)`;
                // TEST arrowBoxDOM.style.transform = `rotate(180deg)`;
            } else {
                arrowBoxDOM.style.transform = "rotate(0deg)";
            }
        };
        const setData = (city) => {
            barometerArrow(city);
            isRising(city);
            weatherCondition(city);
        }
        // listen to websockets
        Echo.channel('public.weather.update')
            .listen('WeatherUpdated', (e) => {
                if( messageDOM.style.display === 'block'){
                    removeMessage();
                }

            const city = e.updatedCity.city;
            const isRisingUpdate = (e) => {
                 //TEST const isRising = 1;
                 if(e.updatedCity.is_rising != null){
                     const isRising = e.updatedCity.is_rising;
                     const isRisingDOM = bothCitiesDOM.querySelector(`.is--rising--${city}`);
                     isRisingDOM.dataset.isRising = isRising;
                 }
            }
            const weatherConditionUpdate = (e) => {
                  // TEST const weatherCondition = 'dry';
                const weatherCondition = e.updatedCity.weather_condition;
                const cityWeatherConditionDOM = bothCitiesDOM.querySelector(`.weather--${city}`);
                cityWeatherConditionDOM.dataset.weatherCondition = weatherCondition;
            }
            const pressureUpdate = (e) => {
                // TEST  const pressure = 960;
                const pressure = e.updatedCity.pressure;
                const barometerDOM = document.querySelector(`.${city}--barometer`);
                barometerDOM.dataset.pressure = pressure;
                // TEST barometerDOM.dataset.pressure = 960
            }
            isRisingUpdate(e);
            weatherConditionUpdate(e);
            pressureUpdate(e);
            setData(city);
            });
        Echo.channel('public.weather.update.fail')
            .listen('WeatherUpdateFail', (e) => {
                const updateTime = e.lastUpdate;
                if(updateTime){
                    addMessage(updateTime);
                }
            })
    }
