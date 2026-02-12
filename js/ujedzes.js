document.addEventListener("DOMContentLoaded", () => {
    const elemek = {
        ujGyakGomb: document.getElementById("ujGyakorlatGomb"),
        panel: document.getElementById("gyakorlatPanel"),
        panelZar: document.getElementById("panelZar"),
        panelLista: document.getElementById("gyakorlatListaOldal"),
        keresInput: document.getElementById("gyakorlatKereses"),
        valasztottWrap: document.getElementById("valasztottGyakorlatok"),
        hiba: document.getElementById("hiba"),
        gyCount: document.getElementById("gyakorlatCount"),
        mentesGomb: document.getElementById("mentes"),
        inditGomb: document.getElementById("inditGomb"),
        befejezGomb: document.getElementById("befejezGomb"),
        idotartamKijelzo: document.getElementById("idotartamKijelzo")
    };

    if (Object.values(elemek).some(e => !e)) {
        return;
    }

    // Számláló / timer
    let timerInterval = null;
    let elteltMasodperc = 0;

    function formatIdo(mp) {
        const m = Math.floor(mp / 60);
        const s = mp % 60;
        return String(m).padStart(2, "0") + ":" + String(s).padStart(2, "0");
    }

    function timerIndit() {
        if (timerInterval) return;
        elteltMasodperc = 0;
        elemek.idotartamKijelzo.textContent = formatIdo(0);
        timerInterval = setInterval(() => {
            elteltMasodperc++;
            elemek.idotartamKijelzo.textContent = formatIdo(elteltMasodperc);
        }, 1000);
        elemek.inditGomb.disabled = true;
        elemek.befejezGomb.disabled = false;
    }

    function timerLeallit() {
        if (timerInterval) {
            clearInterval(timerInterval);
            timerInterval = null;
        }
        elemek.inditGomb.disabled = false;
        elemek.befejezGomb.disabled = true;
    }

    // Gyakorlat számláló frissítése
    function frissitDarab() {
        const db = elemek.valasztottWrap.querySelectorAll(".edzes-sor").length;
        elemek.gyCount.textContent = db + " gyakorlat";
        if (db === 0 && !elemek.valasztottWrap.querySelector(".ures-info")) {
            const p = document.createElement("p");
            p.className = "ures-info";
            p.textContent = "Még nem adtál hozzá gyakorlatot.";
            elemek.valasztottWrap.appendChild(p);
        }
    }

    // Panel nyitás/zárás
    function panelNyit() { elemek.panel.classList.add("open"); }
    function panelCsuk() { elemek.panel.classList.remove("open"); }

    // Gyakorlat sor létrehozása
    function gyakorlatSorLetrehozasa(nev, set = 3, rep = 8, suly = 0) {
        const sor = document.createElement("div");
        sor.className = "edzes-sor";
        sor.innerHTML = `
            <span class="gyakorlat-nev">${nev}</span>
            <label>Set: <input type="number" class="set-input" min="1" max="10" value="${set}"></label>
            <label>Rep: <input type="number" class="rep-input" min="1" max="30" value="${rep}"></label>
            <label>Súly (kg): <input type="number" class="suly-input" min="0" max="500" value="${suly}"></label>
            <button type="button" class="sor-torles">✕</button>
        `;
        return sor;
    }

    // Event listener-ek
    elemek.ujGyakGomb.addEventListener("click", panelNyit);
    elemek.panelZar.addEventListener("click", panelCsuk);

    // Gyakorlat hozzáadása
    elemek.panelLista.addEventListener("click", (e) => {
        if (!e.target.classList.contains("gyakorlat-item")) return;
        const nev = e.target.getAttribute("data-nev") || e.target.textContent.trim();
        if (!nev) return;

        const ures = elemek.valasztottWrap.querySelector(".ures-info");
        if (ures) ures.remove();

        elemek.valasztottWrap.appendChild(gyakorlatSorLetrehozasa(nev));
        frissitDarab();
        panelCsuk();
    });

    // Sor törlése
    elemek.valasztottWrap.addEventListener("click", (e) => {
        if (!e.target.classList.contains("sor-torles")) return;
        const sor = e.target.closest(".edzes-sor");
        if (sor) {
            sor.remove();
            frissitDarab();
        }
    });

    // Kereső
    elemek.keresInput.addEventListener("input", () => {
        const q = elemek.keresInput.value.toLowerCase().trim();
        elemek.panelLista.querySelectorAll(".gyakorlat-item").forEach((btn) => {
            btn.style.display = btn.textContent.toLowerCase().includes(q) ? "block" : "none";
        });
    });

    // Sorok adatainak összegyűjtése
    function sorokOsszegyujtese() {
        return Array.from(elemek.valasztottWrap.querySelectorAll(".edzes-sor")).map((sor) => ({
            nev: sor.querySelector(".gyakorlat-nev")?.textContent.trim() || "",
            set: Number(sor.querySelector(".set-input")?.value) || 0,
            rep: Number(sor.querySelector(".rep-input")?.value) || 0,
            suly: Number(sor.querySelector(".suly-input")?.value) || 0
        }));
    }

    // Indít gomb
    elemek.inditGomb.addEventListener("click", () => {
        const sorok = sorokOsszegyujtese();
        if (sorok.length === 0) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj hozzá legalább egy gyakorlatot az indításhoz!";
            return;
        }
        const edzesNev = document.getElementById("edzesNev")?.value.trim() || "";
        if (!edzesNev) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }
        elemek.hiba.textContent = "";
        timerIndit();
    });

    // Befejez gomb
    elemek.befejezGomb.addEventListener("click", async () => {
        if (window.vendeg) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Jelentkezz be a befejezéshez.";
            return;
        }

        const sorok = sorokOsszegyujtese();
        if (sorok.length === 0) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj hozzá legalább egy gyakorlatot!";
            return;
        }
        const edzesNev = document.getElementById("edzesNev")?.value.trim() || "";
        if (!edzesNev) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }

        timerLeallit();

        try {
            elemek.hiba.style.color = "white";
            elemek.hiba.textContent = "Mentés...";

            const response = await fetch("ujedzes_befejez.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    nev: edzesNev,
                    sorok: sorok,
                    idotartam: elteltMasodperc
                })
            });

            const data = await response.json();

            if (data?.siker && data?.redirect) {
                window.location.href = data.redirect;
            } else {
                elemek.hiba.style.color = "red";
                elemek.hiba.textContent = data?.uzenet || "Hiba történt.";
            }
        } catch (e) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Nem sikerült kapcsolódni a szerverhez.";
        }
    });

    // Mentés (tervként)
    elemek.mentesGomb.addEventListener("click", async () => {
        elemek.hiba.style.color = "red";

        if (window.vendeg) {
            elemek.hiba.textContent = "A mentéshez jelentkezz be.";
            return;
        }

        const sorok = sorokOsszegyujtese();
        if (sorok.length === 0) {
            elemek.hiba.textContent = "Adj hozzá legalább egy gyakorlatot!";
            return;
        }

        const edzesNev = document.getElementById("edzesNev")?.value.trim() || "";
        if (!edzesNev) {
            elemek.hiba.textContent = "Adj nevet az edzésnek!";
            return;
        }

        try {
            elemek.hiba.style.color = "white";
            elemek.hiba.textContent = "Mentés folyamatban...";

            const response = await fetch("mentes_edzesterv.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ nev: edzesNev, sorok: sorok })
            });

            const data = await response.json();
            elemek.hiba.style.color = data?.siker ? "lightgreen" : "red";
            elemek.hiba.textContent = data?.uzenet || (data?.siker ? "Edzésterv sikeresen elmentve." : "Hiba történt a mentés közben.");
        } catch (e) {
            elemek.hiba.style.color = "red";
            elemek.hiba.textContent = "Nem sikerült kapcsolódni a szerverhez.";
        }
    });

    // Terv betöltése
    function tervBetoltese() {
        if (!window.tervAdatok) return;

        const edzesNevInput = document.getElementById("edzesNev");
        if (edzesNevInput && window.tervAdatok.nev) {
            edzesNevInput.value = window.tervAdatok.nev;
        }

        const sorok = window.tervAdatok.tartalom;
        if (Array.isArray(sorok) && sorok.length > 0) {
            const ures = elemek.valasztottWrap.querySelector(".ures-info");
            if (ures) ures.remove();

            elemek.valasztottWrap.querySelectorAll(".edzes-sor").forEach(s => s.remove());
            sorok.forEach(sor => {
                if (sor.nev) {
                    elemek.valasztottWrap.appendChild(gyakorlatSorLetrehozasa(
                        sor.nev,
                        sor.set || 3,
                        sor.rep || 8,
                        sor.suly || 0
                    ));
                }
            });
            frissitDarab();
        }
    }

    tervBetoltese();
    frissitDarab();
});

    