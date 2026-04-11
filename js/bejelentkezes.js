const jelszo = document.getElementById("jelszo");
const mutasd = document.getElementById("mutasd");

if (mutasd && jelszo) {
    mutasd.addEventListener("mouseover", function () {
        jelszo.type = "text";
        mutasd.style.color = "lightblue";
    });
    mutasd.addEventListener("mouseout", function () {
        jelszo.type = "password";
        mutasd.style.color = "white";
    });
}

const email = document.getElementById("email");
const form = document.querySelector(".auth-form");
const hibaEl = document.getElementById("hiba");

if (form) {
    form.addEventListener("submit", function (e) {
        if (!email || !jelszo) return;
        if (email.value.trim() === "" || jelszo.value.trim() === "") {
            e.preventDefault();
            if (hibaEl) hibaEl.innerText = "Minden mezőt töltsön ki!";
        } else {
            if (hibaEl) hibaEl.innerText = "";
        }
    });
}
