var btnSignin = document.querySelector("#signin");
var btnSignup = document.querySelector("#signup");
var logo = document.querySelector("#logo");
var body = document.querySelector("body");

btnSignin.addEventListener("click", function () {
   body.className = "sign-in-js"; 
   // Adiciona a animação de desaparecer e depois a de aparecer
   logo.classList.add("logo-disappear");

   // Espera a animação de desaparecer terminar para reiniciar a de aparecer
   setTimeout(function() {
     logo.classList.remove("logo-disappear");
     logo.classList.add("logo-appear");
   }, 2000); // 2000ms = tempo da animação de desaparecer
});

btnSignup.addEventListener("click", function () {
    body.className = "sign-up-js";

    // Adiciona a animação de desaparecer e depois a de aparecer
    logo.classList.add("logo-disappear");

    // Espera a animação de desaparecer terminar para reiniciar a de aparecer
    setTimeout(function() {
      logo.classList.remove("logo-disappear");
      logo.classList.add("logo-appear");
    }, 2000); // 2000ms = tempo da animação de desaparecer
});
