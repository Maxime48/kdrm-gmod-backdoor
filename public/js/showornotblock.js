setTimeout(function() {
    let targetDiv = document.getElementById("post");
    let btn = document.getElementById("toggle");

    btn.onclick = function () {
        if (targetDiv.style.display !== "none") {
            targetDiv.style.display = "none";
        } else {
            targetDiv.style.display = "block";
        }
    };
    //timeout to execute because laravel is too quick
}, 150);
document.getElementById("post").style.display = "none"
