import './bootstrap';
const nidaDOM = document.querySelector('.nida--city');
const vilniusDOM = document.querySelector('.vilnius--city');
const controlsDOM = document.querySelector('.controls--js');
const navNidaDOM = controlsDOM.querySelector('.nav--nida');
const navVilniusDOM = controlsDOM.querySelector('.nav--vilnius');
const navBothDOM = controlsDOM.querySelector('.nav--both');

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

showCity(navNidaDOM, 'flex', 'none')
showCity(navVilniusDOM, 'none', 'flex');
showCity(navBothDOM, 'flex', 'flex');
