const loginButton = document.querySelector('#login');
const registerButton = document.querySelector('#register');

const slideElement = document.querySelector('.slide1');
const slideElement2 = document.querySelector('.slide2');
const formElement = document.querySelector('#loginForm');
const formElement2 = document.querySelector('#registerForm');
const loginTitle = document.querySelector('#loginTitle');
const inputElements = document.querySelectorAll('#registerForm input');
const inputElements2 = document.querySelectorAll('#loginForm input');
const submitbutton = document.querySelector('#submitButton');
const submitbutton2 = document.querySelector('#submitButton2');


function setSlideState(element, state) {
    const states = ['left', 'center', 'right'];
    states.forEach(s => element.classList.remove(`slide-${s}`));
    element.classList.add(`slide-${state}`);
}


loginButton.addEventListener('click', () => {
    setSlideState(slideElement, 'left');
    setSlideState(slideElement2, 'right');
    formElement.style.display = 'flex';
    formElement2.style.display = 'none';
    loginTitle.textContent = 'Connexion';

        inputElements2.forEach(input => {
        input.style.height = '7vh';
        input.style.width = '20vw';
        input.style.fontSize = '1.8em';
    

    });
    submitbutton.style.width = '15vw';
    submitbutton.style.height = '7vh';
    submitbutton.style.fontSize = '1.8em';
});


registerButton.addEventListener('click', () => {
    setSlideState(slideElement, 'right');
    setSlideState(slideElement2, 'left');
    formElement.style.display = 'none';
    formElement2.style.display = 'flex';
    loginTitle.textContent = 'Inscription';

    inputElements.forEach(input => {
        input.style.height = '5vh';
        input.style.width = '20vw';
        input.style.fontSize = '1.2em';
    });

    submitbutton2.style.width = '15vw';
    submitbutton2.style.height = '7vh';
    submitbutton2.style.fontSize = '1.8em';

});
