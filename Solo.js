
function select(pid, name)
{
    event.preventDefault();

    var debug = document.getElementById('debug');
    var debug_visible = (debug.style.display == "block");

    var xhttp = new XMLHttpRequest();
    xhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            document.write(this.responseText);
            document.close();
        }
    };
    xhttp.open("POST", window.location.href, true);
    xhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    xhttp.send("pid=" + pid + "&passage=" + name + "&debug=" + debug_visible);
    return false;
}

function restart()
{
    if (confirm("Sind sie sicher, dass sie das Abenteuer neu start wollen?"))
    {
        select(-1, "Start");
    }
    return false;
}

function show_debugger()
{
    var debug = document.getElementById('debug');
    var visible = (debug.style.display == "block");
    debug.style.display = (visible ? "none" : "block");
    return false;
}

window.onload = function (e) {

    if (self != top) {
        document.body.className = "framed";
    }
}
