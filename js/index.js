const navToggle = document.getElementById("navToggle");
const navMenu = document.getElementById("navMenu");
const navBackdrop = document.getElementById("navBackdrop");

if (navToggle && navMenu) {
    function nyitMenut() {
        navMenu.classList.add("nav-open");
        if (navBackdrop) navBackdrop.classList.add("active");
    }
    function csukMenut() {
        navMenu.classList.remove("nav-open");
        if (navBackdrop) navBackdrop.classList.remove("active");
    }
    navToggle.addEventListener("click", (e) => {
        e.stopPropagation();
        navMenu.classList.toggle("nav-open");
        if (navBackdrop) navBackdrop.classList.toggle("active");
    });
    if (navBackdrop) navBackdrop.addEventListener("click", csukMenut);
    navMenu.querySelectorAll("a").forEach(a => {
        a.addEventListener("click", () => { if (window.innerWidth <= 768) csukMenut(); });
    });
}