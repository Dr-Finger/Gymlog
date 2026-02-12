document.addEventListener("DOMContentLoaded", () => {
    document.querySelectorAll(".elfogad-gomb").forEach(btn => {
        btn.addEventListener("click", async () => {
            const id = btn.getAttribute("data-id");
            if (!id) return;
            btn.disabled = true;

            try {
                const response = await fetch("barat_elfogad.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ kerelm_id: parseInt(id, 10) })
                });
                const data = await response.json();

                if (data.siker) {
                    const sor = btn.closest(".kerelm-sor");
                    if (sor) sor.remove();
                } else {
                    btn.disabled = false;
                }
            } catch (e) {
                btn.disabled = false;
            }
        });
    });
});
