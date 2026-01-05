document.addEventListener("DOMContentLoaded", () => {
    const root = document.getElementById("ctf-countdown");
    if (!root) return;

    const startStr = root.dataset.start;
    const endStr = root.dataset.end;
    const statusEl = document.getElementById("status-text");
    const dEl = document.getElementById("cd-days");
    const hEl = document.getElementById("cd-hours");
    const mEl = document.getElementById("cd-mins");
    const sEl = document.getElementById("cd-secs");

    if (!startStr || !endStr || !statusEl || !dEl || !hEl || !mEl || !sEl) {
        return;
    }

    const start = new Date(startStr);
    const end = new Date(endStr);

    function pad(n) {
        return n.toString().padStart(2, "0");
    }

    function render() {
        const now = new Date();

        let target;
        let mode;

        if (now < start) {
            // Chưa bắt đầu -> đếm tới START
            mode = "upcoming";
            target = start;
            statusEl.textContent = "Starts in:";
        } else if (now >= start && now <= end) {
            // Đang chạy -> đếm tới END
            mode = "running";
            target = end;
            statusEl.textContent = "CTF is LIVE!";
        } else {
            // Đã kết thúc
            mode = "ended";
            target = null;
            statusEl.textContent = "Event has ended.";
        }

        if (!target) {
            dEl.textContent = "--";
            hEl.textContent = "--";
            mEl.textContent = "--";
            sEl.textContent = "--";
            return;
        }

        const diff = target - now;
        if (diff <= 0) {
            dEl.textContent = "00";
            hEl.textContent = "00";
            mEl.textContent = "00";
            sEl.textContent = "00";
            return;
        }

        const days = Math.floor(diff / (1000 * 60 * 60 * 24));
        const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((diff % (1000 * 60)) / 1000);

        dEl.textContent = pad(days);
        hEl.textContent = pad(hours);
        mEl.textContent = pad(mins);
        sEl.textContent = pad(secs);

        // Nhẹ nhàng đổi glow bằng class (nếu muốn hiệu ứng thêm)
        root.dataset.mode = mode;
    }

    render();
    setInterval(render, 1000);
});

