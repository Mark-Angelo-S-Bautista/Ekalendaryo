const btnTopEdit = document.getElementById("btnTopEdit");
const btnTopSave = document.getElementById("btnTopSave");
const inlineActions = document.getElementById("inlineActions");
const btnCancel = document.getElementById("btnCancel");
const btnSave = document.getElementById("btnSave");
const inputs = [
    document.getElementById("inputName"),
    document.getElementById("inputRole"),
];
const pill1 = document.getElementById("pill1");
const pill2 = document.getElementById("pill2");

function setEditing(on) {
    if (on) {
        btnTopEdit.style.display = "none";
        btnTopSave.style.display = "inline-flex";
        inlineActions.style.display = "flex";
        inputs.forEach((i) => {
            i.disabled = false;
            i.style.cursor = "text";
        });
        pill1.classList.remove("disabled");
        pill2.classList.remove("disabled");
    } else {
        btnTopEdit.style.display = "inline-flex";
        btnTopSave.style.display = "none";
        inlineActions.style.display = "none";
        inputs.forEach((i) => {
            i.disabled = true;
            i.style.cursor = "default";
        });
        pill1.classList.add("disabled");
        pill2.classList.add("disabled");
    }
}

setEditing(false);

btnTopEdit.addEventListener("click", () => setEditing(true));

btnTopSave.addEventListener("click", () => {
    setEditing(false);
    const original = btnTopSave.innerHTML;
    btnTopSave.innerHTML = "Saved ✓";
    setTimeout(() => (btnTopSave.innerHTML = original), 1200);
});

btnCancel.addEventListener("click", () => setEditing(false));

btnSave.addEventListener("click", () => {
    setEditing(false);
    const orig = btnSave.innerHTML;
    btnSave.innerHTML = "Saved ✓";
    setTimeout(() => (btnSave.innerHTML = orig), 900);
});

document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        setEditing(false);
    }
});
