let newUser = 0

if(localStorage.getItem("style") == null) {
    localStorage.setItem("style", "dark");
    newUser = 1
}

if (localStorage.getItem("style") == "dark" || newUser) {
    document.querySelector(".theme").href = "/assets/dark.css?31";
}
function changeTheme() {
    if (document.querySelector(".theme").getAttribute("href") === "#") {
        document.querySelector(".theme").href = "/assets/dark.css?31";
        localStorage.setItem("style", "dark");
    } else {
        document.querySelector(".theme").href = "#";
        localStorage.setItem("style", "light");
    }
}
