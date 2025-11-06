const editProfile = document.getElementById("editProfile");
const saveProfile = document.getElementById("saveProfile");
const cancelEdit = document.getElementById("cancelEdit");
const saveEdit = document.getElementById("saveEdit");
const editActions = document.getElementById("editActions");
const nameInput = document.getElementById("adminName");
const roleInput = document.getElementById("adminRole");

editProfile.onclick = () => {
    editProfile.classList.add("hidden");
    saveProfile.classList.remove("hidden");
    editActions.classList.remove("hidden");
    nameInput.disabled = false;
    roleInput.disabled = false;
};
saveProfile.onclick = cancelEdit.onclick = () => {
    editProfile.classList.remove("hidden");
    saveProfile.classList.add("hidden");
    editActions.classList.add("hidden");
    nameInput.disabled = true;
    roleInput.disabled = true;
};
saveEdit.onclick = () => {
    editProfile.classList.remove("hidden");
    saveProfile.classList.add("hidden");
    editActions.classList.add("hidden");
    nameInput.disabled = true;
    roleInput.disabled = true;
    alert("Changes saved!");
};

const btnChangeEmail = document.getElementById("btnChangeEmail");
const btnChangePassword = document.getElementById("btnChangePassword");
const emailForm = document.getElementById("emailForm");
const passwordForm = document.getElementById("passwordForm");
const cancelEmail = document.getElementById("cancelEmail");
const cancelPassword = document.getElementById("cancelPassword");

btnChangeEmail.onclick = () => {
    emailForm.classList.toggle("hidden");
    passwordForm.classList.add("hidden");
};
btnChangePassword.onclick = () => {
    passwordForm.classList.toggle("hidden");
    emailForm.classList.add("hidden");
};
cancelEmail.onclick = () => emailForm.classList.add("hidden");
cancelPassword.onclick = () => passwordForm.classList.add("hidden");

function togglePassword(id, btn) {
    const input = document.getElementById(id);
    if (input.type === "password") {
        input.type = "text";
        btn.textContent = "🙈";
    } else {
        input.type = "password";
        btn.textContent = "👁";
    }
}
