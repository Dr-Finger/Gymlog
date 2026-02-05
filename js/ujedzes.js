document.addEventListener("DOMContentLoaded", () => {
    const ujGyakGomb = document.getElementById("ujGyakorlatGomb");
    const panel = document.getElementById("gyakorlatPanel");
    const panelZar = document.getElementById("panelZar");
    const panelLista = document.getElementById("gyakorlatListaOldal");
    const keresInput = document.getElementById("gyakorlatKereses");
    const valasztottWrap = document.getElementById("valasztottGyakorlatok");
    const hiba = document.getElementById("hiba");
    const gyCount = document.getElementById("gyakorlatCount");
    const mentesGomb = document.getElementById("mentes");

    if (!ujGyakGomb || !panel || !panelZar || !panelLista || !valasztottWrap || !hiba || !gyCount || !mentesGomb) {
        return;
    }

    function frissitDarab() {
        const db = valasztottWrap.querySelectorAll(".edzes-sor").length;
        gyCount.textContent = db + " gyakorlat";
        if (db === 0 && !valasztottWrap.querySelector(".ures-info")) {
            const p = document.createElement("p");
            p.className = "ures-info";
            p.textContent = "Még nem adtál hozzá gyakorlatot.";
            valasztottWrap.appendChild(p);
        }
    }

    function panelNyit() {
        panel.classList.add("open");
    }

    function panelCsuk() {
        panel.classList.remove("open");
    }

    ujGyakGomb.addEventListener("click", () => {
        panelNyit();
    });

    panelZar.addEventListener("click", () => {
        panelCsuk();
    });

    // Gyakorlat hozzáadása kattintásra (oldalsó panel)
    panelLista.addEventListener("click", (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (!target.classList.contains("gyakorlat-item")) return;

        const nev = target.getAttribute("data-nev") || target.textContent.trim();
        if (!nev) return;

        // első hozzáadásnál távolítsuk el az üres szöveget
        const ures = valasztottWrap.querySelector(".ures-info");
        if (ures) {
            ures.remove();
        }

        const sor = document.createElement("div");
        sor.className = "edzes-sor";
        sor.innerHTML = `
            <span class="gyakorlat-nev">${nev}</span>
            <label>Set:
                <input type="number" class="set-input" min="1" max="10" value="3">
            </label>
            <label>Rep:
                <input type="number" class="rep-input" min="1" max="30" value="8">
            </label>
            <label>Súly (kg):
                <input type="number" class="suly-input" min="0" max="500" value="0">
            </label>
            <button type="button" class="sor-torles">✕</button>
        `;
        valasztottWrap.appendChild(sor);

        frissitDarab();
        panelCsuk();
    });

    // Sor törlése és darabszám frissítése
    valasztottWrap.addEventListener("click", (e) => {
        const target = e.target;
        if (!(target instanceof HTMLElement)) return;
        if (!target.classList.contains("sor-torles")) return;

        const sor = target.closest(".edzes-sor");
        if (sor) {
            sor.remove();
            frissitDarab();
        }
    });

    // Kereső a panelen
    keresInput.addEventListener("input", () => {
        const q = keresInput.value.toLowerCase().trim();
        const elemek = panelLista.querySelectorAll(".gyakorlat-item");
        elemek.forEach((btn) => {
            const text = btn.textContent.toLowerCase();
            btn.style.display = text.includes(q) ? "block" : "none";
        });
    });

    // Egyszerű validáció a "mentésnél"
    mentesGomb.addEventListener("click", () => {
        hiba.style.color = "red";
        const db = valasztottWrap.querySelectorAll(".edzes-sor").length;
        if (db === 0) {
            hiba.textContent = "Adj hozzá legalább egy gyakorlatot!";
            return;
        }
        hiba.style.color = "white";
        hiba.textContent = "A mentés logikája később kerül kialakításra. A felépítés rendben van.";
    });

    frissitDarab();
});

    